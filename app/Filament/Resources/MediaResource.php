<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Community\Post;
use App\Models\Media;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Grid::make([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 1,
                    ])->schema([
                        TextInput::make('name')
                            ->rules(['max:30', 'regex:/^[a-zA-Z\s]*$/'])
                            ->disabled()
                            ->required()
                            ->placeholder('Name'),

                        FileUpload::make('url')
                            ->disk('s3')
                            ->directory('dev')
                            ->helperText('The uploaded image must be less than 2MB.')
                            ->disabled()
                            ->required(),

                        MorphToSelect::make('mediaable')
                            ->types([
                                Type::make(Post::class)->titleAttribute('content'),
                            ])
                            ->disabled()
                            ->label('Mediaable')
                            ->required(),
                    ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->toggleable()
                    ->default('NA')
                    ->searchable(),

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
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }

    public function rules(): array
    {
        return [
            'url' => 'max:2048', // Add any other rules as needed
        ];
    }

    public function messages(): array
    {
        return [
            'url.max' => 'The uploaded image must be less than 2MB.',
        ];
    }
}
