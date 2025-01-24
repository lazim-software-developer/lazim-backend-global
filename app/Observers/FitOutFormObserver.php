<?php

namespace App\Observers;

use App\Filament\Resources\FitOutFormsDocumentResource;
use App\Models\Building\Building;
use App\Models\Forms\FitOutForm;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class FitOutFormObserver
{
    /**
     * Handle the FitOutForm "created" event.
     */
    public function created(FitOutForm $fitOutForm): void
    {
        $requiredPermissions = ['view_any_fit::out::forms::document'];
        $oa_ids = DB::table('building_owner_association')->where(['building_id'=> $fitOutForm->building_id,'active'=> true])
            ->pluck('owner_association_id');
        $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff','Facility Manager'])->pluck('id');
        foreach($oa_ids as $oa_id){
            $oa = OwnerAssociation::find($oa_id);
            $flatexists = DB::table('property_manager_flats')
                ->where(['flat_id' => $fitOutForm->flat_id, 'active' => true, 'owner_association_id' => $oa_id])
                ->exists();
            if($oa->role == 'OA' || ($oa->role == 'Property Manager' && $flatexists)){
                $notifyTo = User::where('owner_association_id', $oa_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
                ->filter(function ($notifyTo) use ($requiredPermissions) {
                    return $notifyTo->can($requiredPermissions);
                });
                Notification::make()
                ->success()
                ->title("New Fitout Form Submission")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('New form submission by '.auth()->user()->first_name)
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(function() use ($fitOutForm,$oa_id){
                            $slug = $oa_id?->slug;
                            if($slug){
                                return FitOutFormsDocumentResource::getUrl('edit', [$slug,$fitOutForm?->id]);
                            }
                            return url('/app/fit-out-forms-documents/' . $fitOutForm?->id.'/edit');
                        }),
                ])
                ->sendToDatabase($notifyTo);
            }
        }
    }

    /**
     * Handle the FitOutForm "updated" event.
     */
    public function updated(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "deleted" event.
     */
    public function deleted(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "restored" event.
     */
    public function restored(FitOutForm $fitOutForm): void
    {
        //
    }

    /**
     * Handle the FitOutForm "force deleted" event.
     */
    public function forceDeleted(FitOutForm $fitOutForm): void
    {
        //
    }
}
