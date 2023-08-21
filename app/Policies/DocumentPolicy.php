<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\Building\Document;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the document can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the document can view the model.
     */
    public function view(User $user, Document $model): bool
    {
        return true;
    }

    /**
     * Determine whether the document can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the document can update the model.
     */
    public function update(User $user, Document $model): bool
    {
        return true;
    }

    /**
     * Determine whether the document can delete the model.
     */
    public function delete(User $user, Document $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     */
    public function deleteAny(User $user): bool
    {
        return true;
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
