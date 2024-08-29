<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanBudgetImports extends Command
{
    protected $signature = 'budget:clean-imports';
    protected $description = 'Clean all files from the budget imports directory';

    public function handle()
    {
        $directory = storage_path('app/budget_imports');

        // Check if directory exists
        if (!File::exists($directory)) {
            $this->info('Directory does not exist.');
            return;
        }

        // Delete all files in the directory
        File::cleanDirectory($directory);

        $this->info('All files in the budget imports directory have been deleted.');

        return 0; // Return success
    }
}
