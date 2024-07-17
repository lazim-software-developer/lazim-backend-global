<?php

namespace App\Filament\Resources\User\TenantResource\Pages;

use Filament\Actions;
use App\Models\MollakTenant;
use Filament\Actions\Action;
use App\Models\Building\Building;
use App\Jobs\WelcomeNotificationJob;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\User\TenantResource;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Facades\Filament;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->whereIn('building_id', Building::where('owner_association_id', Filament::getTenant()?->id ??auth()->user()->owner_association_id)->pluck('id'));
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
            Action::make('Notify Tenants')
                ->button()
                ->form([
                    Select::make('building_id')
                        ->options(function () {
                            if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                return Building::all()->pluck('name', 'id');
                            }
                            else{
                                return Building::where('owner_association_id', auth()->user()->owner_association_id)
                                ->pluck('name', 'id');
                            } 
                        })
                        ->searchable()
                        ->preload()
                ])
                ->action(function (array $data) {
                    $buildingname = Building::find($data['building_id'])->name;
                    $residents = MollakTenant::where('building_id', $data['building_id'])->select('name', 'email')->distinct()->get();
                    $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                    $emailCredentials = OwnerAssociation::find($tenant)->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
                    if ($residents->first() == null) {
                        Notification::make()
                            ->title("No Data for Building in MollakTenant")
                            ->danger()
                            ->body("There are no tenants for the building.")
                            ->send();
                        return;
                    }
                    $OaName = Filament::getTenant()->name;
                    
                    foreach ($residents as $value) {
                        WelcomeNotificationJob::dispatch($value->email, $value->name, $buildingname, $emailCredentials,$OaName);
                    }
                    Notification::make()
                        ->title("Successfully Send Mail")
                        ->success()
                        ->body("sent mail to all the tenants asking them to download the app.")
                        ->send();
                })
                ->slideOver()
        ];
    }
}
