<?php

namespace App\Policies\Community;

use App\Models\Building\Building;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any posts for a specific building.
     *
     * @param  \App\Models\User  $user
     * @param  Integer $buildingId;
     * @return mixed
     */
    // public function viewAny(User $user, $buildingId)
    // {
    //     // If the user is a tenant or Owner, they can view any post
    //     if (in_array($user->role->name, ['Owner', 'Tenant'])) {
    //         return $user->residences()
    //         ->whereHas('building', function ($query) use ($buildingId) {
    //             $query->where('id', $buildingId);
    //         })
    //         ->where('active', 1)
    //         ->exists();
    //     }

    //     // TODO: Write logic for allowing OA admin to view all posts of all buildings which belongs to their OA

    //     // If the user's role is not owner or tenant, they can't view the posts
    //     return true;
    // }

    /**
     * Determine whether the user can create a post for a specific building.
     *
     * @param  \App\Models\User  $user
     * @param  Integer $buildingId;
     * @return mixed
     */
    // public function create(User $user, $buildingId)
    // {
    //     // If the user is a tenant or Owner, they can view any post
    //     if (in_array($user->role->name, ['Owner', 'Tenant'])) {
    //         return $user->residences()
    //         ->whereHas('building', function ($query) use ($buildingId) {
    //             $query->where('id', $buildingId);
    //         })
    //         ->where('active', 1)
    //         ->exists();
    //     }

    //     // TODO: Write logic for allowing OA admin to view all posts of all buildings which belongs to their OA

    //     // If the user's role is not owner or tenant, they can't view the posts
    //     return true;
    // }

    /**
     * Determine whether the user can view any posts for a specific building.
     *
     * @param  \App\Models\User  $user
     * @param  Integer $buildingId;
     * @return mixed
     */
    // public function view(User $user, $buildingId)
    // {
    //     // // If the user is a tenant or Owner, they can view any post
    //     // if (in_array($user->role->name, ['Owner', 'Tenant'])) {
    //     //     return $user->residences()
    //     //     ->whereHas('building', function ($query) use ($buildingId) {
    //     //         $query->where('id', $buildingId);
    //     //     })
    //     //     ->where('active', 1)
    //     //     ->exists();
    //     // }

    //     // TODO: Write logic for allowing OA admin to view all posts of all buildings which belongs to their OA

    //     // If the user's role is not owner or tenant, they can't view the posts
    //     return false;
    // }
     /**
     * Determine whether the attendance can view any models.
     */

     public function viewAny(User $user): bool
     {
        return true;
     }
 
     /**
      * Determine whether the attendance can view the model.
      */
     public function view(User $user): bool
     {
        return true;
     }
 
     /**
      * Determine whether the attendance can create models.
      */
     public function create(User $user): bool
     {
         return true;
     }
 
     /**
      * Determine whether the attendance can update the model.
      */
     public function update(User $user): bool
     {
        return true;
     }
 
     /**
      * Determine whether the attendance can delete the model.
      */
     public function delete(User $user): bool
     {
 
         return false;
 
     }
 
     /**
      * Determine whether the user can delete multiple instances of the model.
      */
     public function deleteAny(User $user): bool
     {
         return false;
 
     }
 
}
