<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Generator;
use Throwable;
use App\Models\Chat;
use Prism\Prism\Prism;
use App\Models\Message;
use App\Enums\ModelName;
use Prism\Prism\Enums\ChunkType;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ChatStreamRequest;
use Illuminate\Support\Facades\Response;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

final class ChatStreamController extends Controller
{
    public function __invoke(ChatStreamRequest $request, Chat $chat): StreamedResponse
    {
        $userMessage = $request->string('message')->trim()->value();
        $model = $request->enum('model', ModelName::class, ModelName::GPT_4_1_NANO);

        $chat->messages()->create([
            'role' => 'user',
            'parts' => $userMessage,
            'attachments' => '[]',
        ]);

        $messages = $this->buildConversationHistory($chat);

        return Response::stream(function () use ($chat, $messages, $model): Generator {
            $assistantContent = '';
            try {
                $response = Prism::text()
                    ->withSystemPrompt(view('prompts.system'))
                    ->using($model->getProvider(), $model->value)
                    ->withMessages($messages)
                    ->asStream();

                foreach ($response as $chunk) {
                    if ($chunk->chunkType === ChunkType::Text) {
                        $assistantContent .= $chunk->text;
                        yield 'data: '.json_encode([
                            'type' => 'text',
                            'content' => $chunk->text
                        ])."\n\n";
                    } elseif ($chunk->chunkType === ChunkType::Thinking) {
                        // Stream reasoning chunks but don't store them
                        yield 'data: '.json_encode([
                            'type' => 'reasoning',
                            'content' => $chunk->text
                        ])."\n\n";
                    }
                }

                if ($assistantContent !== '' && $assistantContent !== '0') {
                    $chat->messages()->create([
                        'role' => 'assistant',
                        'parts' => $assistantContent,
                        'attachments' => '[]',
                    ]);
                    $chat->touch();
                }

            } catch (Throwable $throwable) {
                Log::error("Chat stream error for chat {$chat->id}: ".$throwable->getMessage());
                yield 'data: '.json_encode(['type' => 'error', 'content' => 'Stream failed'])."\n\n";
            }
        });
    }

    private function buildConversationHistory(Chat $chat): array
    {
        return $chat->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $message): UserMessage|\Prism\Prism\ValueObjects\Messages\AssistantMessage => match ($message->role) {
                'user' => new UserMessage(content: $message->parts),
                'assistant' => new AssistantMessage(content: $message->parts),
            })
            ->toArray();
    }
}
