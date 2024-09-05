<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LegalNoticeResource\Pages;
use App\Filament\Resources\LegalNoticeResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\LegalNotice;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class LegalNoticeResource extends Resource
{
    protected static ?string $model = LegalNotice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('legalNoticeId')
                    ->label('Legal Notice ID'),

                Select::make('building_id')
                    ->relationship('building', 'name')
                    ->label('Building Name'),

                Select::make('flat_id')
                    ->relationship('flat', 'property_number')
                    ->label('Flat Number'),

                TextInput::make('mollakPropertyId')
                    ->label('Mollak Property ID'),

                DatePicker::make('registrationDate')
                    ->label('Registration Date'),

                TextInput::make('registrationNumber')
                    ->label('Registration Number'),

                TextInput::make('invoiceNumber')
                    ->label('Invoice Number'),

                TextInput::make('invoicePeriod')
                    ->label('Invoice Period'),

                TextInput::make('previousBalance')
                    ->label('Previous Balance'),

                TextInput::make('invoiceAmount')
                    ->label('Invoice Amount'),

                TextInput::make('approvedLegalAmount')
                    ->label('Approved Legal Amount'),

                TextInput::make('legalNoticePDF')
                    ->label('Legal Notice PDF'),


                DatePicker::make('due_date')
                    ->label('Due Date'),

                TextInput::make('case_status')
                    ->label('Case Status'),

                TextInput::make('case_number')
                    ->label('Case Number'),

                TextInput::make('case_type')
                    ->label('Case Type'),

                Toggle::make('isRDCCaseStart')
                    ->label('RDC Case Started'),

                Toggle::make('isRDCCaseEnd')
                    ->label('RDC Case Ended'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('legalNoticeId')
                    ->label('Legal Notice ID')
                    ->searchable(),

                TextColumn::make('mollakPropertyId')
                    ->label('Mollak Property ID'),

                TextColumn::make('registrationNumber')
                    ->label('Registration Number'),

                TextColumn::make('invoiceNumber')
                    ->label('Invoice Number'),

                TextColumn::make('invoicePeriod')
                    ->label('Invoice Period'),

                TextColumn::make('building.name'),

            ])
            ->filters([
                Filter::make('building')
                    ->form([
                        Select::make('Building')
                            ->searchable()
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                } else {
                                    return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                        ->pluck('name', 'id');
                                }
                            })
                            ->placeholder('Select Building'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['Building']),
                            function ($query) use ($data) {
                                $query->where('building_id', $data['Building']);
                                
                            }
                        );
                    }),

                // Filter::make('flat')
                //     ->form([
                //         Select::make('flat')
                //             ->searchable()
                //             ->options(function () {
                //                 if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                //                     return Flat::all()->pluck('property_number', 'id');
                //                 } else {
                //                     return Flat::where('owner_association_id', auth()->user()?->owner_association_id)
                //                         ->pluck('property_number', 'id');
                //                 }
                //             })
                //             ->placeholder('Select Flat'),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query->when(
                //             isset($data['flat']),
                //             function ($query) use ($data) {
                //                 $query->where('flat_id', $data['flat']);
                                
                //             }
                //         );
                //     }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->searchPlaceholder('Search Legal Notice ID');;
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
            'index' => Pages\ListLegalNotices::route('/'),
            'create' => Pages\CreateLegalNotice::route('/create'),
            'view' => Pages\ViewLegalNotice::route('/{record}'),
            // 'edit' => Pages\EditLegalNotice::route('/{record}/edit'),
        ];
    }
}
