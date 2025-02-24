<?php

namespace App\Policies\Visitor;

use App\Models\User\User;
use App\Models\Visitor\FlatDomesticHelp;
use Illuminate\Auth\Access\HandlesAuthorization;

class FlatDomesticHelpPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_visitor::flat::domestic::help');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\Visitor\FlatDomesticHelp  $flatDomesticHelp
     * @return bool
     */
    public function view(User $user, FlatDomesticHelp $flatDomesticHelp): bool
    {
        return $user->can('view_visitor::flat::domestic::help');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create_visitor::flat::domestic::help');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\Visitor\FlatDomesticHelp  $flatDomesticHelp
     * @return bool
     */
    public function update(User $user, FlatDomesticHelp $flatDomesticHelp): bool
    {
        return $user->can('update_visitor::flat::domestic::help');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\Visitor\FlatDomesticHelp  $flatDomesticHelp
     * @return bool
     */
    public function delete(User $user, FlatDomesticHelp $flatDomesticHelp): bool
    {
        return $user->can('{{ Delete }}');
    }

    /**
     * Determine whether the user can bulk delete.
     *
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('{{ DeleteAny }}');
    }

    /**
     * Determine whether the user can permanently delete.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\Visitor\FlatDomesticHelp  $flatDomesticHelp
     * @return bool
     */
    public function forceDelete(User $user, FlatDomesticHelp $flatDomesticHelp): bool
    {
        return $user->can('{{ ForceDelete }}');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     *
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('{{ ForceDeleteAny }}');
    }

    /**
     * Determine whether the user can restore.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\Visitor\FlatDomesticHelp  $flatDomesticHelp
     * @return bool
     */
    public function restore(User $user, FlatDomesticHelp $flatDomesticHelp): bool
    {
        return $user->can('{{ Restore }}');
    }

    /**
     * Determine whether the user can bulk restore.
     *
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('{{ RestoreAny }}');
    }

    /**
     * Determine whether the user can replicate.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\Visitor\FlatDomesticHelp  $flatDomesticHelp
     * @return bool
     */
    public function replicate(User $user, FlatDomesticHelp $flatDomesticHelp): bool
    {
        return $user->can('{{ Replicate }}');
    }

    /**
     * Determine whether the user can reorder.
     *
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('{{ Reorder }}');
    }

}
