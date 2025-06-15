<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Chat;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;

final class ChatController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Chat::class, 'chat');
    }

    public function index(): Response
    {
        $chatHistory = null;

        if (Auth::check()) {
            $chatHistory = Auth::user()->chats()->orderBy('updated_at', 'desc')->paginate(25);
        }

        return Inertia::render('Chat/Index', [
            'chatHistory' => Inertia::deepMerge($chatHistory),
        ]);
    }

    public function store(StoreChatRequest $request): RedirectResponse
    {
        $chat = Auth::user()->chats()->create([
            'title' => $request->validated()['message'],
            'visibility' => $request->validated()['visibility'],
        ]);

        return to_route('chats.show', ['chat' => $chat]);
    }

    public function show(Chat $chat): Response
    {
        $chatHistory = null;

        if (Auth::check()) {
            $chatHistory = Auth::user()->chats()->orderBy('updated_at', 'desc')->paginate(25);
        }

        return Inertia::render('Chat/Show', [
            'chat' => fn () => $chat->load('messages'),
            'chatHistory' => Inertia::deepMerge($chatHistory),
        ]);
    }

    public function update(Chat $chat, UpdateChatRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['message_id'])) {
            $messageId = $validated['message_id'];

            $message = $chat->messages()->find($messageId);

            if ($message && isset($validated['is_upvoted'])) {
                $upvoteValue = (bool) $validated['is_upvoted'];
                $message->update(['is_upvoted' => $upvoteValue]);
            }
        }

        $updates = [];

        if (isset($validated['title'])) {
            $updates['title'] = $validated['title'];
        }

        if (isset($validated['visibility'])) {
            $updates['visibility'] = $validated['visibility'];
        }

        if ($updates !== []) {
            $chat->update($updates);
        }

        return to_route('chats.show', ['chat' => $chat]);
    }

    public function destroy(Chat $chat): RedirectResponse
    {
        $chat->messages()->delete();
        $chat->delete();

        return to_route('chats.index');
    }
}
