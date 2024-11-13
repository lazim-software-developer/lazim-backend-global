<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacilityBookingResource\Pages;
use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;
use App\Models\Master\Role;
use DB;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FacilityBookingResource extends Resource
{
    protected static ?string $model = FacilityBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Work Permit Request';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('building_id')
                    ->rules(['exists:buildings,id'])
                    ->relationship('building', 'name')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager') {

                            return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                ->pluck('name', 'id');
                        }
                    })
                    ->reactive()
                    ->disabledOn('edit')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->placeholder('Building'),

                Select::make('flat_id')
                    ->label('Flat Number')
                    ->disabledOn('edit')
                    ->options(function () {
                        // return \App\Models\Building\Flat::all()->pluck('flat_number', 'id');
                    }),
                Select::make('bookable_id')
                    ->required()
                    ->disabledOn('edit')
                    ->options(
                        DB::table('facilities')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->label('Permit Work'),
                Select::make('user_id')
                    ->relationship('user', 'first_name')
                    ->disabledOn('edit')
                    ->preload(),

                Hidden::make('bookable_type')
                    ->default('App\Models\WorkPermit'),

                Grid::make(3)
                    ->schema([
                        DatePicker::make('date')
                            ->required()
                            ->disabledOn('edit'),
                        TimePicker::make('start_time')
                            ->required()
                            ->disabledOn('edit'),
                        TimePicker::make('end_time')
                            ->required()
                            ->disabledOn('edit'),
                    ]),
                Textarea::make('description')
                    ->disabledOn('edit'),

                Toggle::make('approved')
                    ->inline(false)
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle')
                    ->onColor('success')
                    ->offColor('danger'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->emptyStateHeading('No Work Permit Requests')
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('bookable.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50)
                    ->label('Task'),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->default('NA')
                    ->searchable()
                    ->date(),
                Tables\Columns\TextColumn::make('start_time')
                    ->default('NA')
                    ->time(),
                Tables\Columns\TextColumn::make('end_time')
                    ->default('NA')
                    ->time(),
                Tables\Columns\IconColumn::make('approved')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('building_id')
                    ->label('Building')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager') {
                            $query->where('owner_association_id', auth()->user()->owner_association_id);
                        }
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                EditAction::make(),
                Action::make('Update Status')
                    ->visible(fn($record) => $record->approved === 0)
                    ->button()
                    ->form([
                        Toggle::make('approved')
                            ->rules(['boolean'])
                            ->required()
                            ->live(),
                    ])
                    ->fillForm(fn(FacilityBooking $record): array=> [
                        'approved' => $record->approved,
                    ])
                    ->action(function (FacilityBooking $record, array $data): void {
                        $record->approved = $data['approved'];
                        $record->save();
                    })
                    ->slideOver(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index'  => Pages\ListFacilityBookings::route('/'),
            'create' => Pages\CreateFacilityBooking::route('/create'),
            'edit'   => Pages\EditFacilityBooking::route('/{record}/edit'),
        ];
    }
}
