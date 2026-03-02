<?php

namespace App\Console\Commands;

use App\Jobs\RefreshSiteImages as RefreshSiteImagesJob;
use Exception;
use Illuminate\Console\Command;

class RefreshSiteImages extends Command
{
    protected $signature = 'sites:refresh-images';

    protected $description = 'Refresh site images for websites that need updating';

    public function handle(): int
    {
        try {
            $this->info('Refreshing site images...');
            $job = new RefreshSiteImagesJob;
            $job->handle();
            $this->info('Site images refresh completed.');

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('Failed to refresh site images: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
