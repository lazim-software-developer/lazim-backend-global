<?php

namespace App\Filament\Resources\AccountsManagerResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use App\Models\Master\Role;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AccountsManagerResource;

class ListAccountsManagers extends ListRecords
{
    protected static string $resource = AccountsManagerResource::class;
    protected static ?string $title = 'Accounts Manager';
    protected function getHeaderActions(): array
    {
        $Accountmanager = User::where(['owner_association_id'=> auth()->user()->owner_association_id, 'role_id' => Role::where('name', 'Accounts Manager')->first()->id])->exists();
        if (!$Accountmanager) {
            return [
                Actions\CreateAction::make(),
            ];
        }
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where(['owner_association_id' => auth()->user()->owner_association_id, 'role_id' => Role::where('name', 'Accounts Manager')->first()->id]);
    }
}
