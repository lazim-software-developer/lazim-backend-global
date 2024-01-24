<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Forms\Guest;
use App\Models\Master\Role;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use App\Models\Visitor\FlatVisitor;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\GuestRegistrationResource\Pages;


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
                    TextInput::make('stay_duration')->disabled()->label('Duration of stay'),
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
                        ->options(function (callable $get) {
                            $name = FlatVisitor::find($get('flat_visitor_id'));
                            return [
                                $get('flat_visitor_id') => $name->name,
                            ];
                        })->helperText('Click on the icon to view more details.')
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
                        ])->searchable()
                        ->selectablePlaceholder(false)
                        ->label('Flat Visitor'),
                    Toggle::make('access_card_holder')->disabled(),
                    Toggle::make('original_passport')->disabled(),
                    Toggle::make('guest_registration')->disabled(),
                    Select::make('status')
                        ->options([
                            'approved' => 'Approve',
                            'rejected' => 'Reject',
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
                    ViewField::make('Guest Passports')->label('Guest Passports')
                        ->view('forms.components.fieldbuilding'),
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
                Filter::make('building')
                    ->form([
                        Select::make('Building')
                            ->searchable()
                            ->options(function () {
                                if(Role::where('id', auth()->user()->role_id)->first()->name != 'Admin'){
                                    return Building::where('owner_association_id',auth()->user()->owner_association_id)->pluck('name', 'id');
                                }
                                return Building::all()->pluck('name', 'id');
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['Building']),
                            function ($query) use ($data) {
                                $query->whereHas('flatVisitor', function ($query) use ($data) {
                                    $query->whereHas('building', function ($query) use ($data) {
                                        $query->where('id', $data['Building']);
                                    });
                                });
                            }
                        );
                    })
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
