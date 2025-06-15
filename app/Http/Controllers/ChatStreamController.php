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
            'parts' => [
                ChunkType::Text->value => $userMessage,
            ],
            'attachments' => '[]',
        ]);

        $messages = $this->buildConversationHistory($chat);

        return Response::stream(function () use ($chat, $messages, $model): Generator {
            $parts = [];

            try {
                $response = Prism::text()
                    ->withSystemPrompt(view('prompts.system'))
                    ->using($model->getProvider(), $model->value)
                    ->withMessages($messages)
                    ->asStream();

                foreach ($response as $chunk) {
                    $chunkData = [
                        'chunkType' => $chunk->chunkType->value,
                        'content' => $chunk->text,
                    ];

                    if (! isset($parts[$chunk->chunkType->value])) {
                        $parts[$chunk->chunkType->value] = '';
                    }

                    $parts[$chunk->chunkType->value] .= $chunk->text;

                    yield json_encode($chunkData)."\n";
                }

                if ($parts !== []) {
                    $chat->messages()->create([
                        'role' => 'assistant',
                        'parts' => $parts,
                        'attachments' => '[]',
                    ]);
                    $chat->touch();
                }

            } catch (Throwable $throwable) {
                Log::error("Chat stream error for chat {$chat->id}: ".$throwable->getMessage());
                yield json_encode([
                    'chunkType' => 'error',
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
