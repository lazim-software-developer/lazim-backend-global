<?php
namespace App\Filament\Resources\UserApprovalResource\Pages;

use DB;
use App\Models\Master\Role;
use App\Models\UserApproval;
use Filament\Facades\Filament;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserApprovalResource;

class ListUserApprovals extends ListRecords
{
    protected static string $resource = UserApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        $pmbuildingIds = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        $flats = DB::table('flats')->whereIn('building_id', $pmbuildingIds)->pluck('id')->toArray();

        // dd($pmbuildingIds);
        $pmFlats = DB::table('property_manager_flats')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('flat_id')
            ->toArray();

        if(auth()->user()->role->name == 'Admin') {
            return parent::getTableQuery()->latest();
        }
        if (auth()->user()->role->name == 'Property Manager'
        || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                ->pluck('role')[0] == 'Property Manager') {
            return parent::getTableQuery()
                ->whereIn('flat_id', $pmFlats)
                ->latest();
        }
        $tenant = Filament::getTenant();
        // Log::info($tenant);
        if ($tenant) {
            return parent::getTableQuery()->whereIn('flat_id', $flats)->latest();
        }
        return parent::getTableQuery()->latest();
    }

    // public function getTabs(): array
    // {
    //     if (auth()->user()->owner_association_id !== 13) {
    //         return [
    //             'all' => Tab::make('All')
    //                 ->modifyQueryUsing(fn (Builder $query) => $query)
    //                 ->badge(parent::getTableQuery()->count())
    //                 // ->badge(UserApproval::query()->count())
    //                 ->badgeColor('success'),
    //             'pending' => Tab::make('Pending')
    //                 ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('status'))
    //                 ->badge(parent::getTableQuery()->whereNull('status')->count())
    //                 // ->badge(UserApproval::query()->whereNull('status')->count())
    //                 ->badgeColor('warning'),
    //             'active' => Tab::make('Approved')
    //                 ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'))
    //                 ->badge(parent::getTableQuery()->where('status', 'approved')->count())
    //                 // ->badge(UserApproval::query()->where('status', 'approved')->count())
    //                 ->badgeColor('success'),
    //             'inactive' => Tab::make('Rejected')
    //                 ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'))
    //                 ->badge(parent::getTableQuery()->where('status', 'rejected')->count())
    //                 // ->badge(UserApproval::query()->where('status', 'rejected')->count())
    //                 ->badgeColor('danger'),
    //         ];
    //     }
    // }
    public function getDefaultActiveTab(): string | int | null
    {
        return 'pending';
    }
}
