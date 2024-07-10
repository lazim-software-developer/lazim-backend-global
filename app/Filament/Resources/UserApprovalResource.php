<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\UserApproval;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserApprovalResource\Pages;
use App\Filament\Resources\UserApprovalResource\RelationManagers;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class UserApprovalResource extends Resource
{
    protected static ?string $model = UserApproval::class;
    protected static ?string $modelLabel = 'Resident Approval';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Select::make('user_id')
                //     ->relationship('user', 'first_name')
                //     ->required()
                //     ->disabled(),
                // Select::make('user_id')->label('email')
                // ->relationship('user', 'email')
                //     ->required()
                //     ->disabled(),
                // Select::make('user_id')->label('phone')
                // ->relationship('user', 'phone')
                //     ->required()
                //     ->disabled(),
                TextInput::make('user')->disabledOn('edit'),
                TextInput::make('email')->disabledOn('edit'),
                TextInput::make('phone')->disabledOn('edit'),
                FileUpload::make('document')
                    ->label(function (Get $get) {
                        if($get('document_type') == 'Ejari'){
                            return 'Tenancy Contract / Ejari';
                        }
                            return $get('document_type');
                    })
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    ->disabled()
                    ->columnSpanFull(),
                FileUpload::make('emirates_document')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    ->disabled()
                    ->columnSpanFull(),
                FileUpload::make('passport')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    ->disabled()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'approved' => 'Approve',
                        'rejected' => 'Reject',
                    ])
                    ->disabled(function (UserApproval $record) {
                        return $record->status != null;
                    })
                    ->searchable()
                    ->live()
                    ->required(),
                TextInput::make('remarks')
                    ->maxLength(50)
                    ->required()
                    ->visible(function (Get $get) {
                        if ($get('status') == 'rejected') {
                            return true;
                        }
                        return false;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('user', function (Builder $query) {
                $query->where('owner_association_id', auth()->user()->owner_association_id);
            }))
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->default('NA'),
                Tables\Columns\TextColumn::make('flat.property_number')->label('Flat Number'),
                Tables\Columns\TextColumn::make('flat.building.name')->label('Building')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListUserApprovals::route('/'),
            'create' => Pages\CreateUserApproval::route('/create'),
            'view' => Pages\ViewUserApproval::route('/{record}'),
            'edit' => Pages\EditUserApproval::route('/{record}/edit'),
        ];
    }
}
