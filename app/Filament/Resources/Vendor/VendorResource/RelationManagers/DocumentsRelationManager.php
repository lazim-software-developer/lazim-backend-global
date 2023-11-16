<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use App\Models\Building\Document;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,])->schema([
                    Select::make('document_library_id')
                        ->rules(['exists:document_libraries,id'])
                        ->relationship('documentLibrary', 'name')
                        ->searchable()
                        ->placeholder('Document Library'),

                    FileUpload::make('url')
                        ->disk('s3')
                        ->directory('dev')
                        ->openable(true)
                        ->downloadable(true)
                        ->label('Document'),

                    Select::make('status')
                        ->options([
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->placeholder('Status'),

                    TextInput::make('remarks')
                        ->rules(['max:255'])
                        ->placeholder('Remarks'),

                    TextInput::make('documentable_id')
                        ->rules(['max:255'])
                        ->placeholder('Documentable Id'),

                    TextInput::make('documentable_type')
                        ->rules(['max:255', 'string'])
                        ->placeholder('Documentable Type'),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('documentLibrary.name')->limit(50),
                Tables\Columns\ImageColumn::make('url')->square()->disk('s3')->label('Document'),
                Tables\Columns\TextColumn::make('status')->limit(50)->label('Status'),
                Tables\Columns\TextColumn::make('remarks')->label('Remarks'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                //Tables\Actions\DeleteAction::make(),
                Action::make('Update Status')
                    ->visible(fn ($record) => $record->status == 'pending')
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }
}
