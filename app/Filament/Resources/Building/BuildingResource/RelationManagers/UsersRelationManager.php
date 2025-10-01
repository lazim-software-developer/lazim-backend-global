<?php


namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms\Get;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name'),
                TextColumn::make('email'),
                TextColumn::make('roles.name')->label('Role')->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                Action::make('Assign Users by Role')
                    ->form([
                        Select::make('role_id')
                            ->label('Select Role')
                            ->options(
                                fn() => Role::where('owner_association_id', auth()->user()->owner_association_id)
                                    ->whereNotIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'OA', 'Owner', 'Managing Director', 'Vendor'])
                                    ->pluck('name', 'id')
                            )
                            ->reactive()
                            ->required()
                            ->afterStateUpdated(fn($state, callable $set) => $set('user_ids', null)),

                        Select::make('user_id')
                            ->label('Select Users')
                            ->multiple()
                            ->required()
                            ->options(function (Get $get) {
                                $role = Role::where('owner_association_id', auth()->user()->owner_association_id)
                                    ->find($get('role_id'));

                                // Get users for the selected role
                                $roleUsers = $role?->users()->pluck('first_name', 'id') ?? collect();

                                // Get already attached users for this building
                                $alreadyAttachedUserIds = $this->ownerRecord
                                    ->users()
                                    ->pluck('users.id')
                                    ->toArray();

                                // Remove already attached users from the list
                                $availableUsers = $roleUsers->except($alreadyAttachedUserIds);

                                // Ensure already selected users remain in the list
                                $selectedUserIds = $get('user_id') ?? [];
                                $selectedUsers = User::whereIn('id', (array) $selectedUserIds)
                                    ->pluck('first_name', 'id');

                                return $availableUsers->union($selectedUsers);
                            }),
                    ])->action(function (array $data, $livewire) {
                        $building = $livewire->ownerRecord;

                        $selectedUserIds = $data['user_id'];

                        $building->users()->syncWithoutDetaching($selectedUserIds);

                        Notification::make()
                            ->title('Users assigned successfully.')
                            ->success()
                            ->send();

                        return;
                    })
                    ->modalWidth('lg')
                    ->modalHeading('Assign Users by Role'),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
