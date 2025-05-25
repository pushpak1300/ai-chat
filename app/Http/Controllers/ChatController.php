<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Prism\Prism\Prism;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('Chat/Index', [
            'title' => 'Chat with AI'
        ]);
    }

    public function stream(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        $message = $request->input('message');

        return new StreamedResponse(function () use ($message) {
            // Set headers for streaming
            header('Cache-Control: no-cache');
            header('Content-Type: text/event-stream');
            header('Connection: keep-alive');

            try {
                // Configure Prism with OpenAI (you can change this to any provider)
                $prism = Prism::text()
                    ->using('openai', 'gpt-4')
                    ->withMaxTokens(1000)
                    ->withTemperature(0.7);

                // Get streaming response
                $stream = $prism->stream($message);

                foreach ($stream as $chunk) {
                    $content = $chunk->text ?? '';

                    // Send server-sent event format
                    echo "data: " . json_encode([
                        'type' => 'chunk',
                        'content' => $content
                    ]) . "\n\n";

                    // Flush the output
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                }

                // Send completion event
                echo "data: " . json_encode([
                    'type' => 'complete'
                ]) . "\n\n";

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

            } catch (\Exception $e) {
                echo "data: " . json_encode([
                    'type' => 'error',
                    'message' => $e->getMessage()
                ]) . "\n\n";

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'Connection' => 'keep-alive',
        ]);
    }
}
