<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\DocumentsResource\Pages;
use App\Filament\Resources\Building\DocumentsResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Building\FlatTenant;
use App\Models\Vendor\Vendor;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
//use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Builder;

class DocumentsResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Building Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    Select::make('document_library_id')
                        ->rules(['exists:document_libraries,id'])
                        ->required()
                        ->relationship('documentLibrary', 'name')
                        ->searchable()
                        ->placeholder('Document Library')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    RichEditor::make('url')
                        ->rules(['max:255', 'string'])
                        ->required()
                        ->placeholder('Url')
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

                    // KeyValue::make('comments')
                    //     ->required()
                    //     ->addable(true)
                    //     ->deletable(true),
                    //     // ->columnSpan([
                    //     //     'default' => 12,
                    //     //     'md' => 12,
                    //     //     'lg' => 12,
                    //     // ]),
                        
                    Repeater::make('comments')
                    ->schema([
                        TextInput::make('key')->required(),
                        
                        TextInput::make('value')->required(),
                        
                    ])
                    ->columns(2),
                    DatePicker::make('expiry_date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Expiry Date')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('accepted_by')
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

                    MorphToSelect::make('documentable')
                        
                        ->types([
                            Type::make(Building::class)->titleAttribute('name'),
                            Type::make(FlatTenant::class)->titleAttribute('tenant_id'),
                            Type::make(Vendor::class)->titleAttribute('name')
                        
                        
                        ])
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                        
                       

                    TextInput::make('documentable_id')
                        ->rules(['max:255'])
                        ->required()
                        ->placeholder('Documentable Id')
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
        ->columns([
            Tables\Columns\TextColumn::make('documentLibrary.name')
                ->toggleable()
                ->limit(50),
            Tables\Columns\TextColumn::make('url')
                ->toggleable()
                ->searchable()
                ->limit(50),
            Tables\Columns\TextColumn::make('status')
                ->toggleable()
                ->searchable(true, null, true)
                ->limit(50),
            Tables\Columns\TextColumn::make('expiry_date')
                ->toggleable()
                ->date(),
            Tables\Columns\TextColumn::make('user.first_name')
                ->toggleable()
                ->limit(50),
            ViewColumn::make('name')->view('tables.columns.document')
                ->toggleable(),
            Tables\Columns\TextColumn::make('documentable_type')
                ->toggleable()
                ->searchable(true, null, true)
                ->limit(50),
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocuments::route('/create'),
            'edit' => Pages\EditDocuments::route('/{record}/edit'),
        ];
    }    
}
