<?php
namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery()->where('is_announcement', 0);

        if (in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['Property Manager', 'OA'])) {
            $pmBuildings = DB::table('building_owner_association')
                ->where('owner_association_id', auth()->user()?->owner_association_id)
                ->where('active', true)
                ->pluck('building_id');

            return $query->whereHas('building', function ($query) use ($pmBuildings) {
                $query->whereIn('buildings.id', $pmBuildings);
            });
        } elseif (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
            return $query->where('owner_association_id', auth()->user()?->owner_association_id);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\CreateAction::make(),
        ];
    }
}
