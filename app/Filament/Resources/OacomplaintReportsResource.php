<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OacomplaintReportsResource\Pages;
use App\Models\BuildingVendor;
use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use App\Models\Master\Role;
use App\Models\OacomplaintReports;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OacomplaintReportsResource extends Resource
{
    protected static ?string $model = OacomplaintReports::class;

    protected static ?string $modelLabel = 'Oa Complaint Reports';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('type')
                    ->options([
                        'Technician' => 'Technician',
                        'Vendor'     => 'Vendor',
                        'Gatekeeper' => 'Gatekeeper',
                    ])
                    ->afterStateUpdated(function (Set $set) {
                        $set('user_id', null);
                    })
                    ->searchable()
                    ->live()
                    ->required(),
                Select::make('building_id')
                    ->relationship('building', 'name')
                    ->preload()
                    ->required()
                    ->live()
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            return Building::where('owner_association_id', auth()->user()->owner_association_id)
                                ->pluck('name', 'id');
                        }
                        return Building::whereNotNull('name')->pluck('name', 'id');
                    })
                    ->afterStateUpdated(function (Set $set) {
                        $set('user_id', null);
                    })
                    ->live()
                    ->searchable(),
                Select::make('user_id')
                    ->relationship('user', 'first_name')
                    ->options(function (Get $get) {
                        if ($get('type') === 'Technician') {
                            $buildingVendor   = BuildingVendor::where('building_id', $get('building_id'))->where('active', 1)->pluck('vendor_id');
                            $technicianVendor = TechnicianVendor::whereIn('vendor_id', $buildingVendor)->pluck('technician_id');
                            return User::whereIn('id', $technicianVendor)->pluck('first_name', 'id');
                        }
                        if ($get('type') === 'Vendor') {
                            $buildingVendor = BuildingVendor::where('building_id', $get('building_id'))->where('active', 1)->pluck('vendor_id');
                            $Vendors        = Vendor::whereIn('id', $buildingVendor)->where('status', 'approved')->pluck('owner_id');
                            return User::whereIn('id', $Vendors)->pluck('first_name', 'id');
                        }
                        if ($get('type') === 'Gatekeeper') {
                            $user = BuildingPoc::where('building_id', $get('building_id'))->where('active', 1)->pluck('user_id');
                            return User::whereIn('id', $user)->pluck('first_name', 'id');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('issue')
                    ->maxLength(350)
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->disk('s3')
                    ->rules('file|mimes:jpeg,jpg,png|max:2048')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->image()
                    ->maxSize(2048)
                    ->required()
                    ->label('Image')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->searchable(),
                TextColumn::make('building.name')->searchable(),
                TextColumn::make('user.first_name')->searchable(),
                TextColumn::make('issue')->searchable()->limit(20),
                ImageColumn::make('image')->disk('s3'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
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
            'index'  => Pages\ListOacomplaintReports::route('/'),
            'create' => Pages\CreateOacomplaintReports::route('/create'),
            'edit'   => Pages\EditOacomplaintReports::route('/{record}/edit'),
        ];
    }
}
