<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_module');
    }

    public function view(User $user, Module $module): bool
    {
        return $user->can('view_module');
    }

    public function create(User $user): bool
    {
        return $user->can('create_module');
    }

    public function update(User $user, Module $module): bool
    {
        return $user->can('update_module');
    }

    public function delete(User $user, Module $module): bool
    {
        return $user->can('delete_module');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_module');
    }

    public function forceDelete(User $user, Module $module): bool
    {
        return $user->can('force_delete_module');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_module');
    }

    public function restore(User $user, Module $module): bool
    {
        return $user->can('restore_module');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_module');
    }

    public function replicate(User $user, Module $module): bool
    {
        return $user->can('replicate_module');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_module');
    }
}
