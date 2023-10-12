<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\Visitor\FlatVisitor;
use Illuminate\Auth\Access\HandlesAuthorization;

class FlatVisitorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the flatVisitor can view any models.
     */
    public function viewAny(User $user): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the flatVisitor can view the model.
     */
    public function view(User $user, FlatVisitor $model): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the flatVisitor can create models.
     */
    public function create(User $user): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the flatVisitor can update the model.
     */
    public function update(User $user, FlatVisitor $model): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the flatVisitor can delete the model.
     */
    public function delete(User $user, FlatVisitor $model): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     */
    public function deleteAny(User $user): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the flatVisitor can restore the model.
     */
    public function restore(User $user, FlatVisitor $model): bool
    {
        return false;
    }

    /**
     * Determine whether the flatVisitor can permanently delete the model.
     */
    public function forceDelete(User $user, FlatVisitor $model): bool
    {
        return false;
    }
}
