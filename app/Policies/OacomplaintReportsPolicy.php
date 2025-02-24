<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\OacomplaintReports;
use Illuminate\Auth\Access\HandlesAuthorization;

class OacomplaintReportsPolicy
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
        return $user->can('view_any_oacomplaint::reports');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\OacomplaintReports  $oacomplaintReports
     * @return bool
     */
    public function view(User $user, OacomplaintReports $oacomplaintReports): bool
    {
        return $user->can('view_oacomplaint::reports');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create_oacomplaint::reports');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\OacomplaintReports  $oacomplaintReports
     * @return bool
     */
    public function update(User $user, OacomplaintReports $oacomplaintReports): bool
    {
        return $user->can('update_oacomplaint::reports');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\OacomplaintReports  $oacomplaintReports
     * @return bool
     */
    public function delete(User $user, OacomplaintReports $oacomplaintReports): bool
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
     * @param  \App\Models\OacomplaintReports  $oacomplaintReports
     * @return bool
     */
    public function forceDelete(User $user, OacomplaintReports $oacomplaintReports): bool
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
     * @param  \App\Models\OacomplaintReports  $oacomplaintReports
     * @return bool
     */
    public function restore(User $user, OacomplaintReports $oacomplaintReports): bool
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
     * @param  \App\Models\OacomplaintReports  $oacomplaintReports
     * @return bool
     */
    public function replicate(User $user, OacomplaintReports $oacomplaintReports): bool
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
