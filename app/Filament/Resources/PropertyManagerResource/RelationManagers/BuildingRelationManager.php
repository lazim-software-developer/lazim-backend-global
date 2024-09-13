<?php

namespace App\Filament\Resources\PropertyManagerResource\RelationManagers;

use App\Imports\BuildingImport;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Forms;
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
                Tables\Columns\TextColumn::make('name')->label('Building Name')->searchable(),
                Tables\Columns\TextColumn::make('from')->label('From')->searchable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        return $query->whereDoesntHave('ownerAssociations');
                    })
                    ->form(fn(AttachAction $action): array => [
                        $action->getRecordSelect()->required(),
                        DatePicker::make('from')->default(Carbon::now()->format('d-M-Y')),
                    ]),
                Action::make('feature')
                    ->label('Upload Buildings') // Set a label for your action
                    ->form([
                        Forms\Components\FileUpload::make('excel_file')
                            ->label('Upload File')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // for .xlsx
                                'application/vnd.ms-excel', // for .xls
                            ])
                            ->required()
                            ->disk('local') // or your preferred disk
                            ->directory('budget_imports'), // or your preferred directory
                    ])
                    ->action(function ($record, array $data, $livewire) {

                        $filePath     = $data['excel_file'];
                        $fullPath     = storage_path('app/' . $filePath);
                        $oaId = $this->ownerRecord->id;

                        if (!file_exists($fullPath)) {
                            Log::error("File not found at path: ", [$fullPath]);
                        }

                        // Now import using the file path
                        Excel::import(new BuildingImport($oaId), $fullPath); // Notify user of success
                    }),
                ExportAction::make('exporttemplate')->exports([
                    ExcelExport::make()
                        ->modifyQueryUsing(fn(Builder $query) => $query->where('id', 0))
                        ->withColumns([
                            Column::make('name'),
                            Column::make('property_group_id'),
                            Column::make('address_line1'),
                            Column::make('area'),
                        ]),
                ])->label('Download sample format file')
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
