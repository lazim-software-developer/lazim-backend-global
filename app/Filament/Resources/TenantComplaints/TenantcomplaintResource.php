<?php

namespace App\Filament\Resources\TenantComplaints;

use App\Filament\Resources\TenantComplaints\TenantcomplaintResource\Pages;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Building\FlatTenant;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantcomplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Complaints';
    protected static ?string $navigationGroup = 'Flat Management';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    MorphToSelect::make('complaintable')

                        ->types([
                            Type::make(Building::class)->titleAttribute('name'),
                            Type::make(FlatTenant::class)->titleAttribute('tenant_id'),

                        ]),
                    TextInput::make('complaintable_id')
                        ->rules(['max:255'])
                        ->hidden()
                        ->required()
                        ->placeholder('Complaintable Id'),
                    Select::make('category')
                        ->options([
                            'civil'    => 'Civil',
                            'MIP'      => 'MIP',
                            'security' => 'Security',
                            'cleaning' => 'Cleaning',
                            'others'   => 'Others',
                        ])
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Category'),
                    FileUpload::make('photo')
                        ->nullable(),
                    TextInput::make('remarks'),

                    Select::make('status')
                        ->options([
                            'pending'   => 'Pending',
                            'completed' => 'Completed',
                        ])
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Status'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('complaintable_type', 'App\Models\Building\FlatTenant')->withoutGlobalScopes())
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('complaintable_type')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                ViewColumn::make('name')->view('tables.columns.combined-column')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.first_name')
                    ->toggleable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('open_time')
                    ->toggleable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('close_time')
                    ->toggleable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index'  => Pages\ListTenantcomplaints::route('/'),
            'create' => Pages\CreateTenantcomplaint::route('/create'),
            'edit'   => Pages\EditTenantcomplaint::route('/{record}/edit'),
        ];
    }
}
