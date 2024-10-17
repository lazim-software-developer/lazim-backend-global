<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TechnicianVendorResource\Pages;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TechnicianVendorResource extends Resource
{
    protected static ?string $model = TechnicianVendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('technician_id')
                    ->preload()
                    ->native(false)
                    ->required()
                    ->label('Technician')
                    ->placeholder('Select Technician')
                    ->options(User::whereHas('role', function ($query) {
                        $query->where('name', 'Technician');
                    })->pluck('first_name', 'id')->toArray()),

                TextInput::make('technician_number')
                    ->placeholder('Enter Technician number'),

                Select::make('vendor_id')
                    ->label('Facility Manager')
                    ->native(false)
                    ->preload()
                    ->placeholder('Select Facility Manager')
                    ->options(Vendor::where('owner_association_id', auth()->user()->owner_association_id)
                            ->pluck('name', 'id')->toArray())
                    ->required(),

                TextInput::make('position')
                    ->maxLength(191),
                Toggle::make('active')
                    ->default(true)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('technician_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Technician')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Facility Manager')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('position')
                    ->default('NA')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index'  => Pages\ListTechnicianVendors::route('/'),
            'create' => Pages\CreateTechnicianVendor::route('/create'),
            'edit'   => Pages\EditTechnicianVendor::route('/{record}/edit'),
        ];
    }
}
