<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubContractorResource\Pages;
use App\Models\SubContractor;
use App\Models\Vendor\Vendor;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SubContractorResource extends Resource
{
    protected static ?string $model = SubContractor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('company_name')
                    ->maxLength(191),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(191),
                Select::make('vendor_id')
                    ->relationship('vendor', 'name'),
                Forms\Components\TextInput::make('trn_no')
                    ->required()
                    ->label('TRN number')
                    ->maxLength(191),
                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->label('Contract Start Date'),
                Forms\Components\DatePicker::make('end_date')
                    ->required()
                    ->label('Contract End Date'),
                FileUpload::make('trade_licence')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled(fn ($context) => $context === 'view')
                    ->downloadable(true)
                    ->openable(true)
                    ->required(fn ($context) => $context !== 'view'),
                FileUpload::make('contract_paper')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled(fn ($context) => $context === 'view')
                    ->downloadable(true)
                    ->openable(true)
                    ->required(fn ($context) => $context !== 'view'),
                FileUpload::make('agreement_letter')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled(fn ($context) => $context === 'view')
                    ->downloadable(true)
                    ->openable(true)
                    ->required(fn ($context) => $context !== 'view'),
                FileUpload::make('additional_doc')
                    ->disk('s3')
                    ->label('Additional Documents')
                    ->directory('dev')
                    ->disabled(fn ($context) => $context === 'view')
                    ->downloadable(true)
                    ->openable(true),

                Forms\Components\Toggle::make('active')
                    ->inline(false)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $vendorId = DB::table('owner_association_vendor')->where('owner_association_id', auth()->user()?->owner_association_id)
                    ->pluck('vendor_id');
                $query->where('vendor_id', $vendorId);
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('trn_no')
                    ->label('TRN number')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('start_date')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('end_date')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('trade_licence')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('contract_paper')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('agreement_letter')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('additional_doc')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('vendor.name'),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index'  => Pages\ListSubContractors::route('/'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'create' => Pages\CreateSubContractor::route('/create'),
            // 'edit'   => Pages\EditSubContractor::route('/{record}/edit'),
        ];
    }
}
