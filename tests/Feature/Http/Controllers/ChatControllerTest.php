<?php

declare(strict_types=1);

use App\Models\Chat;
use App\Models\User;
use App\Models\Message;
use App\Enums\ModelName;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ChatController', function (): void {
    beforeEach(function (): void {
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->actingAs($this->user);
    });

    describe('index', function (): void {
        it('displays paginated chats ordered by updated_at desc', function (): void {
            $olderChat = Chat::factory()->for($this->user)->create(['updated_at' => now()->subDay()]);
            $newerChat = Chat::factory()->for($this->user)->create(['updated_at' => now()]);

            $response = $this->get(route('chats.index'));

            $response->assertOk()
                ->assertInertia(
                    fn ($page) => $page
                        ->has('chatHistory.data', 2)
                        ->where('chatHistory.data.0.id', $newerChat->id)
                        ->where('chatHistory.data.1.id', $olderChat->id)
                );
        });

        it("only shows user's own chats", function (): void {
            $otherUser = User::factory()->create();
            Chat::factory()->for($otherUser)->create();
            $userChat = Chat::factory()->for($this->user)->create();

            $response = $this->get(route('chats.index'));

            $response->assertOk()
                ->assertInertia(
                    fn ($page) => $page
                        ->has('chatHistory.data', 1)
                        ->where('chatHistory.data.0.id', $userChat->id)
                );
        });

        it('paginates results with 25 per page', function (): void {
            Chat::factory()->for($this->user)->count(30)->create();

            $response = $this->get(route('chats.index'));

            $response->assertOk()
                ->assertInertia(
                    fn ($page) => $page
                        ->has('chatHistory.data', 25)
                        ->where('chatHistory.per_page', 25)
                );
        });

        it('allows unauthenticated users to view index page with no chat history', function (): void {
            $this->post(route('logout'));

            $response = $this->get(route('chats.index'));

            $response->assertOk()
                ->assertInertia(
                    fn ($page) => $page
                        ->where('chatHistory', null)
                );
        });
    });

    describe('store', function (): void {
        it('creates a new chat and redirects to show page', function (): void {
            $data = [
                'message' => 'Test chat message',
                'visibility' => 'private',
                'model' => ModelName::GPT_5_NANO->value,
            ];

            $response = $this->post(route('chats.store'), $data);

            $chat = Chat::query()->where('user_id', $this->user->id)->first();

            expect($chat)->not->toBeNull()
                ->and($chat->title)->toBe('Test chat message')
                ->and($chat->visibility)->toBe('private')
                ->and($chat->user_id)->toBe($this->user->id);

            $response->assertRedirect(route('chats.show', $chat));
        });

        it('validates required fields', function (): void {
            $response = $this->post(route('chats.store'), []);

            $response->assertSessionHasErrors(['message', 'visibility', 'model']);
        });

        it('validates visibility enum values', function (): void {
            $data = [
                'message' => 'Test message',
                'visibility' => 'invalid',
                'model' => ModelName::GPT_5_MINI->value,
            ];

            $response = $this->post(route('chats.store'), $data);

            $response->assertSessionHasErrors('visibility');
        });

        it('validates model enum values', function (): void {
            $data = [
                'message' => 'Test message',
                'visibility' => 'private',
                'model' => 'invalid-model',
            ];

            $response = $this->post(route('chats.store'), $data);

            $response->assertSessionHasErrors('model');
        });
    });

    describe('show', function (): void {
        it('displays chat with messages and chat history', function (): void {
            $chat = Chat::factory()->for($this->user)->create();
            $message = Message::factory()->for($chat)->create([
                'role' => 'user',
                'parts' => 'Test message content',
                'attachments' => [],
            ]);

            $response = $this->get(route('chats.show', $chat));

            $response->assertOk()
                ->assertInertia(
                    fn ($page) => $page
                        ->where('chat.id', $chat->id)
                        ->has('chat.messages', 1)
                        ->where('chat.messages.0.id', $message->id)
                        ->has('chatHistory.data')
                );
        });

        it('allows access to own private chats', function (): void {
            $chat = Chat::factory()->for($this->user)->create(['visibility' => 'private']);

            $response = $this->get(route('chats.show', $chat));

            $response->assertOk();
        });

        it('allows access to public chats from other users', function (): void {
            $otherUser = User::factory()->create();
            $chat = Chat::factory()->for($otherUser)->create(['visibility' => 'public']);

            $response = $this->get(route('chats.show', $chat));

            $response->assertOk();
        });

        it('denies access to private chats from other users', function (): void {
            $otherUser = User::factory()->create();
            $chat = Chat::factory()->for($otherUser)->create(['visibility' => 'private']);

            $response = $this->get(route('chats.show', $chat));

            $response->assertForbidden();
        });

        it('allows unauthenticated users to view public chats', function (): void {
            $this->post(route('logout'));
            $otherUser = User::factory()->create();
            $chat = Chat::factory()->for($otherUser)->create(['visibility' => 'public']);

            $response = $this->get(route('chats.show', $chat));

            $response->assertOk()
                ->assertInertia(
                    fn ($page) => $page
                        ->where('chat.id', $chat->id)
                        ->where('chatHistory', null)
                );
        });

        it('denies unauthenticated users access to private chats', function (): void {
            $this->post(route('logout'));
            $chat = Chat::factory()->for($this->user)->create(['visibility' => 'private']);

            $response = $this->get(route('chats.show', $chat));

            $response->assertForbidden();
        });
    });

    describe('update', function (): void {
        beforeEach(function (): void {
            $this->chat = Chat::factory()->for($this->user)->create();
        });

        it('updates chat title', function (): void {
            $data = ['title' => 'Updated Title'];

            $response = $this->patch(route('chats.update', $this->chat), $data);

            $this->chat->refresh();
            expect($this->chat->title)->toBe('Updated Title');
            $response->assertRedirect(route('chats.show', $this->chat));
        });

        it('updates chat visibility', function (): void {
            $data = ['visibility' => 'public'];

            $response = $this->patch(route('chats.update', $this->chat), $data);

            $this->chat->refresh();
            expect($this->chat->visibility)->toBe('public');
            $response->assertRedirect(route('chats.show', $this->chat));
        });

        it('updates message upvote status', function (): void {
            $message = Message::factory()->for($this->chat)->create([
                'role' => 'user',
                'parts' => 'Test message',
                'attachments' => [],
                'is_upvoted' => true,
            ]);

            $data = [
                'message_id' => $message->id,
                'is_upvoted' => true,
            ];

            $response = $this->patch(route('chats.update', $this->chat), $data);

            $message->refresh();
            expect($message->is_upvoted)->toBeTrue();
            $response->assertRedirect(route('chats.show', $this->chat));
        });

        it('validates message_id exists', function (): void {
            $data = [
                'message_id' => 'non-existent-id',
                'is_upvoted' => true,
            ];

            $response = $this->patch(route('chats.update', $this->chat), $data);

            $response->assertSessionHasErrors('message_id');
        });

        it('validates title length', function (): void {
            $data = ['title' => str_repeat('a', 256)];

            $response = $this->patch(route('chats.update', $this->chat), $data);

            $response->assertSessionHasErrors('title');
        });

        it('validates visibility enum', function (): void {
            $data = ['visibility' => 'invalid'];

            $response = $this->patch(route('chats.update', $this->chat), $data);

            $response->assertSessionHasErrors('visibility');
        });
    });

    describe('destroy', function (): void {
        it('deletes chat and redirects to index', function (): void {
            $chat = Chat::factory()->for($this->user)->create();

            $response = $this->delete(route('chats.destroy', $chat));

            expect(Chat::query()->find($chat->id))->toBeNull();
            $response->assertRedirect(route('chats.index'));
        });

        it('deletes associated messages when chat is deleted', function (): void {
            $chat = Chat::factory()->for($this->user)->create();
            $message = Message::factory()->for($chat)->create([
                'role' => 'user',
                'parts' => 'Test message',
                'attachments' => [],
            ]);

            $this->delete(route('chats.destroy', $chat));

            expect(Chat::query()->find($chat->id))->toBeNull()
                ->and(Message::query()->find($message->id))->toBeNull();
        });
    });

    describe('policy integration', function (): void {
        beforeEach(function (): void {
            $this->ownedChat = Chat::factory()->for($this->user)->create(['visibility' => 'private']);
            $this->otherUserPrivateChat = Chat::factory()->for($this->otherUser)->create(['visibility' => 'private']);
            $this->otherUserPublicChat = Chat::factory()->for($this->otherUser)->create(['visibility' => 'public']);
        });

        it('allows viewing own chats', function (): void {
            $response = $this->get(route('chats.show', $this->ownedChat));

            $response->assertOk();
        });

        it('allows viewing public chats from other users', function (): void {
            $response = $this->get(route('chats.show', $this->otherUserPublicChat));

            $response->assertOk();
        });

        it('prevents viewing private chats from other users', function (): void {
            $response = $this->get(route('chats.show', $this->otherUserPrivateChat));

            $response->assertForbidden();
        });

        it('allows updating own chats', function (): void {
            $response = $this->patch(route('chats.update', $this->ownedChat), [
                'title' => 'Updated Title',
            ]);

            $response->assertRedirect(route('chats.show', $this->ownedChat));

            $this->ownedChat->refresh();
            expect($this->ownedChat->title)->toBe('Updated Title');
        });

        it('prevents updating private chats from other users', function (): void {
            $originalTitle = $this->otherUserPrivateChat->title;

            $response = $this->patch(route('chats.update', $this->otherUserPrivateChat), [
                'title' => 'Should Not Update',
            ]);

            $response->assertForbidden();

            $this->otherUserPrivateChat->refresh();
            expect($this->otherUserPrivateChat->title)->toBe($originalTitle);
        });

        it('prevents updating public chats from other users', function (): void {
            $originalTitle = $this->otherUserPublicChat->title;

            $response = $this->patch(route('chats.update', $this->otherUserPublicChat), [
                'title' => 'Should Not Update',
            ]);

            $response->assertForbidden();

            $this->otherUserPublicChat->refresh();
            expect($this->otherUserPublicChat->title)->toBe($originalTitle);
        });

        it('allows deleting own chats', function (): void {
            $response = $this->delete(route('chats.destroy', $this->ownedChat));

            $response->assertRedirect(route('chats.index'));

            expect(Chat::query()->find($this->ownedChat->id))->toBeNull();
        });

        it('prevents deleting private chats from other users', function (): void {
            $response = $this->delete(route('chats.destroy', $this->otherUserPrivateChat));

            $response->assertForbidden();

            expect(Chat::query()->find($this->otherUserPrivateChat->id))->not->toBeNull();
        });

        it('prevents deleting public chats from other users', function (): void {
            $response = $this->delete(route('chats.destroy', $this->otherUserPublicChat));

            $response->assertForbidden();

            expect(Chat::query()->find($this->otherUserPublicChat->id))->not->toBeNull();
        });
    });
});
