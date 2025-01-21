<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorFormResource\Pages;
use App\Filament\Resources\VisitorFormResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Master\Role;
use App\Models\Visitor\FlatVisitor;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class VisitorFormResource extends Resource
{
    protected static ?string $model = FlatVisitor::class;
    protected static ?string $title = 'Visitor';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Visitors';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('flat_id')->label('Flat')
                ->relationship('flat', 'property_number')->disabled(),
                TextInput::make('name')->disabled(),
                TextInput::make('email')->disabled(),
                DatePicker::make('start_time')->label('Date')->disabled(),
                TextInput::make('time_of_viewing')->label('Time')->disabled(),
                TextInput::make('number_of_visitors')->disabled(),
                Select::make('building_id')->relationship('building','name')->label('Building')->disabled()->default('NA'),
                                Repeater::make('guestDocuments')->label('Documents')
                                    ->relationship('guestDocuments')->disabled()
                                    ->schema([
                                        TextInput::make('name')
                                            ->rules(['max:30', 'regex:/^[a-zA-Z\s]*$/'])
                                            ->required()
                                            ->placeholder('Name'),
                                        FileUpload::make('url')
                                            ->disk('s3')
                                            ->rules('file|mimes:jpeg,jpg,png|max:2048')
                                            ->directory('dev')
                                            ->openable(true)
                                            ->downloadable(true)
                                            ->image()
                                            ->maxSize(2048)
                                            ->required()
                                            ->label('File')

                                    ])
                                    ->columns(2)
                                    ->columnSpan([
                                        'sm' => 1,
                                        'md' => 1,
                                        'lg' => 2,
                                    ]),
                                Select::make('status')
                                    ->options([
                                        'approved' => 'Approve',
                                        'rejected' => 'Reject',
                                    ])
                                    ->disabled(function(FlatVisitor $record){
                                        return $record->status != null;
                                    })
                                    ->required()
                                    ->searchable()
                                    ->live(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                ->searchable()
                ->default('NA')
                ->label('Ticket number'),
                TextColumn::make('building.name')
                ->label('Building')
                ->default('NA'),
                TextColumn::make('flat.property_number')
                ->label('Flat'),
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('start_time')->date('Y-m-d')
                    ->label('Date')
                    // ->date()
                    ->default('NA'),
                TextColumn::make('time_of_viewing')
                    ->label('Time')
                    // ->time()
                    ->default('NA'),
                TextColumn::make('number_of_visitors')->default('NA'),
                TextColumn::make('status')->default('Pending'),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('building')
                ->form([
                    Select::make('Building')
                        ->searchable()
                        ->options(function () {
                            if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                return Building::all()->pluck('name', 'id');
                            } else {
                                $buildingId = DB::table('building_owner_association')->where('owner_association_id',auth()->user()?->owner_association_id)->where('active',true)->pluck('building_id');
                                return Building::whereIn('id',$buildingId)->pluck('name', 'id');
                            }
                        })
                        ->reactive()
                        ->afterStateUpdated(function (callable $set) {
                            $set('flat', null);
                        }),
                    
                    Select::make('flat')
                        ->searchable()
                        ->options(function (callable $get) {
                            $buildingId = $get('Building'); // Get selected building ID
                            if (empty($buildingId)) {
                                return []; 
                            }
            
                            return Flat::where('building_id', $buildingId)->pluck('property_number', 'id');
                        }),
                ])
                ->columns(2) 
                ->query(function (Builder $query, array $data): Builder {
                    if (!empty($data['Building'])) {
                        $flatIds = Flat::where('building_id', $data['Building'])->pluck('id');
                        $query->whereIn('flat_id', $flatIds);
                    }
                    if (!empty($data['flat'])) {
                        $query->where('flat_id', $data['flat']);
                    }
            
                    return $query;
                }),

                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'NA' => 'Pending'
                            ])
                            ->label('Status')
                            ->placeholder('Select Status')
                            ->required(),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        $selectedStatus = $data['status'] ?? null;
                        
                        if ($selectedStatus === 'NA') {
                            $query->whereNull('status')
                                    ->orWhereNotIn('status', ['approved', 'rejected']);
                        }elseif ($selectedStatus !== null) {
                            $query->where('status', $selectedStatus);
                        }

                        return $query;
                    })

            
            ])
            ->filtersFormColumns(3) 
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListVisitorForms::route('/'),
            // 'create' => Pages\CreateVisitorForm::route('/create'),
            'edit' => Pages\EditVisitorForm::route('/{record}/edit'),
        ];
    }
}
