<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

final class ChatPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return $user instanceof User || $user === null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Chat $chat): bool
    {
        if (! $user instanceof User) {
            return $chat->visibility === 'public';
        }

        return $user->id === $chat->user_id || $chat->visibility === 'public';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user instanceof User;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Chat $chat): bool
    {
        return $user->id === $chat->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Chat $chat): bool
    {
        return $user->id === $chat->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Chat $chat): bool
    {
        return $user->id === $chat->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Chat $chat): bool
    {
        return $user->id === $chat->user_id;
    }
}
