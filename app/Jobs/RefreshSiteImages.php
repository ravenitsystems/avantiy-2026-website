<?php

namespace App\Jobs;

use App\Services\Duda;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RefreshSiteImages implements ShouldQueue
{
    use Queueable;

    private const BATCH_SIZE = 20;

    public function handle(): void
    {
        $cutoffFifteenMinsAgo = now()->subMinutes(15);

        $rows = DB::table('website')
            ->where('deleted', false)
            ->where(function ($query) {
                $query->whereNull('image_updated_at')
                    ->orWhereRaw('image_updated_at < DATE_ADD(COALESCE(accessed_at, created_at), INTERVAL 6 HOUR)');
            })
            ->where(function ($query) use ($cutoffFifteenMinsAgo) {
                $query->whereNull('image_updated_at')
                    ->orWhere('image_updated_at', '<', $cutoffFifteenMinsAgo);
            })
            ->orderByRaw('image_updated_at IS NULL DESC, image_updated_at ASC')
            ->limit(self::BATCH_SIZE)
            ->get(['duda_id']);

        foreach ($rows as $row) {
            $site_info = Duda::getWebsiteInfo($row->duda_id);
            var_export($site_info);
        }
    }
}
