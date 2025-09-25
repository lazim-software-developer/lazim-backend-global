<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Module;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Resources\ModuleResource\Pages;

class ModuleResource extends Resource
{
    protected static ?string $model                 = Module::class;
    protected static ?string $modelLabel            = 'Module';
    protected static ?string $navigationIcon        = 'heroicon-o-credit-card';
    protected static bool $shouldRegisterNavigation = true;
    protected static bool $isScopedToTenant         = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')
                    ->schema([
                        Grid::make([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ])->schema([
                            TextInput::make('name')
                                ->rules(['regex:/^[a-zA-Z0-9\s]*$/'])
                                ->required()
                                ->disabled(function (callable $get) {
                                    return Role::where('id', auth()->user()->role_id)
                                        ->first()->name != 'Admin' && DB::table('modules')
                                        ->where('name', $get('name'))
                                        ->exists();
                                })
                                ->placeholder('Name'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
         return $table
            ->defaultSort('created_at', 'desc')
            ->poll('60s')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->default('NA')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),

                // single delete button with notification
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()->hasRole('Admin'))
                    ->successNotificationTitle('Module Deleted Successfully'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->hasRole('Admin'))
                        ->successNotificationTitle('Selected Modules Deleted Successfully'),
                ]),
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
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }
}
