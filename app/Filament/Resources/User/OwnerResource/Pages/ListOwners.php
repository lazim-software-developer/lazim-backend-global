<?php

namespace App\Filament\Resources\User\OwnerResource\Pages;

use Filament\Actions;
use App\Models\FlatOwners;
use Filament\Actions\Action;
use App\Models\Building\Flat;
use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use App\Jobs\WelcomeNotificationJob;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\User\OwnerResource;
use App\Models\OwnerAssociation;
use Filament\Facades\Filament;

class ListOwners extends ListRecords
{
    protected static string $resource = OwnerResource::class;
    protected function getTableQuery(): Builder
    {
        $BuildingId = Building::where('owner_association_id',auth()->user()->owner_association_id)->pluck('id');
        $flatsId = Flat::whereIn('building_id',$BuildingId)->pluck('id');
        $flatowners = FlatOwners::whereIn('flat_id',$flatsId)->pluck('owner_id');
        return parent::getTableQuery()->whereIn('id',$flatowners);
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
            Action::make('Notify Owners')
                ->button()
                ->form([
                    Select::make('building_id')
                        ->options(function(){
                            return Building::where('owner_association_id',auth()->user()->owner_association_id)->pluck('name','id');
                        })
                        ->searchable()
                        ->preload()
                ])
                ->action(function (array $data){
                    $buildingname = Building::find($data['building_id'])->name;
                    $flats = Flat::where('building_id',$data['building_id'])->pluck('id');
                    if($flats->first() == null){
                        Notification::make()
                                ->title("No Data for building")
                                ->danger()
                                ->body("There are no flats for the building.")
                                ->send();
                            return;
                    }
                    $flatowners = FlatOwners::whereIn('flat_id',$flats)->pluck('owner_id');
                    if($flatowners->first() == null){
                        Notification::make()
                                ->title("No Data for Flat")
                                ->danger()
                                ->body("There are no flatowners for the flats.")
                                ->send();
                            return;
                    }
                    $residentsemail = ApartmentOwner::whereIn('id',$flatowners)->select('name','email')->distinct()->get();
                    if($residentsemail->first() == null){
                        Notification::make()
                                ->title("No Data for Flatowners in ApartmentOwner")
                                ->danger()
                                ->body("There are no owners for the flatowners.")
                                ->send();
                            return;
                    }
                    $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                    $emailCredentials = OwnerAssociation::find($tenant)->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');

                    foreach ($residentsemail as $value) {
                        WelcomeNotificationJob::dispatch($value->email, $value->name,$buildingname,$emailCredentials);
                    }
                    Notification::make()
                        ->title("Successfully Send Mail")
                        ->success()
                        ->body("sent mail to all the owners asking them to download the app.")
                        ->send();

                })
                ->slideOver()
        ];
    }
}
