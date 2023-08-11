<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\Master\DocumentLibrary;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentLibraryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the documentLibrary can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the documentLibrary can view the model.
     */
    public function view(User $user, DocumentLibrary $model): bool
    {
        return true;
    }

    /**
     * Determine whether the documentLibrary can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the documentLibrary can update the model.
     */
    public function update(User $user, DocumentLibrary $model): bool
    {
        return true;
    }

    /**
     * Determine whether the documentLibrary can delete the model.
     */
    public function delete(User $user, DocumentLibrary $model): bool
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
     * Determine whether the documentLibrary can restore the model.
     */
    public function restore(User $user, DocumentLibrary $model): bool
    {
        return false;
    }

    /**
     * Determine whether the documentLibrary can permanently delete the model.
     */
    public function forceDelete(User $user, DocumentLibrary $model): bool
    {
        return false;
    }
}
