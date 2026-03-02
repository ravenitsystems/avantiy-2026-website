<?php

namespace App\Console\Commands;

use App\Services\Duda;
use Exception;
use Illuminate\Console\Command;

class RemoveDudaSite extends Command
{
    protected $signature = 'duda:remove-site {site_id : The Duda site ID to remove}';

    protected $description = 'Remove a site from Duda and mark it as deleted in the database';

    public function handle(): int
    {
        $siteId = $this->argument('site_id');

        if ($siteId === null || trim((string) $siteId) === '') {
            $this->error('Site ID is required.');

            return self::FAILURE;
        }

        $siteId = trim((string) $siteId);

        try {
            $this->info("Removing site {$siteId} from Duda...");
            Duda::removeSiteFromDuda($siteId);
            $this->info('Site removed successfully.');

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('Failed to remove site: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
