<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintsenquiryResource\Pages;
use App\Filament\Resources\ComplaintsenquiryResource\RelationManagers;
use App\Models\Building\Complaint;
use App\Models\Complaintsenquiry;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ComplaintsenquiryResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Enquirie';

    protected static ?string $navigationGroup = 'Happiness center';
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2])
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
                        ->relationship('user','id')
                        ->options(function(){
                            $tenants = DB::table('flat_tenants')->pluck('tenant_id');
                            // dd($tenants);
                            return DB::table('users')
                                ->whereIn('users.id',$tenants)
                                ->select('users.id','users.first_name')
                                ->pluck('users.first_name','users.id')
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
                        ->searchable()
                        ->required()
                        ->placeholder('Category'),
                    FileUpload::make('photo')
                        ->disk('s3')
                        ->directory('dev')
                        ->maxSize(2048)
                        ->image()
                        ->nullable(),
                    TextInput::make('complaint')
                        ->placeholder('Enquiry'),
                    TextInput::make('complaint_details')
                        ->placeholder('Complaint Details'),
                    Select::make('status')
                        ->options([
                            'pending'   => 'Pending',
                            'resolved' => 'Resolved',
                            ])
                        ->default('pending')
                        ->searchable()
                        ->required()
                        ->placeholder('Status')
                        ->live(),
                    Hidden::make('complaint_type')
                        ->default('enquiries'),
                    TextInput::make('remarks')
                        ->disabled(fn (Get $get) => $get('status') !== 'resolved')
                        ->hiddenOn('create')
                        ->label('Remarks'),
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
                ->default('NA')
                ->searchable()
                ->limit(50),
            TextColumn::make('category')
                ->toggleable()
                ->default('NA')
                ->searchable()
                ->limit(50),
            TextColumn::make('complaint')
                ->toggleable()
                ->default('NA')
                ->searchable()
                ->label('Enquiry'),
            TextColumn::make('complaint_details')
                ->toggleable()
                ->default('NA')
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
            'index' => Pages\ListComplaintsenquiries::route('/'),
            'create' => Pages\CreateComplaintsenquiry::route('/create'),
            'edit' => Pages\EditComplaintsenquiry::route('/{record}/edit'),
        ];
    }    
}
