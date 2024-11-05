<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantDocumentResource\Pages;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\Master\Role;
use App\Models\User\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantDocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon  = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Master';
    protected static ?string $modelLabel      = 'Resident Documents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('documentable_id')
                        ->label('Resident name')
                        ->formatStateUsing(function ($state) {
                            $user = User::find($state);
                            return $user ? $user->first_name . ' ' . $user->last_name : null;
                        })
                        ->disabled(),
                    // TextInput::make('name')->disabled(),
                    Select::make('document_library_id')
                        ->rules(['exists:document_libraries,id'])
                        ->relationship('documentLibrary', 'name')
                        ->disabled()
                        ->searchable()
                        ->placeholder('Document Library'),
                    Select::make('building_id')
                        ->relationship('building', 'name')
                        ->preload()
                        ->disabled()
                        ->default('NA')
                        ->searchable()
                        ->label('Building'),
                    TextInput::make('unit')
                        ->label('Unit number')
                        ->default('NA')
                        ->afterStateHydrated(function ($set, $record) {
                            $flatID     = FlatTenant::where('tenant_id', $record->documentable_id)->value('flat_id');
                            $unitNumber = Flat::where('id', $flatID)->value('property_number') ?? 'NA';
                            $set('unit', $unitNumber);
                        })
                        ->disabled()
                        ->dehydrated(false),
                    DatePicker::make('expiry_date')
                        ->rules(['date'])
                        ->required()
                        ->disabled()
                        ->readonly()
                        ->placeholder('Expiry date'),
                    Select::make('status')
                        ->options([
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->disabled(function (Document $record) {
                            return $record->status != 'submitted';
                        })
                        ->searchable()
                        ->live(),
                    TextInput::make('remarks')
                        ->rules(['max:150'])
                        ->visible(function (callable $get) {
                            if ($get('status') == 'rejected') {
                                return true;
                            }
                            return false;
                        })
                        ->disabled(function (Document $record) {
                            return $record->status != 'submitted';
                        })
                        ->required(),
                    FileUpload::make('url')
                        ->disk('s3')
                        ->directory('dev')
                        ->disabled()
                        ->openable(true)
                        ->downloadable(true)
                        ->label('Document')
                    // ->columnSpan([
                    //     'sm' => 1,
                    //     'md' => 1,
                    //     'lg' => 2,
                    // ])
                    ,
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('documentable_type', 'App\Models\User\User')->withoutGlobalScopes())
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Document name')
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Building')
                    ->limit(50),
                TextColumn::make('unit')
                    ->default('NA')
                    ->label('Unit number')
                    ->getStateUsing(function (Get $get, $record) {
                        $flatID = FlatTenant::where('tenant_id', $record->documentable_id)->value('flat_id');
                        return Flat::where('id', $flatID)->value('property_number');
                    })
                    ->limit(50),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('documentUsers.first_name')
                    ->searchable()
                    ->label('Resident name')
                    ->default('NA'),
                ViewColumn::make('Role')->view('tables.columns.role')->alignCenter(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('documentable_id')
                    ->options(function () {
                        $roleId = Role::whereIn('name', ['tenant', 'owner'])->pluck('id')->toArray();

                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return User::whereIn('role_id', $roleId)->pluck('first_name', 'id');
                        } else {
                            return User::whereIn('role_id', $roleId)->where('owner_association_id', auth()->user()?->owner_association_id)->pluck('first_name', 'id');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Resident'),
                SelectFilter::make('building_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Building::all()->pluck('name', 'id');
                        } else {
                            return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                ->pluck('name', 'id');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
            ])
            ->actions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View Documents')
                    ->visible(function($record){
                        if($record['status']== 'approved'){
                            return true;
                        }
                    }),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Approve Documents')
                    ->visible(function($record){
                        if($record['status']!== 'approved'){
                            return true;
                        }
                    }),

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
            'index' => Pages\ListTenantDocuments::route('/'),
            'edit'  => Pages\EditTenantDocument::route('/{record}/edit'),
        ];
    }
}
