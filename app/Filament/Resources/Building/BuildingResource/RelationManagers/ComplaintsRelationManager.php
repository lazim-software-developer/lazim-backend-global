<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\Building\Complaint;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ComplaintsRelationManager extends RelationManager
{
    protected static string $relationship = 'complaint';

    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2
            ])
            ->schema([
                Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('user_id')
                            ->relationship('user', 'id')
                            ->options(function () {
                                $tenants = DB::table('flat_tenants')->pluck('tenant_id');
                                // dd($tenants);
                                return DB::table('users')
                                    ->whereIn('users.id', $tenants)
                                    ->select('users.id', 'users.first_name')
                                    ->pluck('users.first_name', 'users.id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User'),
                        FileUpload::make('photo')
                            ->disk('s3')
                            ->directory('dev')
                            ->maxSize(2048)
                            ->nullable(),
                        TextInput::make('complaint')
                            ->placeholder('Complaint'),
                        TextInput::make('complaint_details')
                            ->placeholder('Complaint Details'),
            ])
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
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
                ActionsAction::make('Update Status')
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
                            })
                            ->required(),
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
            ]);
    }
}
