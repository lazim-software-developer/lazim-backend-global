<?php

namespace App\Policies;

use App\Models\Building\Document;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the document can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the document can view the model.
     */
    public function view(User $user, Document $model): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the document can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the document can update the model.
     */
    public function update(User $user, Document $model): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the document can delete the model.
     */
    public function delete(User $user, Document $model): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     */
    public function deleteAny(User $user): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the document can restore the model.
     */
    public function restore(User $user, Document $model): bool
    {
        return false;
    }

    /**
     * Determine whether the document can permanently delete the model.
     */
    public function forceDelete(User $user, Document $model): bool
    {
        return false;
    }
}
