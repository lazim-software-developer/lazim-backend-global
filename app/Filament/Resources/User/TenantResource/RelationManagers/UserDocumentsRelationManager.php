<?php

namespace App\Filament\Resources\User\TenantResource\RelationManagers;

use App\Models\Building\Document;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'userDocuments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label('Document Name')
                    ->disabled()
                    ->maxLength(255)
                    ->columnSpan([
                        'sl'=>1,
                        'md'=>1,
                        'lg'=>2,
                    ]),

                FileUpload::make('url')
                        ->disk('s3')
                        ->directory('dev')
                        ->label('Document')
                        ->disabled()
                        ->required()
                        ->columnSpan([
                            'sl'=>1,
                            'md'=>1,
                            'lg'=>2,
                        ]),

                Select::make('status')
                        ->options([
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->searchable()
                        ->required()
                        ->live()
                        ->placeholder('Status'),

                TextInput::make('remarks')
                        ->rules(['max:255'])
                        ->visible(function (callable $get) {
                            if ($get('status') == 'rejected') {
                                return true;
                            }
                                return false;
                        })
                        ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Document Name')
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('documentUsers.first_name')
                    ->searchable()
                    ->label('Tenant Name')
                    ->default('NA')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('Update Status')
                ->visible(fn ($record) => $record->status === 'submitted')
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
                        })
                        ->required(),
                ])
                ->fillForm(fn (Document $record): array => [
                    'status' => $record->status,
                    'remarks' => $record->remarks,
                ])
                ->action(function (Document $record, array $data): void {
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
}
