<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Generator;
use App\Models\Chat;
use Prism\Prism\Prism;
use App\Models\Message;
use App\Enums\ModelName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

final class ChatStreamController extends Controller
{
    public function __invoke(Request $request, Chat $chat): StreamedResponse
    {
        $userMessage = $request->string('message')->trim()->value();
        $modelId = $request->string('model', ModelName::GEMINI_2_0_FLASH_LITE->value)->trim()->value();

        $model = ModelName::tryFrom($modelId) ?? ModelName::GEMINI_2_0_FLASH_LITE;

        $messages = $chat->messages()->orderBy('created_at')->get();
        $messages->map(fn (Message $message): UserMessage|AssistantMessage => match ($message->role) {
            'user' => new UserMessage(content: $message->parts),
            'assistant' => new AssistantMessage(content: $message->parts),
        });

        return Response::stream(function () use ($userMessage, $chat, $model): Generator {
            $finalResponse = '';
            $stream = Prism::text()
                ->withSystemPrompt(view('prompts.system'))
                ->using($model->getProvider(), $model->value)
                ->withPrompt($userMessage)
                ->asStream();

            foreach ($stream as $response) {
                $finalResponse .= $response->text;
                yield $response->text;
            }

            $chat->messages()->create([
                'role' => 'user',
                'parts' => $userMessage,
                'attachments' => '[]',
            ]);

            $chat->messages()->create([
                'role' => 'assistant',
                'parts' => $finalResponse,
                'attachments' => '[]',
            ]);

            $chat->touch();
        });
    }
}
