<?php

namespace App\Filament\Resources\BuildingResource\RelationManagers;

use App\Models\Master\Service;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('start_date')->label('Start Date'),
                TextInput::make('end_date')->label('End Date'),
                TextInput::make('contract_type')->label('Contract Type'),
                TextInput::make('amount')->label('amount'),
                TextInput::make('budget_amount')->label('Budget Amount'),
                TextInput::make('vendor_id')
                ->formatStateUsing(function($state){
                    return Vendor::where('id',$state)->value('name');
                })
                ->label('Vendor'),
                TextInput::make('service_id')
                ->label('Service')
                ->formatStateUsing(function($state){
                    return Service::where('id',$state)->pluck('name');
                })
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service.name')
            ->columns([
                Tables\Columns\TextColumn::make('start_date')->label('Start Date')
                // ->formatStateUsing(function ($state) {
                //     return Carbon::parse($state)->format('Y-m-d');
                // })
                ,
                Tables\Columns\TextColumn::make('end_date')->label('End Date')
                // ->formatStateUsing(function ($state) {
                //     return Carbon::parse($state)->format('Y-m-d');
                // })
                ,
                Tables\Columns\TextColumn::make('contract_type')->label('Contract Type'),
                // Tables\Columns\TextColumn::make('amount')->label('Amount'),
                // Tables\Columns\TextColumn::make('budget_amount')->label('Budget Amount'),
                Tables\Columns\TextColumn::make('vendor.name')->label('Vendor'),
                Tables\Columns\TextColumn::make('service.name')->label('Service'),
                TextColumn::make('remaining_days')
                    ->label('Remaining Days')
                    ->getStateUsing(function ($record) {
                        if ($record->end_date) {
                            $end_date = Carbon::parse($record->end_date);
                            $start_date = Carbon::parse($record->start_date);
                            $now = now();

                            if ($end_date->lessThan($now)) {
                                return 0;
                            }

                            $start_point = $start_date->greaterThan($now) ? $start_date : $now;
                            $remaining_days = $end_date->diffInDays($start_point);
                            return $remaining_days < 0 ? 0 : $remaining_days;
                        }
                        return 'NA';
                    }),

                ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
