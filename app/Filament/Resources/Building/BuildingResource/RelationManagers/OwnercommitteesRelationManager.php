<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\OwnerCommittee;
use App\Models\Building\FlatTenant;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class OwnercommitteesRelationManager extends RelationManager
{
    protected static string $relationship = 'ownercommittees';
    protected static ?string $modelLabel = 'Owner Committes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Owner Committes';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('active')
                    ->required(),
                Select::make('user_id')
                    ->rules(['exists:users,id'])
                    ->relationship('user', 'first_name')
                    ->preload()
                    ->options(function (RelationManager $livewire) {
                        $alreadyExits = OwnerCommittee::where('building_id', $livewire->ownerRecord->id)->pluck('user_id');
                        $tenantId = FlatTenant::where('building_id', $livewire->ownerRecord->id)->whereNotIn('tenant_id', $alreadyExits)->pluck('tenant_id');
                        return User::whereIn('id', $tenantId)->pluck('first_name', 'id');
                    })
                    ->disabled()
                    ->searchable()
                    ->label('Name'),
                Select::make('user_id')
                    ->rules(['exists:users,id'])
                    ->relationship('user', 'email')
                    ->preload()
                    ->options(function (RelationManager $livewire) {
                        $alreadyExits = OwnerCommittee::where('building_id', $livewire->ownerRecord->id)->pluck('user_id');
                        $tenantId = FlatTenant::where('building_id', $livewire->ownerRecord->id)->whereNotIn('tenant_id', $alreadyExits)->pluck('tenant_id');
                        return User::whereIn('id', $tenantId)->pluck('first_name', 'id');
                    })
                    ->disabled()
                    ->searchable()
                    ->label('Email'),
                Select::make('user_id')
                    ->rules(['exists:users,id'])
                    ->relationship('user', 'phone')
                    ->preload()
                    ->options(function (RelationManager $livewire) {
                        $alreadyExits = OwnerCommittee::where('building_id', $livewire->ownerRecord->id)->pluck('user_id');
                        $tenantId = FlatTenant::where('building_id', $livewire->ownerRecord->id)->whereNotIn('tenant_id', $alreadyExits)->pluck('tenant_id');
                        return User::whereIn('id', $tenantId)->pluck('first_name', 'id');
                    })
                    ->disabled()
                    ->searchable()
                    ->label('phone'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                TextColumn::make('user.first_name')->searchable(),
                TextColumn::make('user.email')->searchable(),
                TextColumn::make('user.phone')->searchable(),
                TextColumn::make('building.name')->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                Action::make('Add')
                    // ->visible(fn(RelationManager $livewire) => BuildingPoc::where('building_id', $livewire->ownerRecord->id)->where('active', 1)->count() == 0)
                    ->button()
                    ->form([

                        Select::make('user_id')
                            ->rules(['exists:users,id'])
                            ->relationship('user', 'first_name')
                            ->preload()
                            ->options(function (RelationManager $livewire) {
                                $alreadyExits = OwnerCommittee::where('building_id', $livewire->ownerRecord->id)->pluck('user_id');
                                $tenantId = FlatTenant::where('building_id', $livewire->ownerRecord->id)->whereNotIn('tenant_id', $alreadyExits)->pluck('tenant_id');
                                return User::whereIn('id', $tenantId)->pluck('first_name', 'id');
                            })
                            ->searchable()
                            ->label('User'),
                        Toggle::make('active')
                            ->rules(['boolean'])
                            ->default(true),
                        //
                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                    ])
                    ->action(function (array $data): void {

                        $user = OwnerCommittee::create([
                            'building_id' => $data['building_id'],
                            'user_id' => $data['user_id'],
                            'active' => $data['active'],
                        ]);
                    })
                    ->slideOver()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }
}
