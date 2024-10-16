<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use App\Models\Complaintsenquiry;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\ComplaintsenquiryResource\Pages;
use App\Filament\Resources\ComplaintsenquiryResource\RelationManagers;

class ComplaintsenquiryResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Enquiries';

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
                            ->default(auth()->user()?->owner_association_id),
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->disabled()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('user_id')
                            ->relationship('user', 'first_name')
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
                            ->disabled()
                            ->required()
                            ->label('User'),
                        Textarea::make('complaint')
                            ->label('Enquiry')
                            ->disabled(),
                        Textarea::make('complaint_details')
                            ->label('Enquiry Details')
                            ->disabled(),
                        Hidden::make('status')
                            ->default('open'),
                        Hidden::make('complaint_type')
                            ->default('enquiries'),
                        Repeater::make('media')
                            ->relationship()
                            ->disabled()
                            ->schema([
                                FileUpload::make('url')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->maxSize(2048)
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->label('File'),
                            ]),
                        DatePicker::make('created_at')
                            ->label('Created On')
                            ->disabled(),
                        Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in-progress' => 'In-Progress',
                                'closed' => 'Closed',
                            ])
                            ->disabled(function (Complaint $record) {
                                return $record->status == 'closed';
                            })
                            ->required()
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:150'])
                            // ->visible(function (callable $get) {
                            //     if ($get('status') == 'closed') {
                            //         return true;
                            //     }
                            //     return false;
                            // })
                            ->disabled(function (Complaint $record) {
                                return $record->status == 'closed';
                            })
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Ticket Number'),
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->label('Unit')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('complaint')
                    ->toggleable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable()
                    ->label('Enquiry'),
                TextColumn::make('complaint_details')
                    ->toggleable()
                    ->default('NA')
                    ->limit(20)
                    ->searchable()
                    ->label('Enquiry Details'),
                TextColumn::make('status')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', Filament::getTenant()?->id);
                        }
                    })
                    ->searchable()
                    ->label('Building')
                    ->preload()
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),])
            ;


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
            // 'view' => Pages\ViewComplaintsenquiry::route('/{record}'),
            'edit' => Pages\EditComplaintsenquiry::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_complaintsenquiry');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_complaintsenquiry');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_complaintsenquiry');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_complaintsenquiry');
    }
}
