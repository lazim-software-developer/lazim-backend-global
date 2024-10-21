<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\UserApproval;
use App\Models\Building\Flat;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserApprovalResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\UserApprovalResource\RelationManagers;
use App\Filament\Resources\UserApprovalResource\RelationManagers\HistoryRelationManager;
use Filament\Forms\Components\Section;

class UserApprovalResource extends Resource
{
    protected static ?string $model = UserApproval::class;
    protected static ?string $modelLabel = 'Resident Approval';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
    ->schema([
        Section::make('User Information')
            ->schema([
                TextInput::make('user')->disabledOn('edit'),
                TextInput::make('email')->disabledOn('edit'),
                TextInput::make('phone')->disabledOn('edit'),
                DateTimePicker::make('created_at')
                    ->label('Date of Creation')
                    ->disabled(),
            ])
            ->columns(2),
        Section::make('Flat & Building Details')
            ->schema([
                Select::make('flat_id')->label('Flat')
                    ->relationship('flat', 'property_number')
                    ->disabled()
                    ->live(),
                TextInput::make('building')
                    ->formatStateUsing(function($record){
                        return Flat::where('id', $record->flat_id)->first()?->building->name;
                    })
                    ->disabled(),
            ])
            ->columns(2),
        Section::make('Documents')
            ->schema([
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
                    ->disabled(),
                FileUpload::make('emirates_document')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    ->disabled(),
                FileUpload::make('passport')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    ->disabled(),
            ])
            ->columns(2),
        Section::make('Approval Details')
            ->schema([
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
                Textarea::make('remarks')
                    ->maxLength(250)
                    ->rows(5)
                    ->required()
                    ->visible(function (Get $get) {
                        if ($get('status') == 'rejected') {
                            return true;
                        }
                        return false;
                    }),
            ]),
    ]);

    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->default('NA')->formatStateUsing(fn ($state) => ucwords($state)),
                Tables\Columns\TextColumn::make('flat.property_number')->label('Flat Number')->default('NA'),
                Tables\Columns\TextColumn::make('flat.building.name')->label('Building')->default('NA'),
                Tables\Columns\TextColumn::make('created_at')->label('Date of Creation')->default('NA')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            HistoryRelationManager::class,
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
