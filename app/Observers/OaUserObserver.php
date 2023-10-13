<?php

namespace App\Observers;

use App\Models\OaUserRegistration;

class OaUserObserver
{
    /**
     * Handle the OaUserRegistration "created" event.
     */
    public function created(OaUserRegistration $oaUserRegistration): void
    {

        if (auth()->check()) {
            $oaUserRegistration->oa_user_registration_id= auth()->user()->oa_user_registration_id;
            // // or with a `team` relationship defined:
            // $post->team()->associate(auth()->user()->team);
        }
    }


    /**
     * Handle the OaUserRegistration "updated" event.
     */
    public function updated(OaUserRegistration $oaUserRegistration): void
    {
        //
    }

    /**
     * Handle the OaUserRegistration "deleted" event.
     */
    public function deleted(OaUserRegistration $oaUserRegistration): void
    {
        //
    }

    /**
     * Handle the OaUserRegistration "restored" event.
     */
    public function restored(OaUserRegistration $oaUserRegistration): void
    {
        //
    }

    /**
     * Handle the OaUserRegistration "force deleted" event.
     */
    public function forceDeleted(OaUserRegistration $oaUserRegistration): void
    {
        //
    }
}
