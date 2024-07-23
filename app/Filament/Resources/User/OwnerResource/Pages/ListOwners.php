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
use App\Models\AccountCredentials;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Facades\Filament;

class ListOwners extends ListRecords
{
    protected static string $resource = OwnerResource::class;
    protected function getTableQuery(): Builder
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            return parent::getTableQuery();
        }
        // $BuildingId = Building::where('owner_association_id',Filament::getTenant()?->id ?? auth()->user()->owner_association_id)->pluck('id');
        $flatsId = Flat::where('owner_association_id', Filament::getTenant()?->id ?? auth()->user()->owner_association_id)->pluck('id');
        $flatowners = FlatOwners::whereIn('flat_id', $flatsId)->pluck('owner_id');
        return parent::getTableQuery()->whereIn('id', $flatowners);
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
            Action::make('Notify Owners')
                ->button()
                ->form([
                    Select::make('building_id')
                        ->options(function () {
                            if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                return Building::all()->pluck('name', 'id');
                            } else {
                                return Building::where('owner_association_id', auth()->user()->owner_association_id)
                                    ->pluck('name', 'id');
                            }
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->label('Building')
                ])
                ->action(function (array $data) {
                    $buildingname = Building::find($data['building_id'])->name;
                    $flats = Flat::where('building_id', $data['building_id'])->pluck('id');
                    if ($flats->first() == null) {
                        Notification::make()
                            ->title("No Data for building")
                            ->danger()
                            ->body("There are no flats for the building.")
                            ->send();
                        return;
                    }
                    $flatowners = FlatOwners::whereIn('flat_id', $flats)->pluck('owner_id');
                    if ($flatowners->first() == null) {
                        Notification::make()
                            ->title("No Data for Flat")
                            ->danger()
                            ->body("There are no flatowners for the flats.")
                            ->send();
                        return;
                    }
                    $residentsemail = ApartmentOwner::whereIn('id', $flatowners)->select('name', 'email')->distinct()->get();
                    if ($residentsemail->first() == null) {
                        Notification::make()
                            ->title("No Data for Flatowners in ApartmentOwner")
                            ->danger()
                            ->body("There are no owners for the flatowners.")
                            ->send();
                        return;
                    }
                    $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                    // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');

                    $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
                    $mailCredentials = [
                        'mail_host' => $credentials->host ?? env('MAIL_HOST'),
                        'mail_port' => $credentials->port ?? env('MAIL_PORT'),
                        'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
                        'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
                        'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                        'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
                    ];

                    $OaName = Filament::getTenant()?->name ?? 'Admin';

                    foreach ($residentsemail as $value) {
                        WelcomeNotificationJob::dispatch($value->email, $value->name, $buildingname, $mailCredentials, $OaName);
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
