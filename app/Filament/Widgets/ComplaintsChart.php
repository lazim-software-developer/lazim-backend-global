<?php
namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Building\Building;
use Filament\Widgets\ChartWidget;
use App\Models\Building\Complaint;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ComplaintsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Complaints';
    protected static ?string $maxHeight = '500px';
    protected static ?int $sort = 6;

    // protected int | string | array $columnSpan = 6;

    public static function canView(): bool
    {
        $user = User::find(auth()->user()->id);
        return ($user->can('view_any_complaintscomplaint')||$user->can('view_any_helpdeskcomplaint'));
    }

    protected function getData(): array
    {

        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        \Illuminate\Support\Facades\Log::info('Fetching complaints data', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'userId' => auth()->user()->id,
            'filter' => json_encode($this->filters, JSON_PRETTY_PRINT),
        ]);
        // Fetch all buildings related to the current user's OA
        $buildings = (is_null($this->filters['building'])) ? Building::where('owner_association_id', auth()->user()->owner_association_id)->get() : Building::where('id', $this->filters['building'])->where('owner_association_id', auth()->user()->owner_association_id)->get();

        // Initialize arrays to hold data
        $buildingNames = [];
        $openComplaints = [];
        $closedComplaints = [];

        foreach ($buildings as $building) {
            // Push building names to labels
            $buildingNames[] = $building->name;

            // Base query for complaints in the current building
            $complaintsQuery = Complaint::where('building_id', $building->id);

            // Apply date filters if provided
            if ($startDate) {
                $startOfDay = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $complaintsQuery->where('created_at', '>=', $startOfDay);
            }

            if ($endDate) {
                $endOfDay = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
                $complaintsQuery->where('created_at', '<=', $endOfDay);
            }

            // Count open and closed complaints for each building
            $openComplaints[] = (clone $complaintsQuery)->where('status', 'open')->count();
            $closedComplaints[] = (clone $complaintsQuery)->where('status', 'closed')->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Open Complaints',
                    'data' => $openComplaints,
                    'backgroundColor' => '#4DB6AC',
                    'borderColor' => '#ffffff',
                    'stack' => 'Complaints', // Stack the data
                ],
                [
                    'label' => 'Closed Complaints',
                    'data' => $closedComplaints,
                    'backgroundColor' => '#E57373',
                    'borderColor' => '#ffffff',
                    'stack' => 'Complaints', // Stack the data
                ],
            ],
            'labels' => $buildingNames,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
