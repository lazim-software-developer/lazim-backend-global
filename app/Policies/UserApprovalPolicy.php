<?php

namespace App\Policies;

use App\Models\Building\Flat;
use App\Models\UserApproval;
use App\Models\User\User;
use DB;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserApprovalPolicy
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
        return $user->can('view_any_user::approval');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\UserApproval  $userApproval
     * @return bool
     */
    public function view(User $user, UserApproval $userApproval): bool
    {
        $pmbuildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        $flats = Flat::whereIn('building_id', $pmbuildingIds)->pluck('id')->toArray();

        if (auth()->user()->role->name == 'Property Manager') {
            return $user->can('view_user::approval')
            && $userApproval->owner_association_id === $user->owner_association_id
            && in_array($userApproval->flat_id, $flats);
        }
        return $user->can('view_user::approval');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create_user::approval');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\UserApproval  $userApproval
     * @return bool
     */
    public function update(User $user, UserApproval $userApproval): bool
    {
        $pmbuildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        $flats = Flat::whereIn('building_id', $pmbuildingIds)->pluck('id')->toArray();

        if (auth()->user()->role->name == 'Property Manager') {
            return $user->can('update_user::approval')
            && $userApproval->owner_association_id === $user->owner_association_id
            && in_array($userApproval->flat_id, $flats);
        }
        return $user->can('update_user::approval');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\UserApproval  $userApproval
     * @return bool
     */
    public function delete(User $user, UserApproval $userApproval): bool
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
     * @param  \App\Models\UserApproval  $userApproval
     * @return bool
     */
    public function forceDelete(User $user, UserApproval $userApproval): bool
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
     * @param  \App\Models\UserApproval  $userApproval
     * @return bool
     */
    public function restore(User $user, UserApproval $userApproval): bool
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
     * @param  \App\Models\UserApproval  $userApproval
     * @return bool
     */
    public function replicate(User $user, UserApproval $userApproval): bool
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
