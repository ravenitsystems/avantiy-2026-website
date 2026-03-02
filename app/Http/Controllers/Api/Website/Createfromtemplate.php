<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\ApiBase;
use Illuminate\Http\Request;

/**
 * Creates a website from a template.
 *
 * Expected request: site_name, site_description, template_id (the external Duda template_id)
 *
 * On success return: ['redirect_url' => 'https://...']
 * On error return: ['message' => 'Error description'] and call setFail() / setResponseCode()
 */
class Createfromtemplate extends ApiBase
{
    public function handle(Request $request): array
    {
        // Example: get template_id from the request (sent as JSON in POST body)
        $templateId = (int) $request->input('template_id');
        $siteName = trim((string) $request->input('site_name', ''));
        $siteDescription = trim((string) $request->input('site_description', ''));





        // TODO: Implement - validate session, site_name, template_id; create site from template; return redirect_url or message
        return ['message' => 'Not implemented'];
    }
}
