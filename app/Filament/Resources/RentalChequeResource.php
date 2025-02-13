<?php
namespace App\Filament\Resources;

use App\Filament\Resources\RentalChequeResource\Pages;
use App\Models\OwnerAssociation;
use App\Models\RentalCheque;
use DB;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RentalChequeResource extends Resource
{
    protected static ?string $model = RentalCheque::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Cheque Details')
                    ->description('Edit the Cheque Details.')
                    ->icon('heroicon-o-document-currency-dollar')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('cheque_number')
                                    ->numeric()
                                    ->minLength(6)
                                    ->required()
                                    ->disabledOn('edit')
                                    ->maxLength(12)
                                    ->placeholder('Enter cheque number'),
                                TextInput::make('amount')
                                    ->maxValue(999999999.99)
                                    ->numeric()
                                    ->disabledOn('edit')
                                    ->required()
                                    ->placeholder('Enter amount'),
                                DatePicker::make('due_date')
                                    ->rules(['date'])
                                    ->required()
                                    ->disabledOn('edit')
                                    ->placeholder('Select due date'),
                                Select::make('status')
                                    ->default('Upcoming')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        'Overdue'  => 'Overdue',
                                        'Paid'     => 'Paid',
                                        'Upcoming' => 'Upcoming',
                                    ])
                                    ->placeholder('Select cheque status'),
                                Select::make('mode_payment')
                                    ->label('Payment Mode')
                                    ->default('Cheque')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        'Online' => 'Online',
                                        'Cheque' => 'Cheque',
                                        'Cash'   => 'Cash',
                                    ])
                                    ->placeholder('Select payment mode'),
                                Select::make('cheque_status')
                                    ->native(false)
                                    ->options([
                                        'Cancelled' => 'Cancelled',
                                        'Bounced'   => 'Bounced',
                                        'Paid'      => 'Paid',
                                    ])
                                    ->placeholder('Select cheque status'),
                                TextInput::make('payment_link')
                                    ->url()
                                    ->nullable()
                                    ->maxLength(200)
                                    ->placeholder('Enter payment link'),
                                // Textarea::make('comments')
                                //     ->nullable()
                                //     ->maxLength(200)
                                //     ->placeholder('Enter comments'),
                            ]),
                    ]),
                Section::make('Comments')
                    ->description('View and add comments.')
                    ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                    ->collapsible()
                    ->schema([
                        TextInput::make('new_comment')
                            ->label('New Comment')
                            ->placeholder('Enter new comment'),
                        Textarea::make('old_comments')
                            ->label('Old Comments')
                            ->rows(5)
                            ->disabled()
                            ->default(fn($record) => $record ? implode("\n", array_map(fn($comment, $index) => ($index + 1) . '. ' . $comment, json_decode($record->comments, true) ?? [], array_keys(json_decode($record->comments, true) ?? []))) : '')
                            ->placeholder('No comments available'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $ownerAssociationId = auth()->user()?->owner_association_id;

                if (! $ownerAssociationId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas('rentalDetail.flat', function ($query) use ($ownerAssociationId) {
                    $pmFlats = DB::table('property_manager_flats')
                        ->where('owner_association_id', $ownerAssociationId)
                        ->where('active', true)
                        ->pluck('flat_id')
                        ->toArray();
                    $query
                        ->whereIn('flat_id', $pmFlats);
                })->orderBy('created_at', 'desc');
            })
            ->columns([
                TextColumn::make('rentalDetail.flat.building.name')
                    ->label('Building')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rentalDetail.flat.property_number')
                    ->label('Flat number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cheque_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date(),

                Tables\Columns\TextColumn::make('mode_payment'),
                Tables\Columns\TextColumn::make('cheque_status')
                    ->badge()
                    ->default('NA')
                    ->color(fn(string $state): string => match ($state) {
                        'Paid'                            => 'success',
                        'Bounced'                         => 'primary',
                        'Cancelled'                       => 'danger',
                        default                           => 'gray',
                    }),

            ])
            ->filters([

                SelectFilter::make('flat_property_number')
                    ->label('Flat Number')
                    ->preload()
                    ->options(function () {
                        $pmBuildings = DB::table('building_owner_association')
                            ->where('owner_association_id', auth()->user()?->owner_association_id)
                            ->where('active', true)
                            ->pluck('building_id')
                            ->toArray();
                        $pmFlats = DB::table('property_manager_flats')
                            ->where('owner_association_id', auth()->user()?->owner_association_id)
                            ->where('active', true)
                            ->pluck('flat_id')
                            ->toArray();

                        if (auth()->user()->role->name == 'Property Manager'
                        || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                                ->pluck('role')[0] == 'Property Manager') {
                            return DB::table('flats')
                                ->whereIn('id', $pmFlats)
                                ->pluck('property_number', 'id');
                        }

                        return DB::table('flats')
                            ->whereIn('building_id', $pmBuildings)
                            ->pluck('property_number', 'id');
                    })
                    ->searchable(),
                SelectFilter::make('flat_tenant_name')
                    ->label('Flat Tenant Name')
                    ->relationship(
                        'rentalDetail.flat.tenants.user',
                        'first_name',
                        fn($query) => $query->whereHas('tenants.flat.building.ownerAssociations', function ($query) {
                            $pmFlats = DB::table('property_manager_flats')
                                ->where('owner_association_id', auth()->user()?->owner_association_id)
                                ->where('active', true)
                                ->pluck('flat_id')
                                ->toArray();

                            $query->where('owner_association_id', auth()->user()->owner_association_id)
                                ->whereIn('flat_id', $pmFlats)
                                ->where('building_owner_association.active', true);
                        })
                    )
                    ->preload()
                    ->searchable(),
                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        1  => 'January',
                        2  => 'February',
                        3  => 'March',
                        4  => 'April',
                        5  => 'May',
                        6  => 'June',
                        7  => 'July',
                        8  => 'August',
                        9  => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ])
                    ->default(null)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $month): Builder => $query->whereMonth('due_date', $month)
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index'  => Pages\ListRentalCheques::route('/'),
            'create' => Pages\CreateRentalCheque::route('/create'),
            'edit'   => Pages\EditRentalCheque::route('/{record}/edit'),
        ];
    }
}
