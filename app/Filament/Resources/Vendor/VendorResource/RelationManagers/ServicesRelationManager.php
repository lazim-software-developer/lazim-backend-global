<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use App\Models\Master\Service;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Name')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Toggle::make('active')
                        ->rules(['boolean'])
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->limit(50),
                Tables\Columns\TextColumn::make('price')->limit(50),
                Tables\Columns\IconColumn::make('active')->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DetachAction::make() ->label('Remove'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add')
                    ->recordSelect(function (RelationManager $livewire) {
                        $vendorId = $livewire->ownerRecord->id;

                        // Get all the Servicess
                        $allServices = Service::all()->pluck('id')->toArray();
                        $existingServices =  DB::table('service_vendor')
                            ->where('vendor_id', $vendorId)
                            ->whereIn('service_id', $allServices)->pluck('service_id')->toArray();
                        $notSelected = Service::all()->whereNotIn('id', $existingServices)->pluck('name', 'id')->toArray();
                        return Select::make('recordId')
                            ->label('Services')
                            ->options($notSelected)
                            ->searchable()
                            ->required()
                            ->preload();
                    })
            ]);
    }
}
