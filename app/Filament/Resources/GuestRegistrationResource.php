<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestRegistrationResource\Pages;
use App\Filament\Resources\GuestRegistrationResource\RelationManagers;
use App\Models\Forms\Guest;
use App\Models\GuestRegistration;
use App\Models\Visitor\FlatVisitor;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
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
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Illuminate\Database\Query\JoinClause;
use function Laravel\Prompts\select;

class GuestRegistrationResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static ?string $modeLabel = "Guest Registration";
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
                    TextInput::make('passport_number'),
                    DatePicker::make('visa_validity_date'),
                    DatePicker::make('expiry_date'),
                    TextInput::make('stay_duration'),
                    FileUpload::make('dtmc_license_url')
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable()
                        ->openable()
                        ->label('Dtmc License')
                        ->required()
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ]),
                    // ViewField::make('Building')
                    //     ->view('forms.components.fieldbuilding'),
                    select::make('flat_visitor_id')
                        ->relationship('flatVisitor', 'name')
                        ->createOptionForm([
                            Select::make('building_id')
                                ->relationship('building', 'name')
                                ->preload()
                                ->searchable()
                                ->label('Building Name'),
                            Select::make('flat_id')
                                ->relationship('flat', 'property_number')
                                ->preload()
                                ->searchable()
                                ->label('Property No'),
                            TextInput::make('name'),
                            TextInput::make('phone'),
                            Hidden::make('type')
                                ->default('Guest'),
                            Hidden::make('initiated_by')
                                ->default(auth()->user()->id),
                            TextInput::make('email'),
                            DatePicker::make('start_time')
                                ->label('From Date'),
                            DatePicker::make('end_time')
                                ->label('To Date'),
                            TextInput::make('number_of_visitors'),
                        ])
                        ->editOptionForm([
                            Select::make('building_id')
                                ->relationship('building', 'name')
                                ->preload()
                                ->searchable()
                                ->label('Building Name'),
                            Select::make('flat_id')
                                ->relationship('flat', 'property_number')
                                ->preload()
                                ->searchable()
                                ->label('Property No'),
                            TextInput::make('name'),
                            TextInput::make('phone')
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
                            TextInput::make('email'),
                            DatePicker::make('start_time')
                                ->rules(['date'])
                                ->required()
                                ->placeholder('From Date')
                                ->label('From Date'),
                            DatePicker::make('end_time')
                                ->label('To Date')
                                ->rules(['date'])
                                ->required()
                                ->placeholder('To Date'),
                            TextInput::make('number_of_visitors'),
                        ])
                        ->label('Flat Visitor')
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ]),
                    Toggle::make('access_card_holder'),
                    Toggle::make('original_passport'),
                    Toggle::make('guest_registration'),
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

                ImageColumn::make('dtmc_license_url')
                    ->disk('s3')
                    ->square()
                    ->alignCenter()
                    ->label('DTMC License URL'),

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
                Action::make('Update Status')
                    ->visible(fn ($record) => $record->status === null)
                    ->button()
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
                            ->visible(function (callable $get) {
                                if ($get('status') == 'rejected') {
                                    return true;
                                }
                                return false;
                            }),
                    ])
                    ->fillForm(fn (Guest $record): array => [
                        'status' => $record->status,
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (Guest $record, array $data): void {
                        if ($data['status'] == 'rejected') {
                            $record->status = $data['status'];
                            $record->remarks = $data['remarks'];
                            $record->save();
                        } else {
                            $record->status = $data['status'];
                            $record->save();
                        }
                    })
                    ->slideOver()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            //'create' => Pages\CreateGuestRegistration::route('/create'),
            //'edit' => Pages\EditGuestRegistration::route('/{record}/edit'),
            'view' => Pages\ViewGuestRegistration::route('/{record}'),
        ];
    }
}
