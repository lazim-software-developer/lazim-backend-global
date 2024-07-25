<?php

namespace App\Filament\Widgets;

use App\Models\Master\Role;
use Filament\Widgets\ChartWidget;
use App\Models\Building\Complaint;

class TasksChart extends ChartWidget
{
    protected static ?string $heading = 'Tasks';
    protected static ?string $maxHeight = '200px';
    protected static ?int $sort = 10;
    protected function getData(): array
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            $complaints = Complaint::count();
            $tenantComplaints = Complaint::where('complaint_type', 'tenant_complaint')->count();
            $helpdesk = Complaint::where('complaint_type', 'help_desk')->count();
            return [
                'datasets' => [
                    [
                        'label' => ['Suggestions', 'Enquiries'],
                        'data' => [$tenantComplaints, $helpdesk],
                        'backgroundColor' => ['#5afaa7', '#fa5a92'],
                        'borderColor' => '#ffffff',
                    ],
                ],
                'labels' => ['Tenant Complaints', 'HelpDesk'],
            ];
        } else {
            $complaints = Complaint::where('owner_association_id', auth()->user()?->owner_association_id)->count();
            $tenantComplaints = Complaint::where('owner_association_id', auth()->user()?->owner_association_id)->where('complaint_type', 'tenant_complaint')->count();
            $helpdesk = Complaint::where('owner_association_id', auth()->user()?->owner_association_id)->where('complaint_type', 'help_desk')->count();
            return [
                'datasets' => [
                    [
                        'label' => ['Suggestions', 'Enquiries'],
                        'data' => [$tenantComplaints, $helpdesk],
                        'backgroundColor' => ['#5afaa7', '#fa5a92'],
                        'borderColor' => '#ffffff',
                    ],
                ],
                'labels' => ['Tenant Complaints', 'HelpDesk'],
            ];
        }
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
