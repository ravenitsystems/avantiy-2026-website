<?php

namespace App\Http\Controllers\Api\Website;

use App\Duda\Partner;
use App\Http\Controllers\ApiBase;
use App\Services\CurrentUser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Creates a website from a template.
 *
 * Expected request: site_name, site_description, template_id (the external Duda template_id), team_id (optional; current team context from the frontend, or falls back to session active_team_id).
 *
 * On success return: ['redirect_url' => 'https://...']
 * On error return: ['message' => 'Error description'] and call setFail() / setResponseCode()
 */
class Createfromtemplate extends ApiBase
{
    public function handle(Request $request): array
    {
        if (!CurrentUser::isLoggedIn()) {
            throw new Exception("User must be logged in to perform this action", 500);
        }
        $templateId = (int) $request->input('template_id');
        $siteName = trim((string) $request->input('site_name', ''));
        $siteDescription = trim((string) $request->input('site_description', ''));
        $userId = CurrentUser::getUserId();
        $teamId = $request->input('team_id');
        if ($teamId !== null && $teamId !== '') {
            $teamId = (int) $teamId;
        } else {
            $teamId = $request->session()->get('active_team_id');
            if ($teamId !== null) {
                $teamId = (int) $teamId;
            } else {
                $teamId = null;
            }
        }

        $duda_username = CurrentUser::getDudaUsername();

        $duda = new Partner();
        $site_id = $duda->createWebsiteFromTemplate($templateId);
        $duda->grantWebsitePermissions($duda_username, $site_id, 'owner');

        //$website_data

        DB::table('website')->insert([
            'site_name' => $siteName,
            'site_description' => $siteDescription,
            'ecommerce_data' => '',
            'domain' => '',
            'duda_id' => $site_id,
            'created_at' => date('Y-m-d H:i:s'),
            'accessed_at' => date("Y-m-d H:i:s"),
            'published' => false,
            'published_at' => null,
            'user_id' => $userId,
            'deleted' => false,
            'deleted_at' => null,
            'team_id' => $teamId,
            'payment_until' =>  null,
            'payment_amount' => null,
            'payment_term' => null,
            'google_analytics_measurement_id' => null,
            'google_analytics_property_id' => null,
            'google_tag_manager_container_id' => null,
            'image_updated_at' => null
        ]);


        $editor_link = $duda->getWebsiteEditorLink($duda_username, $site_id);
        if ($editor_link === null) {
            throw new Exception("Could not create website editor link", 500);
        }


        return ['link' => $editor_link];
    }
}
