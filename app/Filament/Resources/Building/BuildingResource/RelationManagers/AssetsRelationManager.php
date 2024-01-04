<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Asset;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Service;
use App\Forms\Components\QrCode;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'assets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                        TextInput::make('name')
                            ->rules([
                                'max:50',
                                'regex:/^[a-zA-Z\s]*$/',
                                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if (Asset::where('building_id', $get('building_id'))->where('name', $value)->exists()) {
                                        $fail('The Name is already taken for this Building.');
                                    }
                                },
                            ])
                            ->required()
                            ->label('Asset Name'),
                        TextInput::make('location')
                            ->required()
                            ->rules(['max:50', 'regex:/^[a-zA-Z0-9\s]*$/'])
                            ->label('Location'),
                        Textarea::make('description')
                            ->label('Description')
                            ->rules(['max:100', 'regex:/^[a-zA-Z0-9\s]*$/']),
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->options(function () {
                                return Service::where('type', 'vendor_service')->where('active', 1)->pluck('name', 'id');
                            })
                            ->required()
                            ->preload()
                            ->searchable()
                            ->label('Service'),
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Asset Name'),
                TextColumn::make('location')->label('Location'),
                TextColumn::make('description')->label('Description'),
                TextColumn::make('service.name')->label('Service Name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }
}
