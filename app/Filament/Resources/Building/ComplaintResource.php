<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\ComplaintResource\Pages;
use App\Filament\Resources\Building\ComplaintResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Building\FlatTenant;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ViewColumn;


class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Incident Reports';

    protected static ?string $navigationGroup = 'Building Management';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    MorphToSelect::make('complaintable')
                        
                        ->types([
                            Type::make(Building::class)->titleAttribute('name'),
                            Type::make(FlatTenant::class)->titleAttribute('tenant_id'),
                        
                            ]),
                       
                        

                    TextInput::make('complaintable_id')
                        ->rules(['max:255'])
                        ->placeholder('Complaintable Id')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->placeholder('User')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    // TextInput::make('Complaintable_Type')->default('Incident Report')
                    //     ->rules(['max:50', 'string'])
                    //     ->disabled()
                    //     ->required()
                    //     ->placeholder('Complaint Type')
                    //     ->columnSpan([
                    //         'default' => 12,
                    //         'md' => 12,
                    //         'lg' => 12,
                    //     ]),

                    // Select::make('Category')
                    //     ->options([
                    //         'civil'=>'Civil',
                    //         'MIP'=>'MIP',
                    //         'security'=>'Security',
                    //         'cleaning'=>'Cleaning',
                    //         'others'=>'Others'
                    //     ])
                    //     ->rules(['max:50', 'string'])
                    //     ->required()
                    //     ->placeholder('Category')
                    //     ->columnSpan([
                    //         'default' => 12,
                    //         'md' => 12,
                    //         'lg' => 12,
                    //     ]),

                    TextInput::make('category')
                        ->rules(['max:255'])
                        ->placeholder('Category')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    DateTimePicker::make('open_time')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Open Time')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    DateTimePicker::make('close_time')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Close Time')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    FileUpload::make('photo')
                        ->nullable()
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    KeyValue::make('remarks')
                        ->required()
                        ->required()
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('status')
                        ->options([
                            'pending'=>'Pending'
                        ])
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Status')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                        
                         
                 ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('complaintable_type','App\Models\Building\Building')->withoutGlobalScopes())
            ->columns([
                Tables\Columns\TextColumn::make('complaintable_type')
                    ->toggleable()
                    ->searchable(true, null, true),
                    

                ViewColumn::make('name')->view('tables.columns.combined-column')
                     ->toggleable(),
                   
                // Tables\Columns\TextColumn::make('Complaintable_Type')
                //      ->toggleable()
                //      ->searchable(true, null, true)
                //      ->limit(50),
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
            ->filters([
                
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
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }    
}
