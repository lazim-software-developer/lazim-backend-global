<?php

namespace App\Console\Commands;

use App\Mail\BuildingDetachmentNotification;
use App\Models\Building\Building;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DetachExpiredBuildings extends Command
{
    protected $signature   = 'buildings:handle-detachments {--dry-run : Run without making actual changes}';
    protected $description = 'Handle building detachments and send notifications';

    private $dryRun         = false;
    private $processedCount = [
        'detached'      => 0,
        'notifications' => 0,
        'errors'        => 0,
    ];

    public function handle()
    {
        $this->dryRun = $this->option('dry-run');

        if ($this->dryRun) {
            $this->info('Running in dry-run mode - no actual changes will be made.');
        }

        $startTime = now();
        Log::info('Building detachment process started', ['dry_run' => $this->dryRun]);

        try {
            $this->handleExpiredBuildings();
            $this->sendDueNotifications();

            $duration = now()->diffInSeconds($startTime);

            $summary = [
                'duration'           => $duration,
                'buildings_detached' => $this->processedCount['detached'],
                'notifications_sent' => $this->processedCount['notifications'],
                'errors'             => $this->processedCount['errors'],
                'dry_run'            => $this->dryRun,
            ];

            Log::info('Building detachment process completed', $summary);

            $this->displaySummary($summary);
        } catch (\Exception $e) {
            Log::error('Building detachment process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Process failed: ' . $e->getMessage());
        }
    }

    private function handleExpiredBuildings()
    {
        $today = Carbon::now()->format('Y-m-d');

        $expiredRelations = DB::table('building_vendor')
            ->where('end_date', '<', $today)
            ->get();

        $this->info("Found {$expiredRelations->count()} expired building relationships");
        Log::info("Processing expired buildings", ['count' => $expiredRelations->count()]);

        foreach ($expiredRelations as $relation) {
            try {
                $building = Building::find($relation->building_id);
                $vendor   = Vendor::with('user')->find($relation->vendor_id);

                if (!$vendor) {
                    $this->logError("Vendor not found for ID: {$relation->vendor_id}");
                    continue;
                }

                if (!$vendor->user) {
                    $this->logError("User not found for Vendor ID: {$relation->vendor_id}");
                    continue;
                }

                $userEmail = $vendor->user->email;
                Log::info("Attempting to send email", [
                    'vendor_id' => $vendor->id,
                    'user_id'   => $vendor->user->id,
                    'email'     => $userEmail,
                    'building'  => $building->name,
                ]);

                if (!$this->dryRun) {
                    DB::beginTransaction();

                    try {
                        // Send email before deleting the relationship
                        Mail::to($userEmail)
                            ->send(new BuildingDetachmentNotification(
                                $building->name,
                                'detached',
                                null
                            ));

                        Log::info("Email sent successfully", ['email' => $userEmail]);

                        // Delete the relationship
                        DB::table('building_vendor')
                            ->where('building_id', $relation->building_id)
                            ->where('vendor_id', $relation->vendor_id)
                            ->where('end_date', $relation->end_date)
                            ->delete();

                        DB::commit();
                    } catch (Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to send email", [
                            'email' => $userEmail,
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                }

                $this->processedCount['detached']++;
                $this->info("Processed Building ID: {$relation->building_id} from Vendor ID: {$relation->vendor_id}");

                Log::info('Building detached', [
                    'building_id' => $relation->building_id,
                    'vendor_id'   => $relation->vendor_id,
                    'dry_run'     => $this->dryRun,
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                $this->logError("Error processing building {$relation->building_id}: " . $e->getMessage());
            }
        }
    }

    private function sendDueNotifications()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $dueRelations = DB::table('building_vendor')
            ->where('end_date', $tomorrow)
            ->get();

        $this->info("Found {$dueRelations->count()} due notifications to send");
        Log::info("Processing due notifications", ['count' => $dueRelations->count()]);

        foreach ($dueRelations as $relation) {
            try {
                $building = Building::find($relation->building_id);
                $vendor   = Vendor::with('user')->find($relation->vendor_id);

                if (!$vendor || !$vendor->user) {
                    $this->logError("User not found for Vendor ID: {$relation->vendor_id}");
                    continue;
                }

                if (!$this->dryRun) {
                    Mail::to($vendor->user->email)
                        ->send(new BuildingDetachmentNotification(
                            $building->name,
                            'due',
                            $tomorrow
                        ));
                }

                $this->processedCount['notifications']++;
                $this->info("Notification sent for Building ID: {$relation->building_id}");

                Log::info('Due notification sent', [
                    'building_id' => $relation->building_id,
                    'vendor_id'   => $relation->vendor_id,
                    'dry_run'     => $this->dryRun,
                ]);

            } catch (\Exception $e) {
                $this->logError("Error sending notification for building {$relation->building_id}: " . $e->getMessage());
            }
        }
    }

    private function logError($message)
    {
        $this->processedCount['errors']++;
        $this->error($message);
        Log::error($message);
    }

    private function displaySummary(array $summary)
    {
        $this->newLine();
        $this->info('=== Process Summary ===');
        $this->info("Duration: {$summary['duration']} seconds");
        $this->info("Buildings Detached: {$summary['buildings_detached']}");
        $this->info("Notifications Sent: {$summary['notifications_sent']}");
        $this->error("Errors Encountered: {$summary['errors']}");
        $this->info("Dry Run: " . ($summary['dry_run'] ? 'Yes' : 'No'));
        $this->newLine();
    }
}
