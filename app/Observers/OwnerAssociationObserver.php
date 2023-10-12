<?php

namespace App\Observers;

use App\Jobs\FetchBuildingsJob;
use App\Models\OwnerAssociation;

class OwnerAssociationObserver
{
    public function created(OwnerAssociation $ownerAssociation)
    {
        FetchBuildingsJob::dispatch($ownerAssociation);
    }
}
