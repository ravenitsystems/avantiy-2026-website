<?php

namespace App\Jobs;

use App\Services\Duda;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Imagick;

class UpdateTemplates implements ShouldQueue
{
    use Queueable;

    private const THUMBNAIL_SCALE_WIDTH = 500;

    private const THUMBNAIL_CROP_WIDTH = 470;

    private const THUMBNAIL_CROP_HEIGHT = 310;

    private const THUMBNAIL_CROP_X = 15;

    private const THUMBNAIL_CROP_Y = 15;

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        if (! extension_loaded('imagick')) {
            throw new Exception('The Imagick PHP extension is required for the UpdateTemplates job.');
        }

        $templatesDir = public_path('media/templates');
        $this->ensureDirectoryExists($templatesDir);

        $dudaTemplates = Duda::getAvailableTemplates();
        $incomingTemplateIds = array_keys($dudaTemplates);

        foreach ($dudaTemplates as $dudaTemplate) {
            $externalTemplateId = (int) ($dudaTemplate['template_id'] ?? 0);
            if ($externalTemplateId <= 0) {
                continue;
            }

            $existing = DB::table('template')->where('template_id', $externalTemplateId)->first();

            $enabled = $existing !== null ? (bool) $existing->enabled : true;
            $orderOffsetValue = $existing !== null ? (int) $existing->order_offset : 1000;
            $frontPage = $existing !== null ? (bool) $existing->front_page : false;

            $props = $dudaTemplate['template_properties'] ?? [];
            $hasStore = (bool) ($props['has_store'] ?? false);
            $hasBlog = (bool) ($props['has_blog'] ?? false);
            $hasBooking = (bool) ($props['has_booking'] ?? false);
            $aiEnabled = (bool) ($props['ai_enabled'] ?? false);
            $templateType = (string) ($props['type'] ?? 'duda');
            $templateType = strlen($templateType) > 16 ? substr($templateType, 0, 16) : $templateType;
            $visibility = 'public';

            $templateData = [
                'template_id' => $externalTemplateId,
                'preview_url' => $this->truncate($dudaTemplate['preview_url'] ?? '', 256),
                'template_type' => $templateType,
                'visibility' => $visibility,
                'has_store' => $hasStore,
                'has_blog' => $hasBlog,
                'has_booking' => $hasBooking,
                'ai_enabled' => $aiEnabled,
                'deleted' => false,
            ];

            if ($existing !== null) {
                // Do not override admin-editable fields: enabled, order_offset, front_page, name
                DB::table('template')->where('id', $existing->id)->update($templateData);
                $templateDbId = (int) $existing->id;
            } else {
                $templateData['date_created'] = now();
                $templateData['enabled'] = $enabled;
                $templateData['order_offset'] = $orderOffsetValue;
                $templateData['front_page'] = $frontPage;
                $templateData['name'] = $this->truncate($dudaTemplate['template_name'] ?? 'Untitled', 128);
                $templateDbId = (int) DB::table('template')->insertGetId($templateData);
            }

            $this->syncTemplateCategories($templateDbId, $dudaTemplate['categories'] ?? []);

            $thumbnailUrl = $dudaTemplate['desktop_thumbnail_url'] ?? null;
            if (! empty($thumbnailUrl)) {
                $shouldDownload = $this->shouldDownloadThumbnail($existing);
                if ($shouldDownload) {
                    $ext = $this->downloadAndProcessThumbnail($thumbnailUrl, $externalTemplateId, $templatesDir);
                    if ($ext !== null) {
                        DB::table('template')->where('id', $templateDbId)->update([
                            'thumbnail_ext' => $ext,
                            'image_last_refreshed_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Mark as deleted any templates in DB whose template_id is not in the incoming stream
        // Only when we received templates (skip if Duda returned empty, to avoid mass-deleting on API errors)
        if (! empty($incomingTemplateIds)) {
            DB::table('template')
                ->whereNotIn('template_id', $incomingTemplateIds)
                ->update(['deleted' => true]);
        }
    }

    private const THUMBNAIL_REFRESH_DAYS = 7;

    private function shouldDownloadThumbnail(?object $existing): bool
    {
        if ($existing !== null && ! empty($existing->deleted)) {
            return false;
        }
        if ($existing === null) {
            return true;
        }
        $lastRefreshed = $existing->image_last_refreshed_at ?? null;
        if ($lastRefreshed === null) {
            return true;
        }
        $cutoff = now()->subDays(self::THUMBNAIL_REFRESH_DAYS);
        $refreshedAt = \Carbon\Carbon::parse($lastRefreshed);

        return $refreshedAt->lt($cutoff);
    }

    private function ensureDirectoryExists(string $dir): void
    {
        $mediaDir = public_path('media');
        if (! is_dir($mediaDir)) {
            mkdir($mediaDir, 0755, true);
        }
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function syncTemplateCategories(int $templateDbId, array $categoryNames): void
    {
        DB::table('template_category_link')->where('template_id', $templateDbId)->delete();

        foreach ($categoryNames as $categoryName) {
            $name = $this->truncate(is_string($categoryName) ? $categoryName : '', 128);
            if ($name === '') {
                continue;
            }

            $category = DB::table('template_category')->where('name', $name)->first();
            if ($category === null) {
                $categoryId = DB::table('template_category')->insertGetId(['name' => $name]);
            } else {
                $categoryId = (int) $category->id;
            }

            DB::table('template_category_link')->insert([
                'template_id' => $templateDbId,
                'template_category_id' => $categoryId,
            ]);
        }
    }

    /**
     * @return string|null The file extension (e.g. '.png') on success, null on failure
     * @throws Exception
     */
    private function downloadAndProcessThumbnail(string $url, int $templateId, string $templatesDir): ?string
    {
        $response = Http::timeout(30)->get($url);
        if (! $response->successful()) {
            return null;
        }

        $body = $response->body();
        if ($body === '') {
            return null;
        }

        $ext = $this->getExtensionFromUrl($url);
        $originalPath = $templatesDir . DIRECTORY_SEPARATOR . 'o_' . $templateId . $ext;

        if (file_put_contents($originalPath, $body) === false) {
            return null;
        }

        try {
            $imagick = new Imagick($originalPath);
            $imagick->scaleImage(self::THUMBNAIL_SCALE_WIDTH, 0);
            $imagick->cropImage(
                self::THUMBNAIL_CROP_WIDTH,
                self::THUMBNAIL_CROP_HEIGHT,
                self::THUMBNAIL_CROP_X,
                self::THUMBNAIL_CROP_Y
            );
            $croppedPath = $templatesDir . DIRECTORY_SEPARATOR . 'c_' . $templateId . $ext;
            $imagick->writeImage($croppedPath);
            $imagick->clear();
            $imagick->destroy();

            $imagick = new Imagick($croppedPath);
            $imagick->cropImage(
                self::THUMBNAIL_CROP_WIDTH,
                self::THUMBNAIL_CROP_HEIGHT * 0.7,
                0,
                0
            );
            $thinPath = $templatesDir . DIRECTORY_SEPARATOR . 't_' . $templateId . $ext;
            $imagick->writeImage($thinPath);
            $imagick->clear();
            $imagick->destroy();

            return $ext;
        } catch (Exception $e) {
            @unlink($originalPath);
            throw $e;
        }
    }

    private function getExtensionFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (is_string($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                return $ext === 'jpeg' ? '.jpg' : '.' . $ext;
            }
        }

        return '.png';
    }

    private function truncate(string $value, int $maxLength): string
    {
        return strlen($value) > $maxLength ? substr($value, 0, $maxLength) : $value;
    }
}
