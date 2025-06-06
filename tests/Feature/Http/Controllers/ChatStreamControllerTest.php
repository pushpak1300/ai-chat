<?php

declare(strict_types=1);

use App\Models\Chat;
use App\Models\User;
use Prism\Prism\Prism;
use App\Enums\ModelName;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Testing\TextResponseFake;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ChatStreamController', function (): void {
    beforeEach(function (): void {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->chat = Chat::factory()->for($this->user)->create();
    });

    it('streams text response correctly', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Hello, how can I help you today?')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Hello',
            'model' => ModelName::GPT_4O_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('Hello, how can I help you today?');
    });

    it('saves user and assistant messages to database', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('I understand your question.')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $initialMessageCount = $this->chat->messages()->count();

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'What is the weather like?',
            'model' => ModelName::GPT_4O_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('I understand your question.');

        expect($this->chat->messages()->count())->toBe($initialMessageCount + 2);

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();

        expect($userMessage->parts)->toBe('What is the weather like?');
        expect($assistantMessage->parts)->toBe('I understand your question.');
    });

    it('trims whitespace from user message', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Response')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => '  Hello with spaces  ',
            'model' => ModelName::GPT_4O_MINI->value,
        ]);

        $response->assertOk();

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->parts)->toBe('Hello with spaces');
    });

    it('updates chat timestamp', function (): void {
        $originalUpdatedAt = $this->chat->updated_at;

        Prism::fake([
            TextResponseFake::make()
                ->withText('Response')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $this->travel(1)->minute();

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('Response');

        $this->chat->refresh();
        expect($this->chat->updated_at)->toBeGreaterThan($originalUpdatedAt);
    });

    it('works with different models', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Custom model response')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test',
            'model' => ModelName::GPT_4O_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('Custom model response');
    });

    it('handles chunked streaming correctly', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('This is a longer response that will be chunked')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(5);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Tell me a story',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('This is a longer response that will be chunked');
    });

    it('defaults to gpt-4.1-nano model when not specified', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Default model response')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test without model',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('Default model response');
    });

    it('preserves existing chat messages during streaming', function (): void {
        $this->chat->messages()->create([
            'role' => 'user',
            'parts' => 'Previous user message',
            'attachments' => '[]',
        ]);
        $this->chat->messages()->create([
            'role' => 'assistant',
            'parts' => 'Previous assistant message',
            'attachments' => '[]',
        ]);

        Prism::fake([
            TextResponseFake::make()
                ->withText('New response')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'New message',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('New response');

        expect($this->chat->messages()->count())->toBe(4);

        $messages = $this->chat->messages()->orderBy('created_at')->get();
        expect($messages[0]->parts)->toBe('Previous user message');
        expect($messages[1]->parts)->toBe('Previous assistant message');
        expect($messages[2]->parts)->toBe('New message');
        expect($messages[3]->parts)->toBe('New response');
    });

    it('builds conversation history correctly', function (): void {
        $this->chat->messages()->create([
            'role' => 'user',
            'parts' => 'First user message',
            'attachments' => '[]',
        ]);
        $this->chat->messages()->create([
            'role' => 'assistant',
            'parts' => 'First assistant response',
            'attachments' => '[]',
        ]);
        $this->chat->messages()->create([
            'role' => 'user',
            'parts' => 'Second user message',
            'attachments' => '[]',
        ]);

        Prism::fake([
            TextResponseFake::make()
                ->withText('Final response')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'New message',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('Final response');

        expect($this->chat->messages()->count())->toBe(5);
    });

    it('handles error finish reason and ends stream correctly', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Partial response before error')
                ->withFinishReason(FinishReason::Error),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'This will cause an error',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('Partial response before error');

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();

        expect($userMessage->parts)->toBe('This will cause an error');
        expect($assistantMessage->parts)->toBe('Partial response before error');
    });

    it('handles exceptions during streaming and logs errors', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withFinishReason(FinishReason::Error),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'This will throw an exception',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->parts)->toBe('This will throw an exception');

        $assistantMessages = $this->chat->messages()->where('role', 'assistant');
        expect($assistantMessages->count())->toBe(0);
    });

    it('does not save assistant message when content is empty', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $initialMessageCount = $this->chat->messages()->count();

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        expect($this->chat->messages()->count())->toBe($initialMessageCount + 1);

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->parts)->toBe('Test message');

        $assistantMessages = $this->chat->messages()->where('role', 'assistant');
        expect($assistantMessages->count())->toBe(0);
    });

    it('does not save assistant message when content is zero string', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('0')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $initialMessageCount = $this->chat->messages()->count();

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('0');

        expect($this->chat->messages()->count())->toBe($initialMessageCount + 1);

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->parts)->toBe('Test message');

        $assistantMessages = $this->chat->messages()->where('role', 'assistant');
        expect($assistantMessages->count())->toBe(0);
    });

    it('uses system prompt from view', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Response with system prompt')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('Response with system prompt');
    });

    it('sets correct message attributes when creating user message', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Response')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->role)->toBe('user');
        expect($userMessage->parts)->toBe('Test message');
        expect($userMessage->attachments)->toBe('[]');
    });

    it('sets correct message attributes when creating assistant message', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Assistant response')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('Assistant response');

        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();
        expect($assistantMessage)->not()->toBeNull();
        expect($assistantMessage->role)->toBe('assistant');
        expect($assistantMessage->parts)->toBe('Assistant response');
        expect($assistantMessage->attachments)->toBe('[]');
    });

    it('handles multiple text chunks correctly', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('First chunk Second chunk')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Multi-chunk message',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('First chunk Second chunk');

        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();
        expect($assistantMessage->parts)->toBe('First chunk Second chunk');
    });

    it('only processes text chunks and ignores other chunk types', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Only this text should be processed')
                ->withFinishReason(FinishReason::Stop),
        ]);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();
        $response->assertStreamedContent('Only this text should be processed');

        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();
        expect($assistantMessage->parts)->toBe('Only this text should be processed');
    });
});
