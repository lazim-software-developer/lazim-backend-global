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
                Tables\Columns\IconColumn::make('active')->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DetachAction::make() ->label('Remove'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add')
                    ->recordSelect(function () {
                        // Get all the Services
                        $allServices = Service::all()->pluck('name', 'id')->toArray();

                        // Get the IDs of the selected Services
                        $selectedServiceIds = DB::table('service_vendor')->pluck('service_id')->toArray();

                        // Filter out the selected Services from the list of all Services
                        $availableServices = array_diff_key($allServices, array_flip($selectedServiceIds));

                        return Select::make('recordId')
                            ->label('Services')
                            ->options($availableServices)
                            ->searchable()
                            ->required()
                            ->preload();
                    })
            ]);
    }
}
