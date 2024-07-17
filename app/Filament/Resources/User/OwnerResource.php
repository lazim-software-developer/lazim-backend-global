<?php

namespace App\Filament\Resources\User;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use App\Models\FlatOwners;
use App\Models\User\Owner;
use Filament\Tables\Table;
use App\Models\Building\Flat;
use App\Models\ApartmentOwner;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Filters\buildingFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\User\OwnerResource\Pages;
use App\Filament\Resources\User\OwnerResource\RelationManagers;
use App\Filament\Resources\User\OwnerResource\RelationManagers\UserDocumentsRelationManager;
use App\Jobs\WelcomeNotificationJob;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class OwnerResource extends Resource
{
    protected static ?string $model = ApartmentOwner::class;
    protected static ?string $modelLabel = 'Owners';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2
            ])
                ->schema([

                    TextInput::make('owner_number')
                        ->numeric()
                        ->required()
                        ->placeholder('Owner Number'),
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Name'),
                    TextInput::make('mobile')
                        ->rules(['regex:/^(971)(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                        ->nullable()
                        ->placeholder('Mobile'),
                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'])
                        ->required()
                        ->placeholder('Email'),
                    TextInput::make('passport')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Passport'),
                    TextInput::make('emirates_id')
                        ->numeric()
                        ->required()
                        ->placeholder('Emirates Id'),
                    Repeater::make('flatOwners')
                        ->relationship()
                        ->schema([
                            Select::make('flat_id')
                                ->relationship('flat', 'property_number')
                                ->preload()
                                ->searchable()
                                ->label('Unit Number'),
                            // ViewField::make('Building')
                            //     ->view('forms.components.building-name-owner')
                        ])
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ])
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->default('NA')
                    ->label('Name')
                    ->limit(50),
                Tables\Columns\TextColumn::make('mobile')
                    ->searchable()
                    ->default('NA')
                    ->label('Mobile')
                    ->limit(50),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->default('NA')
                    ->label('Email')
                    ->limit(50),
                ViewColumn::make('Unit')->view('tables.columns.apartment-ownerflat')->alignCenter(),
                ViewColumn::make('Building')->view('tables.columns.apartment-ownerbuilding')->alignCenter(),
            ])
            ->actions([
                Action::make('Notify Owner')
                ->button()
                ->action(function (array $data,$record){
                    $flatID = FlatOwners::where('owner_id',$record->id)->value('flat_id');
                    $buildingname = Flat::where('id',$flatID)->first()->building->name;
                    $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                    $emailCredentials = OwnerAssociation::find($tenant)->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
                    $OaName = Filament::getTenant()->name;

                    if($record->email==null){
                        Notification::make()
                        ->title('Email not found')
                        ->success()
                        ->send();
                    }else{
                        WelcomeNotificationJob::dispatch($record->email, $record->name,$buildingname,$emailCredentials,$OaName);
                        Notification::make()
                        ->title("Successfully Sent Mail")
                        ->success()
                        ->body("Sent mail to owner asking him to download the app.")
                        ->send();
                    }
                })
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('building')
                    ->form([
                        Select::make('Building')
                            ->searchable()
                            ->options(function () {
                                if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
                                    return Building::all()->pluck('name', 'id');
                                }
                                else{
                                    return Building::where('owner_association_id', auth()->user()->owner_association_id)
                                    ->pluck('name', 'id');
                                } 
                            })
                            ->placeholder('Select Building'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['Building']),
                            function ($query) use ($data) {
                                $query->whereHas('flatOwners.flat', function ($query) use ($data) {
                                    $query->where('building_id', $data['Building']);
                                });
                            }
                        );
                    }),
                    Filter::make('Property Number')
                    ->form([
                        TextInput::make('property_number')
                            ->placeholder('Search Unit Number')->label('Unit')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['property_number'])) {
                            $query->whereHas('flatOwners.flat', function ($query) use ($data) {
                                $query->where('property_number', 'like', '%' . $data['property_number'] . '%');
                            });
                        }
                        return $query;
                    })
                ],layout: FiltersLayout::AboveContent)->filtersFormColumns(2)
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //UserDocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwners::route('/'),
            //'create' => Pages\CreateOwner::route('/create'),
            'view' => Pages\ViewOwner::route('/{record}'),
            // 'edit' => Pages\EditOwner::route('/{record}/edit'),
        ];
    }
}
