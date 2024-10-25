<?php

namespace App\Filament\Resources\PropertyManagerResource\RelationManagers;

use App\Imports\BuildingImport;
use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class BuildingRelationManager extends RelationManager
{
    protected static string $relationship = 'buildings';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Building Name')
                    ->default('NA')->searchable(),
                Tables\Columns\TextColumn::make('from')->label('From')
                    ->default('NA')->searchable(),
                Tables\Columns\TextColumn::make('to')->searchable(),
            ])
            ->headerActions([
                Action::make('Attach Building')
                    ->slideOver()
                    ->modalWidth('lg')
                    ->form([
                        Select::make('building_id')
                            ->label('Building')
                            ->options(function (RelationManager $livewire) {

                                $ownerAssociationIds = OwnerAssociation::where('role', 'Property Manager')
                                    ->pluck('id');

                                $existingBuildingIds = DB::table('building_owner_association')
                                    ->whereIn('owner_association_id', $ownerAssociationIds)
                                    ->pluck('building_id');

                                return Building::whereNotIn('id', $existingBuildingIds)
                                    ->pluck('name', 'id');
                            })

                            ->helperText('Create a Building if it does not exist.')
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->optionsLimit(500)
                            ->live()
                            ->createOptionForm([
                                Grid::make(['default' => 2])
                                    ->schema([
                                        Grid::make([
                                            'sm' => 1,
                                            'md' => 1,
                                            'lg' => 1,
                                        ])->schema([
                                            TextInput::make('name')
                                                ->rules(['max:50', 'string'])
                                                ->required()
                                                ->placeholder('Name'),

                                            TextInput::make('property_group_id')
                                                ->rules(['max:50', 'string'])
                                                ->required()
                                                ->placeholder('Property Group Id')
                                                ->unique(
                                                    'buildings',
                                                    'property_group_id',
                                                    fn(?Model $record) => $record,
                                                ),

                                            Select::make('building_type')
                                                ->options([
                                                    'commercial'  => 'Commercial',
                                                    'residential' => 'Residential',
                                                ]),

                                            TextInput::make('address_line1')
                                                ->rules(['max:500', 'string'])
                                                ->required()
                                                ->placeholder('Address Line1'),

                                            TextInput::make('address_line2')
                                                ->rules(['max:500', 'string'])
                                                ->nullable()
                                                ->placeholder('Address Line2'),

                                            Hidden::make('owner_association_id')
                                                ->default(auth()->user()?->owner_association_id),

                                            TextInput::make('area')
                                                ->rules(['max:100', 'string'])
                                                ->required()
                                                ->placeholder('Area'),

                                            MarkdownEditor::make('description')
                                                ->toolbarButtons([
                                                    'bold',
                                                    'bulletList',
                                                    'italic',
                                                    'link',
                                                    'orderedList',
                                                    'redo',
                                                    'undo',
                                                ])
                                                ->label('About'),

                                            FileUpload::make('cover_photo')
                                                ->disk('s3')
                                                ->rules(['file', 'mimes:jpeg,jpg,png', function () {
                                                    return function (string $attribute, $value, Closure $fail) {
                                                        if ($value->getSize() / 1024 > 2048) {
                                                            $fail('The cover photo must not be greater than 2MB.');
                                                        }
                                                    };
                                                }])
                                                ->directory('dev')
                                                ->image()
                                                ->maxSize(2048)
                                                ->label('Cover Photo'),

                                            TextInput::make('floors')
                                                ->numeric()
                                                ->minValue(1)
                                                ->maxValue(999)
                                                ->disabled(function (?Model $record) {
                                                    return $record?->floors != null;
                                                })
                                                ->placeholder('Floors')
                                                ->label('Floor'),

                                            TextInput::make('parking_count')
                                                ->numeric()
                                                ->minValue(1)
                                                ->maxValue(999)
                                                ->placeholder('Parking Count')
                                                ->label('Parking Count'),

                                            Toggle::make('allow_postupload')
                                                ->rules(['boolean'])
                                                ->label('Allow post-upload'),

                                            Toggle::make('show_inhouse_services')
                                                ->rules(['boolean'])
                                                ->label('Show Personal services')
                                                ->hiddenOn('create'),
                                        ]),
                                    ]),
                            ])
                            ->createOptionModalHeading('Create Building')
                            ->createOptionUsing(function (array $data) {
                                $building = Building::create([
                                    'name'                  => $data['name'],
                                    'property_group_id'     => $data['property_group_id'],
                                    'address_line1'         => $data['address_line1'],
                                    'address_line2'         => $data['address_line2'],
                                    'area'                  => $data['area'],
                                    'description'           => $data['description'],
                                    'floors'                => $data['floors'],
                                    'parking_count'         => $data['parking_count'],
                                    'building_type'         => $data['building_type'],
                                    'allow_postupload'      => $data['allow_postupload'],
                                    'show_inhouse_services' => $data['show_inhouse_services'],
                                    'lat'                   => $data['lat'] ?? null,
                                    'lng'                   => $data['lng'] ?? null,
                                ]);

                                if (isset($data['cover_photo'])) {
                                    $building->addMediaFromDisk($data['cover_photo'], 's3')
                                        ->toMediaCollection('Buildings');
                                }

                                return $building->id;
                            }),

                        Grid::make(2)->schema([
                            DatePicker::make('from')
                                ->required()
                                ->default(Carbon::now()->format('Y-m-d'))
                                ->afterStateUpdated(function (Set $set) {
                                    $set('to', null);
                                }),

                            DatePicker::make('to')
                                ->after('from')
                                ->required()
                                ->validationMessages([
                                    'after' => 'The "to" date must be after the "from" date.',
                                ]),
                        ]),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        $buildingId = $data['building_id'];

                        // Check if the building exists, if not, create it
                        $building = Building::firstOrCreate(
                            ['id' => $buildingId],
                            [
                                'name'                  => $data['name'] ?? '',
                                'property_group_id'     => $data['property_group_id'] ?? '',
                                'address_line1'         => $data['address_line1'] ?? '',
                                'address_line2'         => $data['address_line2'] ?? '',
                                'area'                  => $data['area'] ?? '',
                                'description'           => $data['description'] ?? '',
                                'floors'                => $data['floors'] ?? null,
                                'parking_count'         => $data['parking_count'] ?? null,
                                'building_type'         => $data['building_type'] ?? null,
                                'allow_postupload'      => $data['allow_postupload'] ?? false,
                                'show_inhouse_services' => $data['show_inhouse_services'] ?? false,
                                'lat'                   => $data['lat'] ?? null,
                                'lng'                   => $data['lng'] ?? null,
                                'owner_association_id' => $livewire->ownerRecord->id
                            ]
                        );

                        DB::table('building_owner_association')->insert([
                            'owner_association_id' => $livewire->ownerRecord->id,
                            'building_id'          => $building->id,
                            'from'                 => $data['from'],
                            'to'                   => $data['to'],
                        ]);
                    }),

                Action::make('Upload Buildings')
                    ->form([
                        Forms\Components\FileUpload::make('excel_file')
                            ->label('Upload File')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required()
                            ->disk('local')
                            ->directory('budget_imports'),
                    ])
                    ->action(function ($record, array $data, $livewire) {
                        $filePath = $data['excel_file'];
                        $fullPath = storage_path('app/' . $filePath);
                        $oaId     = $this->ownerRecord->id;

                        if (!file_exists($fullPath)) {
                            Log::error("File not found at path: ", [$fullPath]);
                            return;
                        }

                        Excel::import(new BuildingImport($oaId), $fullPath);
                    }),

                ExportAction::make('exporttemplate')
                    ->exports([
                        ExcelExport::make()
                            ->modifyQueryUsing(fn(Builder $query) => $query->where('id', 0))
                            ->withColumns([
                                Column::make('name'),
                                Column::make('building_type'),
                                Column::make('property_group_id'),
                                Column::make('address_line1'),
                                Column::make('area'),
                                Column::make('floors'),
                                Column::make('parking_count'),
                                Column::make('from'),
                                Column::make('to'),
                            ]),
                    ])
                    ->label('Download sample format file'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->emptyStateDescription('Attach or Upload a Building to get started.')
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
