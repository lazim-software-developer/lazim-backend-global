<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Tables;
use App\Models\Asset;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Master\Service;
use App\Forms\Components\QrCode;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AssetResource\Pages;
use App\Models\OwnerAssociation;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Vendor Management';
    protected static ?string $modelLabel      = 'Assets';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->required()
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::pluck('name', 'id');
                                }
                                $oaId = auth()->user()?->owner_association_id;
                                return Building::where('owner_association_id', $oaId)
                                    ->pluck('name', 'id');
                            })
                            ->preload()
                            ->searchable()
                            ->live()
                            ->label('Building'),
                        TextInput::make('name')
                            // ->rules([
                            //     'max:50',
                            //     'regex:/^[a-zA-Z\s]*$/',
                            //     fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            //         if (Asset::where('building_id', $get('building_id'))->where('name', $value)->exists()) {
                            //             $fail('The Name is already taken for this Building.');
                            //         }
                            //     },
                            // ])
                            ->maxLength(20)
                            ->required()
                            ->label('Asset name'),
                        TextInput::make('floor')
                            ->required()
                            ->rules(['max:50']),
                        TextInput::make('location')
                            ->required()
                            ->rules(['max:50', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9\s!@#$%^&*_+\-=,.]*$/'])
                            ->label('Spot'),
                        TextInput::make('division')
                            ->required()
                            ->rules(['max:50']),
                        TextInput::make('discipline')
                            ->required()
                            ->rules(['max:50']),
                        TextInput::make('frequency_of_service')
                            ->required()->integer()->suffix('days')->minValue(1),
                        Textarea::make('description')
                            ->label('Description')
                            ->rules(['max:100', 'regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9\s!@#$%^&*_+\-=,.]*$/']),
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->options(function () {
                                return Service::all()->where('type', 'vendor_service')->where('active', 1)->pluck('name', 'id');
                            })
                            // ->default(Service::where('name', 'MEP Services')->where('active', 1)->first()->id)
                            // ->disabled()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->label('Service'),
                        TextInput::make('asset_code')
                            ->visible(function (callable $get) {
                                if ($get('asset_code') != null) {
                                    return true;
                                }
                                return false;
                            }),
                    ]),
                QrCode::make('qr_code')
                    ->label('QR Code')
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2,
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->label('Asset name')->sortable(),
                TextColumn::make('description')->searchable()->default('NA')->label('Description'),
                TextColumn::make('location')->label('Location'),
                TextColumn::make('service.name')->searchable()->label('Service')->sortable(),
                TextColumn::make('building.name')->searchable()->label('Building')->sortable(),
                TextColumn::make('asset_code'),
                TextColumn::make('vendors.name')->default('NA')
                    ->searchable()->label('Vendor'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', Filament::getTenant()?->id);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
                SelectFilter::make('service_id')
                    ->relationship('service', 'name', fn(Builder $query) => $query->where('type', 'vendor_service'))
                    ->searchable()
                    ->preload()
                    ->label('Service'),
                Filter::make('Vendor')
                    ->form([
                        Select::make('vendor')
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                                    return OwnerAssociation::find(Filament::getTenant()?->id)->vendors->where('pivot.status', 'approved')->pluck('name', 'id');
                                } else {
                                    return Vendor::pluck('name', 'id');
                                }
                            })
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['vendor'])) {
                            $assets = DB::connection('mysql')->table('asset_vendor')->where('vendor_id', $data['vendor'])->pluck('asset_id');
                            return $query->whereIn('id', $assets);
                        }
                        return $query;
                    }),
                TernaryFilter::make('id')
                    ->label('Assigned')
                    ->placeholder('All assets')
                    ->trueLabel('Assigned assets')
                    ->falseLabel('Unassigned assets')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('vendors'),
                        false: fn(Builder $query) => $query->whereDoesntHave('vendors'),
                        blank: fn(Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    )
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('attach')
                        ->form([
                            Select::make('vendor_id')
                                ->required()
                                ->relationship('vendors', 'name')
                                ->options(function () {
                                    $oaId = auth()->user()?->owner_association_id;
                                    return Vendor::whereHas('ownerAssociation', function ($query) {
                                        $query->where('owner_association_id', Filament::getTenant()->id)
                                            ->where('status', 'approved');
                                    })
                                        ->pluck('name', 'id');
                                })
                        ])
                        ->action(function (Collection $records, array $data) {
                            $vendorId = $data['vendor_id'];
                            // dd($records,$vendorId);
                            foreach ($records as $record) {
                                // dd($record->vendors()->syncWithoutDetaching([$vendorId]));
                                $record->vendors()->sync([$vendorId]);
                            }
                            Notification::make()
                                ->title("Vendor attached successfully")
                                ->success()
                                ->send();
                        })->label('Attach Vendor')
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'view' => Pages\ViewAsset::route('/{record}'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),

        ];
    }
}
