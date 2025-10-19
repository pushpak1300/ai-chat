<?php

declare(strict_types=1);

use App\Models\Chat;
use App\Models\User;
use Prism\Prism\Prism;
use App\Enums\ModelName;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\ValueObjects\Usage;
use Prism\Prism\Testing\TextStepFake;
use Prism\Prism\Text\ResponseBuilder;
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
                ->withFinishReason(FinishReason::Stop)
                ->withUsage(new Usage(25, 15))
                ->withMeta(new Meta('test-response-1', 'gpt-4o-mini')),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Hello',
            'model' => ModelName::GPT_5_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('"eventType":"text_delta"');
        expect($content)->toContain('Hello, how can I help you today?');
    });

    it('handles mixed chunk types in streaming response', function (): void {
        Prism::fake([
            (new ResponseBuilder)
                ->addStep(
                    TextStepFake::make()
                        ->withText('Let me think about this...')
                        ->withFinishReason(FinishReason::Stop)
                        ->withUsage(new Usage(50, 30))
                        ->withMeta(new Meta('thinking-step', 'gpt-4o-mini'))
                        ->withAdditionalContent([
                            'thinking' => 'The user is asking a complex question that requires careful consideration.',
                            'meta' => 'Processing request with enhanced reasoning.',
                        ])
                )
                ->toResponse(),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Explain quantum computing',
            'model' => ModelName::GPT_5_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('"eventType":"text_delta"');
        expect($content)->toContain('Let me think about this...');
    });

    it('handles streaming with finish reason error', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Partial response before error occurs')
                ->withFinishReason(FinishReason::Error)
                ->withUsage(new Usage(20, 10))
                ->withMeta(new Meta('error-response', 'gpt-4o-mini')),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'This might cause an error',
            'model' => ModelName::GPT_5_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('Partial response before error occurs');

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();

        expect($userMessage->parts)->toBe(['text' => 'This might cause an error']);
        expect($assistantMessage->parts)->toBe(['text' => 'Partial response before error occurs']);
    });

    it('handles multi-step streaming response', function (): void {
        Prism::fake([
            (new ResponseBuilder)
                ->addStep(
                    TextStepFake::make()
                        ->withText('First, let me analyze your question.')
                        ->withFinishReason(FinishReason::Stop)
                        ->withUsage(new Usage(30, 15))
                        ->withMeta(new Meta('step-1', 'gpt-4o-mini'))
                )
                ->addStep(
                    TextStepFake::make()
                        ->withText(' Now, here is my detailed response.')
                        ->withFinishReason(FinishReason::Stop)
                        ->withUsage(new Usage(40, 25))
                        ->withMeta(new Meta('step-2', 'gpt-4o-mini'))
                )
                ->toResponse(),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Complex multi-step question',
            'model' => ModelName::GPT_5_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('First, let me analyze your question.');
        expect($content)->toContain(' Now, here is my detailed response.');

        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();
        expect($assistantMessage->parts)->toBe(['text' => 'First, let me analyze your question. Now, here is my detailed response.']);
    });

    it('handles different chunk types correctly', function (): void {
        Prism::fake([
            (new ResponseBuilder)
                ->addStep(
                    TextStepFake::make()
                        ->withText('I need to think about this carefully.')
                        ->withFinishReason(FinishReason::Stop)
                        ->withUsage(new Usage(45, 30))
                        ->withMeta(new Meta('thinking-response', 'gpt-4o-mini'))
                        ->withAdditionalContent([
                            'thinking' => "Let me analyze the user's request step by step.",
                            'meta' => 'Processing with enhanced reasoning capabilities.',
                        ])
                )
                ->toResponse(),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Complex analytical question',
            'model' => ModelName::GPT_5_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('I need to think about this carefully.');
        expect($content)->toContain('"eventType":"text_delta"');

        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();
        expect($assistantMessage->parts)->toBe(['text' => 'I need to think about this carefully.']);
        expect($assistantMessage->attachments)->toBe('[]');
    });

    it('saves user and assistant messages to database with parts', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('I understand your question.')
                ->withFinishReason(FinishReason::Stop)
                ->withUsage(new Usage(42, 28))
                ->withMeta(new Meta('weather-response', 'gpt-4o-mini')),
        ])->withFakeChunkSize(1000);

        $initialMessageCount = $this->chat->messages()->count();

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'What is the weather like?',
            'model' => ModelName::GPT_5_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('I understand your question.');

        expect($this->chat->messages()->count())->toBe($initialMessageCount + 2);

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();

        expect($userMessage->parts)->toBe(['text' => 'What is the weather like?']);
        expect($userMessage->attachments)->toBe('[]');
        expect($assistantMessage->parts)->toBe(['text' => 'I understand your question.']);
        expect($assistantMessage->attachments)->toBe('[]');
    });

    it('streams chunks with proper JSON formatting', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Response')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
            'model' => ModelName::GPT_5_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();

        // Check that the response contains properly formatted JSON chunks
        $streamedContent = $response->streamedContent();
        expect($streamedContent)->toContain('"eventType":"text_delta"');
        expect($streamedContent)->toContain('Response');
    });

    it('creates user message with text parts', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Response')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->parts)->toBe(['text' => 'Test message']);
        expect($userMessage->attachments)->toBe('[]');
    });

    it('saves assistant message with text parts', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Assistant response')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('Assistant response');

        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();
        expect($assistantMessage)->not()->toBeNull();
        expect($assistantMessage->parts)->toBe(['text' => 'Assistant response']);
        expect($assistantMessage->attachments)->toBe('[]');
    });

    it('handles multiple text parts correctly', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('First chunk Second chunk')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Multi-chunk message',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('First chunk Second chunk');

        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();
        expect($assistantMessage->parts)->toBe(['text' => 'First chunk Second chunk']);
        expect($assistantMessage->attachments)->toBe('[]');
    });

    it('does not save assistant message when content is empty', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $initialMessageCount = $this->chat->messages()->count();

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        expect($this->chat->messages()->count())->toBe($initialMessageCount + 1);

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->parts)->toBe(['text' => 'Test message']);
        expect($userMessage->attachments)->toBe('[]');

        $assistantMessages = $this->chat->messages()->where('role', 'assistant');
        expect($assistantMessages->count())->toBe(0);
    });

    it('trims whitespace from user message', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Response')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => '  Hello with spaces  ',
            'model' => ModelName::GPT_5_MINI->value,
        ]);

        $response->assertOk();

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->parts)->toBe(['text' => 'Hello with spaces']);
    });

    it('updates chat timestamp', function (): void {
        $originalUpdatedAt = $this->chat->updated_at;

        Prism::fake([
            TextResponseFake::make()
                ->withText('Response')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $this->travel(1)->minute();

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('Response');

        $this->chat->refresh();
        expect($this->chat->updated_at)->toBeGreaterThan($originalUpdatedAt);
    });

    it('works with different models', function (): void {
        $fake = Prism::fake([
            TextResponseFake::make()
                ->withText('Custom model response')
                ->withFinishReason(FinishReason::Stop)
                ->withUsage(new Usage(30, 20))
                ->withMeta(new Meta('custom-model-test', 'gpt-4o-mini')),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test',
            'model' => ModelName::GPT_5_MINI->value,
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('Custom model response');

        $fake->assertCallCount(1);
        $fake->assertRequest(function (array $requests): true {
            expect($requests[0]->model())->toBe('gpt-5-mini');

            return true;
        });
    });

    it('handles chunked streaming correctly', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('This is a longer response that will be chunked')
                ->withFinishReason(FinishReason::Stop)
                ->withUsage(new Usage(60, 35))
                ->withMeta(new Meta('story-response', 'gpt-4o-mini')),
        ])->withFakeChunkSize(5);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Tell me a story',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('"eventType":"text_delta"');
        expect($content)->toContain('This ');
    });

    it('defaults to gpt-4.1-nano model when not specified', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Default model response')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test without model',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('Default model response');
    });

    it('preserves existing chat messages during streaming', function (): void {
        $this->chat->messages()->create([
            'role' => 'user',
            'parts' => ['text' => 'Previous user message'],
            'attachments' => '[]',
        ]);
        $this->chat->messages()->create([
            'role' => 'assistant',
            'parts' => ['text' => 'Previous assistant message'],
            'attachments' => '[]',
        ]);

        Prism::fake([
            TextResponseFake::make()
                ->withText('New response')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'New message',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('New response');

        expect($this->chat->messages()->count())->toBe(4);

        $messages = $this->chat->messages()->orderBy('created_at')->get();
        expect($messages[0]->parts)->toBe(['text' => 'Previous user message']);
        expect($messages[1]->parts)->toBe(['text' => 'Previous assistant message']);
        expect($messages[2]->parts)->toBe(['text' => 'New message']);
        expect($messages[3]->parts)->toBe(['text' => 'New response']);
    });

    it('builds conversation history correctly', function (): void {
        $this->chat->messages()->create([
            'role' => 'user',
            'parts' => ['text' => 'First user message'],
            'attachments' => '[]',
        ]);
        $this->chat->messages()->create([
            'role' => 'assistant',
            'parts' => ['text' => 'First assistant response'],
            'attachments' => '[]',
        ]);
        $this->chat->messages()->create([
            'role' => 'user',
            'parts' => ['text' => 'Second user message'],
            'attachments' => '[]',
        ]);

        Prism::fake([
            TextResponseFake::make()
                ->withText('Final response')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'New message',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('Final response');

        expect($this->chat->messages()->count())->toBe(5);
    });

    it('handles error finish reason and ends stream correctly', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Partial response before error')
                ->withFinishReason(FinishReason::Error),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'This will cause an error',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('Partial response before error');

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();

        expect($userMessage->parts)->toBe(['text' => 'This will cause an error']);
        expect($assistantMessage->parts)->toBe(['text' => 'Partial response before error']);
    });

    it('handles exceptions during streaming and logs errors', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withFinishReason(FinishReason::Error),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'This will throw an exception',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->parts)->toBe(['text' => 'This will throw an exception']);

        $assistantMessages = $this->chat->messages()->where('role', 'assistant');
        expect($assistantMessages->count())->toBe(0);
    });

    it('saves assistant message when content is zero string', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('0')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $initialMessageCount = $this->chat->messages()->count();

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('0');

        expect($this->chat->messages()->count())->toBe($initialMessageCount + 2);

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->parts)->toBe(['text' => 'Test message']);

        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();
        expect($assistantMessage->parts)->toBe(['text' => '0']);
    });

    it('uses system prompt from view', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Response with system prompt')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('Response with system prompt');
    });

    it('sets correct message attributes when creating user message', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Response')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();

        $userMessage = $this->chat->messages()->where('role', 'user')->latest()->first();
        expect($userMessage->role)->toBe('user');
        expect($userMessage->parts)->toBe(['text' => 'Test message']);
        expect($userMessage->attachments)->toBe('[]');
    });

    it('sets correct message attributes when creating assistant message', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Assistant response')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('Assistant response');

        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();
        expect($assistantMessage)->not()->toBeNull();
        expect($assistantMessage->role)->toBe('assistant');
        expect($assistantMessage->parts)->toBe(['text' => 'Assistant response']);
        expect($assistantMessage->attachments)->toBe('[]');
    });

    it('only processes text parts for message content storage', function (): void {
        Prism::fake([
            TextResponseFake::make()
                ->withText('Only this text should be processed')
                ->withFinishReason(FinishReason::Stop),
        ])->withFakeChunkSize(1000);

        $response = $this->post(route('chat.stream', $this->chat), [
            'message' => 'Test message',
        ]);

        $response->assertOk();
        $response->assertStreamed();

        $content = $response->streamedContent();
        expect($content)->toContain('Only this text should be processed');

        $assistantMessage = $this->chat->messages()->where('role', 'assistant')->latest()->first();
        expect($assistantMessage->parts)->toBe(['text' => 'Only this text should be processed']);
        expect($assistantMessage->attachments)->toBe('[]');
    });
});
