<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HelpdeskcomplaintResource\Pages;
use App\Filament\Resources\HelpdeskcomplaintResource\RelationManagers;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HelpdeskcomplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Complaint';

    protected static ?string $navigationGroup = 'Help Desk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2])
                    ->schema([
                        MorphToSelect::make('complaintable')
                            ->types([
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
            // ->modifyQueryUsing(fn(Builder $query) => $query->where('complaintable_type', 'App\Models\Building\Building')->withoutGlobalScopes())
            // ->poll('60s')
            ->columns([
                ViewColumn::make('name')->view('tables.columns.combined-column')
                    ->toggleable(),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->limit(50),
                TextColumn::make('category')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('open_time')
                    ->toggleable()
                    ->dateTime(),
                TextColumn::make('status')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('close_time')
                    ->toggleable()
                    ->dateTime(),

            ])
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
            'index' => Pages\ListHelpdeskcomplaints::route('/'),
            'create' => Pages\CreateHelpdeskcomplaint::route('/create'),
            'edit' => Pages\EditHelpdeskcomplaint::route('/{record}/edit'),
        ];
    }    
}
