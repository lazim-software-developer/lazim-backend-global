<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoveOutFormsDocumentResource\Pages;
use App\Filament\Resources\MoveOutFormsDocumentResource\RelationManagers;
use App\Models\Forms\MoveInOut;
use App\Models\MoveOutFormsDocument;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MoveOutFormsDocumentResource extends Resource
{
    protected static ?string $model = MoveInOut::class;
    protected static ?string $modelLabel = 'MoveOut';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Forms Document';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->poll('60s')
        ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'move-out')->withoutGlobalScopes())
        ->columns([
            TextColumn::make('name')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('email')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('phone')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('type')
                ->searchable()
                ->default('NA')
                ->limit(50),
            TextColumn::make('moving_date')
                ->limit(50),
            TextColumn::make('moving_time')
                ->limit(50),
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
            ImageColumn::make('handover_acceptance')
                ->disk('s3')
                ->circular(),
            ImageColumn::make('receipt_charges')
                ->circular()
                ->disk('s3'),
            ImageColumn::make('contract')
                ->circular()
                ->disk('s3'),
            ImageColumn::make('title_deed')
                ->circular()
                ->disk('s3'),
            ImageColumn::make('passport')
                ->circular()
                ->disk('s3'),
            ImageColumn::make('dewa')
                ->circular()
                ->disk('s3'),
            ImageColumn::make('cooling_registration')
                ->circular()
                ->disk('s3'),
            ImageColumn::make('gas_registration')
                ->circular()
                ->disk('s3'),
            ImageColumn::make('vehicle_registration')
                ->circular()
                ->disk('s3'),
            ImageColumn::make('movers_license')
                ->circular()
                ->disk('s3'),
            ImageColumn::make('movers_liability')
                ->circular()
                ->disk('s3'),
            
        ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Action::make('Update Status')
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
                            ->visible(function(callable $get){
                                if($get('status')=='rejected')
                                {
                                    return true;
                                }
                                return false;
                            }),
                    ])
                    ->fillForm(fn (MoveInOut $record): array => [
                        'status' => $record->status,
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (MoveInOut $record,array $data): void {
                        if($data['status'] == 'rejected')
                        {
                            $record->status = $data['status'];
                            $record->remarks = $data['remarks'];
                            $record->save();
                        }
                        else
                        {
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
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMoveOutFormsDocuments::route('/'),
            //'create' => Pages\CreateMoveOutFormsDocument::route('/create'),
            //'edit' => Pages\EditMoveOutFormsDocument::route('/{record}/edit'),
        ];
    }    
}
