<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Chat;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;
use Illuminate\Pagination\LengthAwarePaginator;

final class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var LengthAwarePaginator<Chat> $chats */
        $chats = Auth::user()->chats()->orderBy('updated_at', 'desc')->paginate(25);

        return Inertia::render('Chat/Index', [
            'chatHistory' => Inertia::deepMerge($chats),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatRequest $request)
    {
        $chat = Auth::user()->chats()->create([
            'title' => $request->validated()['message'],
            'visibility' => $request->validated()['visibility'],
        ]);

        return redirect()->route('chats.show', ['chat' => $chat]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Chat $chat)
    {
        abort_if($chat->user_id !== Auth::id() && $chat->visibility !== 'public', 403);

        $chat->load('messages');

        /** @var LengthAwarePaginator<Chat> $chats */
        $chats = Auth::user()->chats()->orderBy('updated_at', 'desc')->paginate(25);

        return Inertia::render('Chat/Show', [
            'chat' => $chat,
            'chatHistory' => Inertia::deepMerge($chats),
        ]);
    }

    public function update(Chat $chat, UpdateChatRequest $request)
    {
        if ($request->filled('message_id')) {
            $message = $chat->messages()->find($request->string('message_id'));

            if (! $message) {
                return null;
            }

            if ($request->filled('is_upvoted')) {
                $message->update(['is_upvoted' => $request->boolean('is_upvoted')]);
            }

            if ($request->filled('message')) {
                $chat->messages()
                    ->where('created_at', '>', $message->created_at)
                    ->delete();

                $message->update(['message' => $request->string('message')]);
            }
        }

        $updates = [];

        if ($request->filled('title')) {
            $updates['title'] = $request->string('title');
        }

        if ($request->filled('visibility')) {
            $updates['visibility'] = $request->string('visibility');
        }

        if ($updates !== []) {
            $chat->update($updates);
        }

        return redirect()->route('chats.show', ['chat' => $chat]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chat $chat)
    {
        $chat->delete();

        return redirect()->route('chats.index');
    }
}
