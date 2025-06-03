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
use Illuminate\Pagination\LengthAwarePaginator;

final class ChatController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Chat::class, 'chat');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $chatsHistory = Auth::user()->chats()->orderBy('updated_at', 'desc')->paginate(25);

        return Inertia::render('Chat/Index', [
            'chatHistory' => Inertia::deepMerge($chatsHistory),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatRequest $request): RedirectResponse
    {
        $chat = Auth::user()->chats()->create([
            'title' => $request->validated()['message'],
            'visibility' => $request->validated()['visibility'],
        ]);

        return to_route('chats.show', ['chat' => $chat]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Chat $chat): Response
    {
        /** @var LengthAwarePaginator<Chat> $chatHistory */
        $chatHistory = Auth::user()->chats()->orderBy('updated_at', 'desc')->paginate(25);

        return Inertia::render('Chat/Show', [
            'chat' => fn () => $chat->load('messages'),
            'chatHistory' => Inertia::deepMerge($chatHistory),
        ]);
    }

    public function update(Chat $chat, UpdateChatRequest $request): RedirectResponse
    {
        if ($request->filled('message_id')) {
            $messageId = $request->get('message_id');

            $message = $chat->messages()->find($messageId);

            if ($message && $request->has('is_upvoted')) {
                $upvoteValue = $request->boolean('is_upvoted');
                $message->update(['is_upvoted' => $upvoteValue]);
            }
        }

        $updates = [];

        if ($request->filled('title')) {
            $updates['title'] = $request->get('title');
        }

        if ($request->filled('visibility')) {
            $updates['visibility'] = $request->get('visibility');
        }

        if ($updates !== []) {
            $chat->update($updates);
        }

        return to_route('chats.show', ['chat' => $chat]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chat $chat): RedirectResponse
    {
        $chat->messages()->delete();
        $chat->delete();

        return to_route('chats.index');
    }
}
