<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HelpdeskcomplaintResource\Pages;
use App\Filament\Resources\HelpdeskcomplaintResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Building\FlatTenant;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class HelpdeskcomplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Complaint';

    protected static ?string $navigationGroup = 'Help Desk';

    public static function form(Form $form): Form
    {
        // dd($form);
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2
                ])
                    ->schema([
                        Hidden::make('complaintable_type')
                            ->default('App\Models\Building\FlatTenant'),
                        Hidden::make('complaintable_id')
                            ->default(1),
                        Hidden::make('owner_association_id')
                            ->default(auth()->user()->owner_association_id),
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('user_id')
                            ->relationship('user', 'id')
                            ->options(function () {
                                $tenants = DB::table('flat_tenants')->pluck('tenant_id');
                                // dd($tenants);
                                return DB::table('users')
                                    ->whereIn('users.id', $tenants)
                                    ->select('users.id', 'users.first_name')
                                    ->pluck('users.first_name', 'users.id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User'),
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
                            ->searchable()
                            ->placeholder('Category'),
                        FileUpload::make('photo')
                            ->disk('s3')
                            ->directory('dev')
                            ->maxSize(2048)
                            ->nullable(),
                        TextInput::make('complaint')
                            ->placeholder('Complaint'),
                        Hidden::make('status')
                            ->default('pending'),
                        Hidden::make('complaint_type')
                            ->default('help_desk'),
                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            // ->modifyQueryUsing(fn(Builder $query) => $query->where('complaintable_type', 'App\Models\Building\Building')->withoutGlobalScopes())
            // ->poll('60s')
            ->columns([
                // ViewColumn::make('name')->view('tables.columns.combined-column')
                //     ->toggleable(),
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('category')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('complaint')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('status')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),


            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->label('Building')
                    ->preload()
            ])
            ->actions([
                Action::make('Update Status')
                    ->visible(fn ($record) => $record->status === 'open')
                    ->button()
                    ->form([
                        Select::make('status')
                            ->options([
                                'open'   => 'Open',
                                'resolved' => 'Resolved',
                            ])
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->visible(function (callable $get) {
                                if ($get('status') == 'resolved') {
                                    return true;
                                }
                                return false;
                            })
                            ->required(),
                    ])
                    ->fillForm(fn (Complaint $record): array => [
                        'status' => $record->status,
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (Complaint $record, array $data): void {
                        if ($data['status'] == 'resolved') {
                            $record->status = $data['status'];
                            $record->remarks = $data['remarks'];
                            $record->save();
                        } else {
                            $record->status = $data['status'];
                            $record->save();
                        }
                    })
                    ->slideOver()
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
            'view' => Pages\ViewHelpdeskcomplaint::route('/{record}'),
        ];
    }
}
