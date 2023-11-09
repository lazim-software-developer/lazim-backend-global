<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\Building\Complaint;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComplaintsRelationManager extends RelationManager
{
    protected static string $relationship = 'complaint';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('building.name')
                ->default('NA')
                ->searchable()
                ->limit(50),
            TextColumn::make('user.first_name')
                ->toggleable()
                ->default('NA')
                ->searchable()
                ->limit(50),
            TextColumn::make('category')
                ->toggleable()
                ->default('NA')
                ->searchable()
                ->limit(50),
            TextColumn::make('complaint')
                ->toggleable()
                ->default('NA')
                ->searchable(),
            TextColumn::make('status')
                ->toggleable()
                ->default('NA')
                ->searchable()
                ->limit(50),
        ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('Update Status')
                    ->visible(fn ($record) => $record->status === 'open')
                    ->button()
                    ->form([
                        Select::make('status')
                            ->options([
                                'open'   => 'Open',
                                'resolved' => 'Resolved',
                            ])
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->visible(function (callable $get) {
                                if ($get('status') == 'resolved') {
                                    return true;
                                }
                                return false;
                            }),
                    ])
                    ->fillForm(fn (Complaint $record): array => [
                        'status' => $record->status,
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (Complaint $record, array $data): void {
                        if ($data['status'] == 'resolved') {
                            $record->status = $data['status'];
                            $record->remarks = $data['remarks'];
                            $record->save();
                        } else {
                            $record->status = $data['status'];
                            $record->save();
                        }
                    })
                    ->slideOver()
            ]);
    }
}
