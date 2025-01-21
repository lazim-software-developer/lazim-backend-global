<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetMaintenanceResource\Pages;
use App\Filament\Resources\AssetMaintenanceResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\TechnicianAssets;
use App\Models\User\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class AssetMaintenanceResource extends Resource
{
    protected static ?string $model = AssetMaintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel      = 'Assets Maintenance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('maintenance_date')
                    ->required(),
                Select::make('building_id')
                    ->relationship('building','name'),
                // TextInput::make('building'),
                TextInput::make('maintained_by'),
                TextInput::make('status'),
                TextInput::make('asset'),
                TextInput::make('technician'),
                TextInput::make('vendor'),
                ViewField::make('Service history')
                    ->view('forms.components.comments')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $buildings = Building::where('owner_association_id',auth()->user()?->owner_association_id)->pluck('id');
        return $table
            // ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('building_id', $buildings)->orderBy('maintenance_date','desc')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('building.name'),
                TextColumn::make('technicianAsset.asset.name'),
                TextColumn::make('maintenance_date'),
                TextColumn::make('user.first_name')->label('Maintained by'),
                TextColumn::make('status'),
            ])
            ->filters([
                SelectFilter::make('building_id')
                    ->label('Building')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } else {
                            $buildingId = DB::table('building_owner_association')->where('owner_association_id',auth()->user()?->owner_association_id)->where('active',true)->pluck('building_id');
                            return Building::whereIn('id',$buildingId)->pluck('name', 'id');
                        }
                    })
                    ->preload()
                    ->searchable(),
                    SelectFilter::make('maintained_by')
                    ->label('Technician')
                    ->options(function(){
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            $technicianId =TechnicianAssets::pluck('technician_id');
                            return User::whereIn('id',$technicianId)->pluck('first_name','id');
                        } else {
                            $technicianId =TechnicianAssets::where('owner_association_id',auth()->user()->owner_association_id)->pluck('technician_id');
                            return User::whereIn('id',$technicianId)->pluck('first_name','id');
                        }

                    })
                    ->searchable()
                    ->preload(),

                    Filter::make('asset')
                        ->form([
                            Select::make('asset')
                                ->label('Asset')
                                ->options(function () {
                                    if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                        $assetIds = TechnicianAssets::pluck('asset_id');
                                        return Asset::whereIn('id', $assetIds)->pluck('name', 'id');
                                    } else {
                                        $assetIds = TechnicianAssets::where('owner_association_id', auth()->user()->owner_association_id)->pluck('asset_id');
                                        return Asset::whereIn('id', $assetIds)->pluck('name', 'id');
                                    }
                                })
                                ->searchable()
                                ->placeholder('Select Asset'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (!empty($data['asset'])) {
                                $query->whereHas('technicianAsset', function ($query) use ($data) {
                                    $query->where('asset_id', $data['asset']);
                                });
                            }
                            
                            return $query;
                        })

                    
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

//     public static function infolist(Infolist $infolist): Infolist
// {
//     return $infolist
//         ->schema([
//             Section::make()->columns([
//                 'sm' => 2,
//                 'xl' => 3,
//                 '2xl' => 3,
//             ])->schema([
//                 TextEntry::make('building.name'),
//                 TextEntry::make('maintenance_date'),
//                 TextEntry::make('user.first_name')->label('Maintained by'),
//                 TextEntry::make('status'),
//                 TextEntry::make('technicianAsset.asset')->formatStateUsing(fn ($state) => json_decode($state)->name)->label('Asset'),
//                 TextEntry::make('technicianAsset.user')->formatStateUsing(fn ($state) => json_decode($state)->first_name)->label('Technician'),
//                 TextEntry::make('technicianAsset.vendor')->formatStateUsing(fn ($state) => json_decode($state)->name)->label('Vendor'),
//                 ViewEntry::make('media')->view('infolists.components.asset-maintenance-media')

//                 ])
//         ]);
// }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetMaintenances::route('/'),
            // 'create' => Pages\CreateAssetMaintenance::route('/create'),
            'view' => Pages\ViewAssetMaintenance::route('/{record}'),
            // 'edit' => Pages\EditAssetMaintenance::route('/{record}/edit'),
        ];
    }
}
