<?php

namespace App\Console\Commands;

use App\Mail\PMBuildingDetachmentNotification;
use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DetachExpiredPMBuildings extends Command
{
    protected $signature   = 'buildings:handle-pm-detachments {--dry-run : Run without making actual changes}';
    protected $description = 'Handle property manager building detachments and send notifications';

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
        Log::info('Property Manager building detachment process started', ['dry_run' => $this->dryRun]);

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

            Log::info('Property Manager building detachment process completed', $summary);
            $this->displaySummary($summary);
        } catch (\Exception $e) {
            Log::error('Property Manager building detachment process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Process failed: ' . $e->getMessage());
        }
    }

    private function handleExpiredBuildings()
    {
        $today = Carbon::now()->format('Y-m-d');

        $expiredRelations = DB::table('building_owner_association')
            ->join('owner_associations', 'building_owner_association.owner_association_id', '=', 'owner_associations.id')
            ->where('building_owner_association.to', '<', $today)
            ->where('building_owner_association.active', true)
            ->where('owner_associations.role', 'Property Manager')
            ->select('building_owner_association.*', 'owner_associations.email')
            ->get();

        $this->info("Found {$expiredRelations->count()} expired building relationships");
        Log::info("Processing expired buildings", ['count' => $expiredRelations->count()]);

        foreach ($expiredRelations as $relation) {
            try {
                $building        = Building::find($relation->building_id);
                $propertyManager = OwnerAssociation::find($relation->owner_association_id);

                if (!$building || !$propertyManager) {
                    $this->logError("Building or Property Manager not found for relation ID: {$relation->id}");
                    continue;
                }

                if (!$this->dryRun) {
                    DB::beginTransaction();

                    try {
                        // Send notification to property manager
                        Mail::to($propertyManager->email)
                            ->send(new PMBuildingDetachmentNotification(
                                $building->name,
                                'detached',
                                null
                            ));

                        // Send notifications to all users associated with this property manager
                        foreach ($propertyManager->users as $user) {
                            if ($user->email !== $propertyManager->email) {
                                Mail::to($user->email)
                                    ->send(new PMBuildingDetachmentNotification(
                                        $building->name,
                                        'detached',
                                        null
                                    ));
                            }
                        }

                        // Update the relationship as inactive
                        DB::table('building_owner_association')
                            ->where('building_id', $relation->building_id)
                            ->where('owner_association_id', $relation->owner_association_id)
                            ->where('to', $relation->to)
                            ->update(['active' => false]);

                        DB::commit();

                        $this->processedCount['detached']++;
                        $this->info("Processed Building: {$building->name} for Property Manager: {$propertyManager->email}");

                        Log::info('Building detached', [
                            'building' => $building->name,
                            'pm_email' => $propertyManager->email,
                            'dry_run'  => $this->dryRun,
                        ]);

                    } catch (Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }
                }
            } catch (Exception $e) {
                $this->logError("Error processing building {$relation->building_id}: " . $e->getMessage());
            }
        }
    }

    private function sendDueNotifications()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $dueRelations = DB::table('building_owner_association')
            ->join('owner_associations', 'building_owner_association.owner_association_id', '=', 'owner_associations.id')
            ->where('building_owner_association.to', $tomorrow)
            ->where('building_owner_association.active', true)
            ->where('owner_associations.role', 'Property Manager')
            ->select('building_owner_association.*', 'owner_associations.email')
            ->get();

        $this->info("Found {$dueRelations->count()} due notifications to send");
        Log::info("Processing due notifications", ['count' => $dueRelations->count()]);

        foreach ($dueRelations as $relation) {
            try {
                $building        = Building::find($relation->building_id);
                $propertyManager = OwnerAssociation::find($relation->owner_association_id);

                if (!$building || !$propertyManager) {
                    $this->logError("Building or Property Manager not found for relation ID: {$relation->id}");
                    continue;
                }

                if (!$this->dryRun) {
                    // Send notification to property manager
                    Mail::to($propertyManager->email)
                        ->send(new PMBuildingDetachmentNotification(
                            $building->name,
                            'due',
                            $tomorrow
                        ));

                    // Send notifications to all associated users
                    foreach ($propertyManager->users as $user) {
                        if ($user->email !== $propertyManager->email) {
                            Mail::to($user->email)
                                ->send(new PMBuildingDetachmentNotification(
                                    $building->name,
                                    'due',
                                    $tomorrow
                                ));
                        }
                    }

                    $this->processedCount['notifications']++;
                    $this->info("Due notification sent for Building: {$building->name}");

                    Log::info('Due notification sent', [
                        'building' => $building->name,
                        'pm_email' => $propertyManager->email,
                        'dry_run'  => $this->dryRun,
                    ]);
                }
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
