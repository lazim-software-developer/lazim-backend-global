<?php

namespace App\Observers;

use App\Filament\Resources\TenantDocumentResource;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Master\DocumentLibrary;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
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
                $allowedDocuments = DocumentLibrary::where('label','master')->pluck('id')->toArray();
                if ($document->document_library_id && in_array($document->document_library_id, $allowedDocuments)) {
                    $requiredPermissions = ['view_any_tenant::document'];
                    $oam_ids = DB::table('building_owner_association')->where('building_id',$document->building_id)->where('active', true)->pluck('owner_association_id');
                    $pm = OwnerAssociation::whereIn('id', $oam_ids)->where('role', 'Property Manager')->first();
                    $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff', 'Facility Manager'])->pluck('id');
                    foreach ($oam_ids as $oam_id) {
                        $oa = OwnerAssociation::find($oam_id);
                        $flatexists = DB::table('property_manager_flats')
                        ->where(['flat_id' => $document->flat_id, 'active' => true, 'owner_association_id' => $oa->role == 'OA' ? $pm?->id : $oa->id])
                        ->exists();
                        if($oa->role == 'OA' && !$flatexists || ($oa->role == 'Property Manager' && $flatexists)){
                            $notifyTo = User::where('owner_association_id', $oa->id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
                            ->filter(function ($notifyTo) use ($requiredPermissions) {
                                return $notifyTo->can($requiredPermissions);
                            });
                            if($notifyTo->count() > 0){
                                foreach($notifyTo as $user){
                                    if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->document_id', $document->id)->exists()){
                                        $data=[];
                                        $data['notifiable_type']='App\Models\User\User';
                                        $data['notifiable_id']=$user->id;
                                        $data['url']=TenantDocumentResource::getUrl('edit', [$oa->slug, $document->id]);
                                        $data['title']=$document->name." Received for Building:".$document->building->name;
                                        $data['body']='A new document received from  '.auth()->user()->first_name;
                                        $data['building_id']=$document->building_id;
                                        $data['custom_json_data']=json_encode([
                                            'building_id' => $document->building_id,
                                            'document_id' => $document->id,
                                            'user_id' => auth()->user()->id ?? null,
                                            'owner_association_id' => $oa->id,
                                            'type' => 'Document',
                                            'priority' => 'Medium',
                                        ]);
                                        NotificationTable($data);
                                    }
                                }
                            }
                            // Notification::make()
                            //     ->success()
                            //     ->title($document->name . " Received")
                            //     ->icon('heroicon-o-document-text')
                            //     ->iconColor('warning')
                            //     ->body('A new document received from  '.auth()->user()->first_name)
                            //     ->actions([
                            //         Action::make('view')
                            //             ->button()
                            //             ->url(function() use ($oa,$document){
                            //                 $slug = $oa?->slug;
                            //                 if($slug){
                            //                     return TenantDocumentResource::getUrl('edit', [$slug,$document?->id]);
                            //                 }
                            //                 return url('/app/tenant-documents/' . $document?->id.'/edit');
                            //             }),
                            //     ])
                            //     ->sendToDatabase($notifyTo);
                        }
                    }
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
