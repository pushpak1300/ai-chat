<?php

declare(strict_types=1);

use App\Models\Chat;
use App\Models\User;
use Prism\Prism\Prism;
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
            'model' => 'gemini-2.0-flash',
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
            'model' => 'gemini-2.0-flash',
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
            'model' => 'gemini-1.5-pro',
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

    it('defaults to gemini-2.0-flash model when not specified', function (): void {
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
        // Create some existing messages
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
});
