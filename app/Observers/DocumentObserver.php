<?php

namespace App\Observers;

use App\Filament\Resources\TenantDocumentResource;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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
                    $requiredPermissions = ['view_any_tenant::document'];
                    $building = Building::where('id', $document->building_id)->first();
                    $roles = Role::where('owner_association_id',$building->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
                    $notifyTo = User::where('owner_association_id', $building->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
                    ->filter(function ($notifyTo) use ($requiredPermissions) {
                        return $notifyTo->can($requiredPermissions);
                    });
                    Log::info(Filament::getTenant()->id);
                    Log::info(Filament::getTenant()->id.TenantDocumentResource::getUrl('edit', $document->id));
                    Notification::make()
                        ->success()
                        ->title($document->name . " Received")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('A new document received from  '.auth()->user()->first_name)
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url(url('/admin/tenant-documents/'.$document->id.'/edit')),
                        ])
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
