<?php

namespace App\Policies;

use App\Models\FamilyMember;
use App\Models\User\User;
use Illuminate\Auth\Access\Response;

class FamilyMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_family::members');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FamilyMember $familyMember): bool
    {
        return $user->can('view_family::members');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_family::members');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FamilyMember $familyMember): bool
    {
        return $user->can('update_family::members');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FamilyMember $familyMember): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FamilyMember $familyMember): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FamilyMember $familyMember): bool
    {
        //
    }
}
