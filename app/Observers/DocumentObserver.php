<?php

namespace App\Observers;

use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\User\User;
use Filament\Notifications\Notification;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        if ($document->documentable_type == 'App\Models\User\User') {
            if ($document->building_id) {
                $allowedDocuments = [1, 2, 3, 4, 5];
                if ($document->document_library_id && in_array($document->document_library_id, $allowedDocuments)) {
                    $building = Building::where('id', $document->building_id)->first();
                    $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id', 10)->get();
                    Notification::make()
                        ->success()
                        ->title($document->name . " Received")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('A new document received from  ' . auth()->user()->first_name)
                        ->sendToDatabase($notifyTo);
                }
            }
        }
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "restored" event.
     */
    public function restored(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "force deleted" event.
     */
    public function forceDeleted(Document $document): void
    {
        //
    }
}
