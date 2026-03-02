<?php

namespace App\Console\Commands;

use App\Jobs\UpdateTemplates as UpdateTemplatesJob;
use Exception;
use Illuminate\Console\Command;

class UpdateTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'templates:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync templates from Duda API to the local database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Syncing templates from Duda...');
            $job = new UpdateTemplatesJob;
            $job->handle();
            $this->info('Templates updated successfully.');

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('Failed to update templates: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
