<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Generator;
use Throwable;
use App\Models\Chat;
use Prism\Prism\Prism;
use App\Models\Message;
use App\Enums\ModelName;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\ChatStreamRequest;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\ChunkType;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

final class ChatStreamController extends Controller
{
    public function __invoke(ChatStreamRequest $request, Chat $chat): StreamedResponse
    {
        $userMessage = $request->string('message')->trim()->value();
        $model = $request->enum('model', ModelName::class, ModelName::GEMINI_2_0_FLASH_LITE);

        $chat->messages()->create([
            'role' => 'user',
            'parts' => $userMessage,
            'attachments' => '[]',
        ]);

        $messages = $this->buildConversationHistory($chat);

        return Response::stream(function () use ($chat, $messages, $model): Generator {
            $assistantContent = '';
            try {
                $prism = Prism::text()
                    ->withSystemPrompt(view('prompts.system'))
                    ->using($model->getProvider(), $model->value)
                    ->withMessages($messages);

                foreach ($prism->asStream() as $chunk) {
                    if ($chunk->chunkType === ChunkType::Text) {
                        $assistantContent .= $chunk->text;
                        yield $chunk->text;
                    }
                }
                if ($assistantContent) {
                    $chat->messages()->create([
                        'role' => 'assistant',
                        'parts' => $assistantContent,
                        'attachments' => '[]',
                    ]);
                    $chat->touch();
                }

            } catch (Throwable $e) {
                Log::error("Chat stream error for chat {$chat->id}: " . $e->getMessage());
                yield "data: " . json_encode(['error' => 'Stream failed']) . "\n\n";
            }
        });
    }

    private function buildConversationHistory(Chat $chat): array
    {
        return $chat->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn(Message $message) => match ($message->role) {
                'user' => new UserMessage(content: $message->parts),
                'assistant' => new AssistantMessage(content: $message->parts),
            })
            ->toArray();
    }
}
