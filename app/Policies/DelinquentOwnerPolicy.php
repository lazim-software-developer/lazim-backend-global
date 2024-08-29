<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\DelinquentOwner;
use Illuminate\Auth\Access\HandlesAuthorization;

class DelinquentOwnerPolicy
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
        return $user->can('view_any_delinquent::owner');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\DelinquentOwner  $delinquentOwner
     * @return bool
     */
    public function view(User $user, DelinquentOwner $delinquentOwner): bool
    {
        return $user->can('view_delinquent::owner');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create_delinquent::owner');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\DelinquentOwner  $delinquentOwner
     * @return bool
     */
    public function update(User $user, DelinquentOwner $delinquentOwner): bool
    {
        return $user->can('update_delinquent::owner');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User\User  $user
     * @param  \App\Models\DelinquentOwner  $delinquentOwner
     * @return bool
     */
    public function delete(User $user, DelinquentOwner $delinquentOwner): bool
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
     * @param  \App\Models\DelinquentOwner  $delinquentOwner
     * @return bool
     */
    public function forceDelete(User $user, DelinquentOwner $delinquentOwner): bool
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
     * @param  \App\Models\DelinquentOwner  $delinquentOwner
     * @return bool
     */
    public function restore(User $user, DelinquentOwner $delinquentOwner): bool
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
     * @param  \App\Models\DelinquentOwner  $delinquentOwner
     * @return bool
     */
    public function replicate(User $user, DelinquentOwner $delinquentOwner): bool
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
