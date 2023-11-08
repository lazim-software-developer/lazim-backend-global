<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintssuggessionResource\Pages;
use App\Filament\Resources\ComplaintssuggessionResource\RelationManagers;
use App\Models\Building\Complaint;
use App\Models\Complaintssuggession;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ComplaintssuggessionResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Suggestion';

    protected static ?string $navigationGroup = 'Happiness center';
    public static function form(Form $form): Form
    {
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
                            ->image()
                            ->nullable(),
                        TextInput::make('complaint')
                            ->placeholder('Suggestion'),
                        TextInput::make('complaint_details')
                            ->placeholder('Complaint Details'),
                        Hidden::make('status')
                            ->default('open'),
                        Hidden::make('complaint_type')
                            ->default('suggestions'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('complaint')
                    ->toggleable()
                    ->searchable()
                    ->label('Suggestion'),
                TextColumn::make('complaint_details')
                    ->toggleable()
                    ->searchable()
                    ->label('Complaint Details'),
                TextColumn::make('status')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),

            ])
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->label('Building')
                    ->preload()
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
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
                            }),
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
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListComplaintssuggessions::route('/'),
            'view' => Pages\ViewComplaintssuggession::route('/{record}'),
        ];
    }
}
