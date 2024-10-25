<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FitOutFormsDocumentResource\Pages;
use App\Filament\Resources\FitOutFormsDocumentResource\RelationManagers\ContractorRequestRelationManager;
use App\Models\Building\Building;
use App\Models\Forms\FitOutForm;
use App\Models\Master\Role;
use App\Models\Order;
use Closure;
use DB;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class FitOutFormsDocumentResource extends Resource
{
    protected static ?string $model = FitOutForm::class;

    protected static ?string $modelLabel       = 'Fitout';
    protected static ?string $pluralModelLabel = 'Fitout';

    protected static ?string $navigationGroup = 'Forms Document';
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('contractor_name')
                        ->disabled()
                        ->label('Contractor name'),
                    TextInput::make('email')
                        ->label('Contractor email')
                        ->disabled()
                        ->placeholder('Email'),
                    TextInput::make('phone')
                        ->label('Contractor phone number')
                        ->disabled()
                        ->placeholder('Phone Number'),
                    Select::make('building_id')
                        ->relationship('building', 'name')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Building'),
                    Select::make('flat_id')
                        ->relationship('flat', 'property_number')
                        ->preload()
                        ->disabled()
                        ->searchable()
                        ->label('Unit number'),
                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->disabled()
                        ->preload()
                        ->searchable()
                        ->label('User'),
                    Toggle::make('no_objection')->disabled(),
                    Toggle::make('undertaking_of_waterproofing')->disabled(),
                    Select::make('status')
                        ->options([
                            'approved' => 'Approve',
                            'rejected' => 'Reject',
                        ])
                        ->disabled(function (FitOutForm $record) {

                            return $record->status != null;
                        })->visible(function (FitOutForm $record) {

                        return $record->contractorRequest?->exists();
                    })
                        ->required()
                        ->searchable()
                        ->live(),
                    TextInput::make('id')
                        ->formatStateUsing(function (?Model $record) {
                            $orderpayment_status = Order::where(['orderable_id' => $record->id, 'orderable_type' => FitOutForm::class])->first()?->payment_status;
                            if ($orderpayment_status) {
                                return $orderpayment_status == 'requires_payment_method' ? 'Payment Failed' : $orderpayment_status;
                            }
                            return 'NA';
                        })
                        ->label('Payment status')
                        ->readOnly(),
                    TextInput::make('remarks')
                        ->rules(['max:150'])
                        ->visible(function (callable $get) {
                            if ($get('status') == 'rejected') {
                                return true;
                            }
                            return false;
                        })
                        ->disabled(function (FitOutForm $record) {
                            return $record->status != null;
                        })
                        ->required(),
                    FileUpload::make('admin_document')
                        ->disk('s3')
                        ->directory('dev')->required()
                        ->rules(['file', 'mimes:pdf', function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if ($value->getSize() / 1024 > 2048) {
                                    $fail('The document must not be greater than 2MB.');
                                }
                            };
                        }])
                        ->openable(true)
                        ->downloadable(true)
                        ->disabled(function (FitOutForm $record) {

                            return $record->admin_document;
                        })
                        ->visible(function (callable $get, $record) {
                            if ($record->orders->first()?->payment_status == 'succeeded' && $record->status == 'approved') {
                                return true;
                            }
                            return false;
                        })->helperText('Once a document is uploaded, it cannot be modified.')
                        ->label('Document'),
                    // If the form is rejected, we need to capture which fields are rejected
                    CheckboxList::make('rejected_fields')
                        ->label('Please select rejected fields')
                        ->options([
                            'contractor_name' => 'Contractor Name',
                            'email'           => 'Contractor email',
                            'phone'           => 'Contractor phone',
                        ])->columns(4)
                        ->visible(function (callable $get) {
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
            ->poll('60s')
            ->columns([

                TextColumn::make('ticket_number')
                    ->searchable()
                    ->default('--')
                    ->label('Ticket number'),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('--')
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('--')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('--')
                    ->label('Unit number')
                    ->limit(50),
                TextColumn::make('status')
                    ->searchable()
                    ->default('--')
                    ->limit(50),
                TextColumn::make('orders')
                    ->formatStateUsing(fn($state) => json_decode($state) ? (json_decode($state)->payment_status == 'requires_payment_method' ? 'Payment Failed' : json_decode($state)->payment_status) : 'NA')
                    ->label('Payment status')
                    ->default('--')
                    ->limit(50),
                TextColumn::make('remarks')
                    ->searchable()
                    ->default('--')
                    ->limit(50),
                IconColumn::make('no_objection')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
                IconColumn::make('undertaking_of_waterproofing')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                //     ->relationship('building', 'name', function (Builder $query) {
                //         if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                //             $query->where('owner_association_id', Filament::getTenant()?->id);
                //         }

                //     })
                //     ->searchable()
                //     ->preload()
                //     ->label('Building'),
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::pluck('name', 'id');
                        } elseif (auth()->user()->role->name == 'Property Manager') {
                            $buildingIds = DB::table('building_owner_association')
                                ->where('owner_association_id', auth()->user()->owner_association_id)
                                ->where('active', true)
                                ->pluck('building_id');

                            return Building::whereIn('id', $buildingIds)
                                ->pluck('name', 'id');

                        }
                        $oaId = auth()->user()?->owner_association_id;
                        return Building::where('owner_association_id', $oaId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
            ])
            ->bulkActions([
                ExportBulkAction::make(),

            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
            ]);

    }

    public static function getRelations(): array
    {
        return [
            ContractorRequestRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFitOutFormsDocuments::route('/'),
            // 'view' => Pages\ViewFitOutFormsDocument::route('/{record}'),
            'edit'  => Pages\EditFitOutFormsDocument::route('/{record}/edit'),

        ];
    }
}
