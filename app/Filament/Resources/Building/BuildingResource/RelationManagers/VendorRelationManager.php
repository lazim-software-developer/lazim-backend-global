<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\AttachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VendorRelationManager extends RelationManager
{
    protected static string $relationship = 'vendors';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([[
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ]])->schema([
                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->placeholder('Name'),
                    Select::make('owner_id')->label('Manager Name')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->preload()
                        ->getSearchResultsUsing(fn (string $search): array => User::where('role_id', 1, "%{$search}%")->limit(50)->pluck('first_name', 'id')->toArray())
                        ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->first_name)
                        ->placeholder('Manager Name'),
                    TextInput::make('tl_number')->label('Trade License Number')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->unique(
                            'vendors',
                            'tl_number',
                            fn (?Model $record) => $record
                        )
                        ->placeholder('Trade License Number'),
                    DatePicker::make('tl_expiry')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Expiry Date'),
                    Select::make('status')
                        ->required()
                        ->searchable()
                        ->options([
                            'all' => 'All',
                            'pending' => 'Pending',
                            'resolved' => 'Resolved',
                        ])
                        ->placeholder('status'),
                    Toggle::make('active')
                        ->rules(['boolean']),
                    TextInput::make('remarks')
                        ->default('NA'),
                ]),
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->limit(50),
                Tables\Columns\TextColumn::make('user.first_name')->label('Manager')->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\DetachAction::make()->label('Remove'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add')
                    ->recordSelect(function (RelationManager $livewire) {
                        $buildingId = $livewire->ownerRecord->id;

                        // Get all the Vendors
                        $allVendors = Vendor::all()->pluck('id')->toArray();
                        $existingVendors =  DB::table('building_vendor')
                            ->where('building_id', $buildingId)
                            ->whereIn('vendor_id', $allVendors)->pluck('vendor_id')->toArray();
                        $notSelectedVendors = Vendor::whereHas('ownerAssociation', function ($query) {
                            $query->where('owner_association_id', Filament::getTenant()->id);
                        })->whereNotIn('id', $existingVendors)->pluck('name', 'id')->toArray();
                        return Select::make('recordId')
                            ->label('Vendors')
                            ->options($notSelectedVendors)
                            ->searchable()
                            ->required()
                            ->preload();
                    })
            ]);
    }
}
