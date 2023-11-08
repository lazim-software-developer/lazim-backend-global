<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FitOutFormsDocumentResource\Pages;
use App\Models\Forms\FitOutForm;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FitOutFormsDocumentResource extends Resource
{
    protected static ?string $model = FitOutForm::class;

    protected static ?string $modelLabel = 'FitOut';
    protected static ?string $navigationGroup = 'Forms Document';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('contractor_name')
                        ->required()
                        ->label('Contractor Name'),
                    TextInput::make('email')
                        ->label('Email')
                        ->required()
                        ->placeholder('Email'),
                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->required()
                        ->placeholder('Phone Number'),
                    Select::make('building_id')
                        ->relationship('building', 'name')
                        ->preload()
                        ->searchable()
                        ->label('Building Name'),
                    Select::make('flat_id')
                        ->relationship('flat', 'property_number')
                        ->preload()
                        ->searchable()
                        ->label('Property No'),
                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->required()
                        ->preload()
                        ->searchable()
                        ->label('User'),
                    TextInput::make('status')
                        ->required()
                        ->label('Status'),
                    TextInput::make('remarks')
                        ->required()
                        ->label('Remarks'),
                    Toggle::make('no_objection'),
                    Toggle::make('undertaking_of_waterproofing'),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                IconColumn::make('no_objection')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
                IconColumn::make('undertaking_of_waterproofing')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Action::make('Update Status')
                    ->visible(fn ($record) => $record->status === null)
                    ->button()
                    ->form([
                        Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->visible(function (callable $get) {
                                if ($get('status') == 'rejected') {
                                    return true;
                                }
                                return false;
                            }),
                    ])
                    ->fillForm(fn (FitOutForm $record): array => [
                        'status' => $record->status,
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (FitOutForm $record, array $data): void {
                        if ($data['status'] == 'rejected') {
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFitOutFormsDocuments::route('/'),
            'view' => Pages\ViewFitOutFormsDocument::route('/{record}'),

        ];
    }
}
