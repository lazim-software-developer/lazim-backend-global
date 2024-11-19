<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalChequeResource\Pages;
use App\Models\RentalCheque;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                                    ->minLength(0)
                                    ->required()
                                    ->maxLength(6)
                                    ->placeholder('Enter cheque number'),
                                TextInput::make('amount')
                                    ->maxLength(20)
                                    ->numeric()
                                    ->minLength(0)
                                    ->required()
                                    ->placeholder('Enter amount'),
                                DatePicker::make('due_date')
                                    ->rules(['date'])
                                    ->required()
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

                if (!$ownerAssociationId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas('rentalDetail.flat', function ($query) use ($ownerAssociationId) {
                    $query->where('owner_association_id', $ownerAssociationId);
                });
            })
            ->columns([
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
                    ->relationship(
                        'rentalDetail.flat',
                        'property_number',
                        fn($query) => $query->whereHas('building', function ($query) {
                            $query->where('owner_association_id', auth()->user()->owner_association_id);
                        })
                    )
                    ->preload()
                    ->searchable(),
                SelectFilter::make('flat_tenant_name')
                    ->label('Flat Tenant Name')
                    ->relationship(
                        'rentalDetail.flat.tenants.user',
                        'first_name',
                        fn($query) => $query->whereHas('tenants.flat.building', function ($query) {
                            $query->where('owner_association_id', auth()->user()->owner_association_id);
                        })
                    )
                    ->preload()
                    ->searchable(),

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
