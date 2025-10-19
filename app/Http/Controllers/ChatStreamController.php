<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Generator;
use Throwable;
use App\Models\Chat;
use Prism\Prism\Prism;
use App\Models\Message;
use App\Enums\ModelName;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ChatStreamRequest;
use Illuminate\Support\Facades\Response;
use Prism\Prism\Streaming\Events\ThinkingEvent;
use Prism\Prism\Streaming\Events\TextDeltaEvent;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

final class ChatStreamController extends Controller
{
    public function __invoke(ChatStreamRequest $request, Chat $chat): StreamedResponse
    {
        $userMessage = $request->string('message')->trim()->value();
        $model = $request->enum('model', ModelName::class, ModelName::GPT_5_NANO);

        $chat->messages()->create([
            'role' => 'user',
            'parts' => [
                'text' => $userMessage,
            ],
            'attachments' => '[]',
        ]);

        $messages = $this->buildConversationHistory($chat);

        return Response::stream(function () use ($chat, $messages, $model): Generator {
            $parts = [
                'text' => '',
                'thinking' => '',
            ];

            try {
                $response = Prism::text()
                    ->withSystemPrompt(view('prompts.system'))
                    ->using($model->getProvider(), $model->value)
                    ->withMessages($messages)
                    ->asStream();

                foreach ($response as $event) {
                    $eventData = match ($event::class) {
                        TextDeltaEvent::class => [
                            'eventType' => 'text_delta',
                            'content' => $event->delta,
                        ],
                        ThinkingEvent::class => [
                            'eventType' => 'thinking',
                            'content' => $event->delta,
                        ],
                        default => null,
                    };

                    if ($eventData === null) {
                        continue;
                    }

                    if ($event instanceof TextDeltaEvent) {
                        $parts['text'] .= $event->delta;
                    }

                    if ($event instanceof ThinkingEvent) {
                        $parts['thinking'] .= $event->delta;
                    }

                    yield json_encode($eventData)."\n";
                }

                if ($parts['text'] !== '' || $parts['thinking'] !== '') {
                    $chat->messages()->create([
                        'role' => 'assistant',
                        'parts' => array_filter($parts, fn ($value) => $value !== ''),
                        'attachments' => '[]',
                    ]);
                    $chat->touch();
                }

            } catch (Throwable $throwable) {
                Log::error("Chat stream error for chat $chat->id: ".$throwable->getMessage());
                yield json_encode([
                    'eventType' => 'error',
                    'content' => 'Stream failed',
                ])."\n";
            }
        });
    }

    private function buildConversationHistory(Chat $chat): array
    {
        return $chat->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $message): UserMessage|AssistantMessage => match ($message->role) {
                'user' => new UserMessage(content: $message->parts['text'] ?? ''),
                'assistant' => new AssistantMessage(content: $message->parts['text'] ?? ''),
            })
            ->toArray();
    }
}
