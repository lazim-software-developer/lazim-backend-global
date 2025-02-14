<?php

namespace App\Filament\Resources\OwnerAssociationResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use App\Models\Master\Role;
use Filament\Actions\Action;
use App\Models\ApartmentOwner;
use App\Models\OwnerAssociation;
use App\Jobs\WelcomeNotificationJob;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OwnerAssociationResource;

class ListOwnerAssociations extends ListRecords
{
    protected static string $resource = OwnerAssociationResource::class;
    protected ?string $heading        = 'Owner Association';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            return parent::getTableQuery()->where('role','OA');
        }
        return parent::getTableQuery()->where('id', auth()->user()?->owner_association_id)->where('role','OA');
    }
}
