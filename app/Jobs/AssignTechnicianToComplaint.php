<?php

namespace App\Jobs;

use App\Models\Building\Complaint;
use App\Models\TechnicianVendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignTechnicianToComplaint implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $complaint;

    /**
     * Create a new job instance.
     *
     * @param Complaint $complaint
     * @return void
     */
    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $serviceId = $this->complaint->service_id;

        // Fetch technician_vendor_ids for the given service
        $technicianVendorIds = DB::table('service_technician_vendor')
                                 ->where('service_id', $serviceId)
                                 ->pluck('technician_vendor_id');

        // Fetch technicians who are active and match the service
        $technicians = TechnicianVendor::whereIn('id', $technicianVendorIds)
                                       ->where('active', true)
                                       ->withCount(['complaint' => function ($query) {
                                           $query->where('status', 'open');
                                       }])
                                       ->orderBy('id', 'asc')
                                       ->get();

        $selectedTechnician = $technicians->first();

        if ($selectedTechnician) {
            $this->complaint->technician_id = $selectedTechnician->technician_id;
            $this->complaint->save();
        } else {
            Log::info("No technicians to add", []);
        }
    }
}