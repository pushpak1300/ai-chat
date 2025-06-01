<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Generator;
use App\Models\Chat;
use Prism\Prism\Prism;
use App\Models\Message;
use Illuminate\Http\Request;
use Prism\Prism\Enums\Provider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

final class ChatStreamController extends Controller
{
    public function __invoke(Request $request, Chat $chat)
    {
        Log::info('Stream started');

        $request->validate([
            'message' => 'required|string',
            'model' => 'nullable|string',
            'visibility' => 'nullable|string',
        ]);

        $userMessage = $request->input('message');
        $model = $request->input('model', 'gemini-2.0-flash');

        Log::info('Processing message', [
            'message' => $userMessage,
            'chat_id' => $chat->id,
            'model' => $model,
        ]);
        $messages = $chat->messages()->orderBy('created_at')->get();
        $messages->map(fn(Message $message): \Prism\Prism\ValueObjects\Messages\UserMessage|\Prism\Prism\ValueObjects\Messages\AssistantMessage => match ($message->role) {
            'user' => new UserMessage(content: $message->parts),
            'assistant' => new AssistantMessage(content: $message->parts),
        });

        return Response::stream(function () use ($userMessage, $chat, $model): Generator {
            $finalResponse = '';
            $stream = Prism::text()
                ->using(Provider::Gemini, $model)
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

            Log::info('Stream completed', ['final_response' => $finalResponse]);
        });
    }
}
