<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestRegistrationResource\Pages;
use App\Models\Forms\Guest;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;


class GuestRegistrationResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static ?string $modeLabel = "Guest registration";
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    ViewField::make('Guest Details')->view('forms.components.form.guest-registration-name'),
                    DatePicker::make('visa_validity_date')->disabled()
                    ->visible(function (callable $get) {
                        if ($get('visa_validity_date') != null) {
                            return true;
                        }
                        return false;
                    })
                    ->label('Tourist/Visitor visa validity date'),
                    TextInput::make('stay_duration')->disabled()->label('duration of stay'),
                    FileUpload::make('dtmc_license_url')
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable(true)
                        ->openable(true)
                        ->disabled()
                        ->label('DTMC License File')
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ]),
                    // ViewField::make('Building')
                    //     ->view('forms.components.fieldbuilding'),
                    select::make('flat_visitor_id')
                        ->relationship('flatVisitor', 'name')
                        // ->createOptionForm([
                        //     Select::make('building_id')
                        //         ->relationship('building', 'name')
                        //         ->preload()
                        //         ->searchable()
                        //         ->label('Building Name'),
                        //     Select::make('flat_id')
                        //         ->relationship('flat', 'property_number')
                        //         ->preload()
                        //         ->searchable()
                        //         ->label('Property No'),
                        //     TextInput::make('name'),
                        //     TextInput::make('phone'),
                        //     Hidden::make('type')
                        //         ->default('Guest'),
                        //     Hidden::make('initiated_by')
                        //         ->default(auth()->user()->id),
                        //     TextInput::make('email'),
                        //     DatePicker::make('start_time')
                        //         ->label('From Date'),
                        //     DatePicker::make('end_time')
                        //         ->label('To Date'),
                        //     TextInput::make('number_of_visitors'),
                        // ])
                        ->editOptionForm([
                            Select::make('building_id')
                                ->relationship('building', 'name')
                                ->preload()
                                ->disabled()
                                ->searchable()
                                ->label('Building Name'),
                            Select::make('flat_id')
                                ->relationship('flat', 'property_number')
                                ->preload()
                                ->searchable()
                                ->disabled()
                                ->label('Unit Number'),
                            TextInput::make('name')
                                ->disabled(),
                            TextInput::make('phone')
                                ->disabled()
                                ->unique(
                                    'flat_visitors',
                                    'phone',
                                    fn (?Model $record) => $record
                                ),
                            Hidden::make('type')
                                ->default('Guest'),
                            Hidden::make('initiated_by')
                                ->default(auth()->user()->id),
                            Hidden::make('approved_by')
                                ->default(auth()->user()->id),
                            TextInput::make('email')
                                ->disabled(),
                            DatePicker::make('start_time')
                                ->rules(['date'])
                                ->disabled()
                                ->placeholder('From Date')
                                ->label('Guest Arrival Date'),
                            DatePicker::make('end_time')
                                ->label('Guest Departure Date')
                                ->rules(['date'])
                                ->disabled()
                                ->placeholder('Guest Departure Date'),
                            TextInput::make('number_of_visitors')
                                ->disabled(),
                        ])
                        ->disabled()
                        ->label('Flat Visitor'),
                    Toggle::make('access_card_holder')->disabled(),
                    Toggle::make('original_passport')->disabled(),
                    Toggle::make('guest_registration')->disabled(),
                    Select::make('status')
                        ->options([
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->disabled(function (Guest $record) {
                            return $record->status != null;
                        })
                        ->required()
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
                        ->disabled(function (Guest $record) {
                            return $record->status != null;
                        })
                        ->required(),
                    // If the form is rejected, we need to capture which fields are rejected
                    CheckboxList::make('rejected_fields')
                        ->label('Please select rejected fields')
                        ->options([
                            'passport_number' => 'Passport Number',
                            'visa_validity_date' => 'Tourist/Visitor visa validity date',
                            'stay_duration' => 'duration of stay',
                            'start_date' => 'Guest arrival date',
                            'number_of_visitors' => 'Number of visitors',
                            'end_date' => 'Guest departure date',
                            'email' => 'Guest Email',
                        ])
                        ->columns(4)
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ])
                        ->visible(function (callable $get) {
                            if ($get('status') == 'rejected') {
                                return true;
                            }
                            return false;
                        })
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('Name')->view('tables.columns.name'),
                TextColumn::make('stay_duration')
                    ->searchable()
                    ->alignCenter()
                    ->default('NA')
                    ->label('Stay duration(days)'),
                ViewColumn::make('Flat')->view('tables.columns.flat'),
                ViewColumn::make('Building')->view('tables.columns.building'),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // SelectFilter::make('flat_visitor_id')
                // ->relationship('flatVisitor','name')
                // ->options(function () {
                //     return FlatVisitor::join('buildings', function (JoinClause $join) {
                //             $join->on('flat_visitors.building_id', '=', 'buildings.id');
                //         })
                //         ->select('flat_visitors.id', 'buildings.name')
                //         ->pluck('name', 'id')
                //         ->toArray();
                // })
                // ->label('Building'),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
        // ->emptyStateActions([
        //     Tables\Actions\CreateAction::make(),
        // ]);
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
            'index' => Pages\ListGuestRegistrations::route('/'),
            // 'view' => Pages\ViewGuestRegistrations::route('/{record}'),
            'edit' => Pages\EditGuestRegistration::route('/{record}/edit'),
        ];
    }
}
