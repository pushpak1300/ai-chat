<?php

declare(strict_types=1);

use App\Models\Chat;
use App\Models\User;
use App\Policies\ChatPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ChatPolicy', function (): void {
    beforeEach(function (): void {
        $this->policy = new ChatPolicy();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->ownedChat = Chat::factory()->for($this->user)->create(['visibility' => 'private']);
        $this->otherUserChat = Chat::factory()->for($this->otherUser)->create(['visibility' => 'private']);
        $this->publicChat = Chat::factory()->for($this->otherUser)->create(['visibility' => 'public']);
    });

    describe('viewAny', function (): void {
        it('allows authenticated users to view any chats', function (): void {
            $result = $this->policy->viewAny($this->user);

            expect($result)->toBeTrue();
        });

        it('allows unauthenticated users to view any chats', function (): void {
            $result = $this->policy->viewAny(null);

            expect($result)->toBeTrue();
        });
    });

    describe('view', function (): void {
        it('allows user to view their own chat', function (): void {
            $result = $this->policy->view($this->user, $this->ownedChat);

            expect($result)->toBeTrue();
        });

        it('allows user to view public chats owned by others', function (): void {
            $result = $this->policy->view($this->user, $this->publicChat);

            expect($result)->toBeTrue();
        });

        it('prevents user from viewing private chats owned by others', function (): void {
            $result = $this->policy->view($this->user, $this->otherUserChat);

            expect($result)->toBeFalse();
        });

        it('allows unauthenticated users to view public chats', function (): void {
            $result = $this->policy->view(null, $this->publicChat);

            expect($result)->toBeTrue();
        });

        it('prevents unauthenticated users from viewing private chats', function (): void {
            $result = $this->policy->view(null, $this->ownedChat);

            expect($result)->toBeFalse();
        });

        it('prevents unauthenticated users from viewing private chats of others', function (): void {
            $result = $this->policy->view(null, $this->otherUserChat);

            expect($result)->toBeFalse();
        });
    });

    describe('create', function (): void {
        it('allows authenticated user to create a chat', function (): void {
            $result = $this->policy->create($this->user);

            expect($result)->toBeTrue();
        });

        it('prevents unauthenticated user from creating a chat', function (): void {
            $result = $this->policy->create(null);

            expect($result)->toBeFalse();
        });
    });

    describe('update', function (): void {
        it('allows user to update their own chat', function (): void {
            $result = $this->policy->update($this->user, $this->ownedChat);

            expect($result)->toBeTrue();
        });

        it('prevents user from updating chats owned by others', function (): void {
            $result = $this->policy->update($this->user, $this->otherUserChat);

            expect($result)->toBeFalse();
        });

        it('prevents user from updating public chats owned by others', function (): void {
            $result = $this->policy->update($this->user, $this->publicChat);

            expect($result)->toBeFalse();
        });
    });

    describe('delete', function (): void {
        it('allows user to delete their own chat', function (): void {
            $result = $this->policy->delete($this->user, $this->ownedChat);

            expect($result)->toBeTrue();
        });

        it('prevents user from deleting chats owned by others', function (): void {
            $result = $this->policy->delete($this->user, $this->otherUserChat);

            expect($result)->toBeFalse();
        });

        it('prevents user from deleting public chats owned by others', function (): void {
            $result = $this->policy->delete($this->user, $this->publicChat);

            expect($result)->toBeFalse();
        });
    });

    describe('restore', function (): void {
        it('allows user to restore their own chat', function (): void {
            $result = $this->policy->restore($this->user, $this->ownedChat);

            expect($result)->toBeTrue();
        });

        it('prevents user from restoring chats owned by others', function (): void {
            $result = $this->policy->restore($this->user, $this->otherUserChat);

            expect($result)->toBeFalse();
        });
    });

    describe('forceDelete', function (): void {
        it('allows user to force delete their own chat', function (): void {
            $result = $this->policy->forceDelete($this->user, $this->ownedChat);

            expect($result)->toBeTrue();
        });

        it('prevents user from force deleting chats owned by others', function (): void {
            $result = $this->policy->forceDelete($this->user, $this->otherUserChat);

            expect($result)->toBeFalse();
        });
    });
});
