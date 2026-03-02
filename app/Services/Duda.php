<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Duda
{
    const array SITE_FULL_PERMISSIONS = [
        'STATS_TAB',
        'EDIT',
        'ADD_FLEX',
        'E_COMMERCE',
        'PUBLISH',
        'REPUBLISH',
        'DEV_MODE',
        'INSITE',
        'SEO',
        'BACKUPS',
        'CUSTOM_DOMAIN',
        'RESET',
        'BLOG',
        'PUSH_NOTIFICATIONS',
        'LIMITED_EDITING',
        'SITE_COMMENTS',
        'CONTENT_LIBRARY',
        'EDIT_CONNECTED_DATA',
        'MANAGE_CONNECTED_DATA',
        'USE_APP',
        'BOOKING_ADMIN',
        'CLIENT_MANAGE_FREE_APPS',
        "AI_ASSISTANT",
        "CONTENT_LIBRARY_EXTERNAL_DATA_SYNC",
        "SEO_OVERVIEW"
    ];

    /**
     * Creates a Duda user, this method only creates the account on the duda end it does not record it against the user
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @return string
     * @throws Exception
     */
    public static function createDudaAccount(string $email, string $first_name, string $last_name): string
    {
        $duda_username = 'avw_' . substr(md5(microtime(true)), 0, 12);
        do {
            $usernameQuery = DB::table('user')->where('duda_username', $duda_username);
            if (Schema::hasColumn('user', 'deleted')) {
                $usernameQuery->where('deleted', false);
            }
            $username_search = $usernameQuery->first('id');
            if ($username_search !== null) {
                $duda_username = 'avw_' . substr(md5(microtime(true)), 0, 12);
            }
        } while ($username_search !== null);
        $payload = [
            'account_name' => $duda_username,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'account_type' => 'CUSTOMER'
        ];
        self::makeRequest('/api/accounts/create', 'POST', $payload, null, 204);
        return $duda_username;
    }

    /**
     * Returns a list of available templates from Duda API.
     * Filters for ADVANCED-2.0 editor only.
     *
     * @return array<int, array> Associative array keyed by template_id
     * @throws Exception
     */
    public static function getAvailableTemplates(): array
    {
        $response = self::makeRequest('/api/sites/multiscreen/templates', 'GET', null, null);
        $items = $response['data'] ?? [];
        if (!is_array($items)) {
            return [];
        }
        $data = [];
        foreach ($items as $item) {
            if (($item['editor'] ?? '') === 'ADVANCED-2.0') {
                $templateId = $item['template_id'] ?? null;
                if ($templateId !== null && $templateId !== '') {
                    $data[(int) $templateId] = $item;
                }
            }
        }
        return $data;
    }

    /**
     * Creates a website by use of a template, there is no AI technology with this option
     * @param int $user_id
     * @param int|null $team_id
     * @param int $template_id
     * @param string $site_name
     * @param string $site_description
     * @return string
     * @throws Exception
     */
    public static function createWebsiteFromTemplate(int $user_id, ?int $team_id, int $template_id, string $site_name, string $site_description): string
    {
        $userQuery = DB::table('user')->where('id', $user_id);
        if (Schema::hasColumn('user', 'deleted')) {
            $userQuery->where('deleted', false);
        }
        $user = $userQuery->first('id');
        if ($user === null) {
            throw new Exception('User does not exist');
        }
        $site_create_response = self::makeRequest('/api/sites/multiscreen/create', 'POST', ['template_id' => $template_id], null);
        if (!array_key_exists('site_name', $site_create_response['data'])) {
            throw new Exception('Site name is missing');
        }
        $site_id = $site_create_response['data']['site_name'] ?? '';
        if ($site_id == '') {
            throw new Exception('Site name is missing');
        }
        DB::table('website')->insert([
            'site_name' => $site_name,
            'site_description' => $site_description,
            'ecommerce_data' => '{}',
            'domain' => '',
            'duda_id' => $site_id,
            'created_at' => date('Y-m-d H:i:s'),
            'accessed_at' => date('Y-m-d H:i:s'),
            'published' => false,
            'published_at' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'team_id' => $team_id,
            'image_updated_at' => date('Y-m-d H:i:s'),
        ]);
        self::copyTemplateMediaToWebsite($template_id, $site_id);
        return $site_id;
    }

    /**
     * Copy template media (o_* and c_* files/dirs) from templates to websites.
     * Creates public/media and public/media/websites if they do not exist.
     */
    private static function copyTemplateMediaToWebsite(int $templateId, string $siteId): void
    {
        $mediaPath = public_path('media');
        $templatesPath = $mediaPath . DIRECTORY_SEPARATOR . 'templates';
        $websitesPath = $mediaPath . DIRECTORY_SEPARATOR . 'websites';

        if (! File::isDirectory($mediaPath)) {
            File::makeDirectory($mediaPath, 0755, true);
        }
        if (! File::isDirectory($websitesPath)) {
            File::makeDirectory($websitesPath, 0755, true);
        }

        $oPattern = $templatesPath . DIRECTORY_SEPARATOR . 'o_' . $templateId . '.*';
        $cPattern = $templatesPath . DIRECTORY_SEPARATOR . 'c_' . $templateId . '.*';

        foreach (glob($oPattern) ?: [] as $src) {
            if (File::isFile($src)) {
                $ext = substr($src, strrpos($src, '.'));
                File::copy($src, $websitesPath . DIRECTORY_SEPARATOR . 'o_' . $siteId . $ext);
            } elseif (File::isDirectory($src)) {
                File::copyDirectory($src, $websitesPath . DIRECTORY_SEPARATOR . 'o_' . $siteId);
            }
        }

        foreach (glob($cPattern) ?: [] as $src) {
            if (File::isFile($src)) {
                $ext = substr($src, strrpos($src, '.'));
                File::copy($src, $websitesPath . DIRECTORY_SEPARATOR . 'c_' . $siteId . $ext);
            } elseif (File::isDirectory($src)) {
                File::copyDirectory($src, $websitesPath . DIRECTORY_SEPARATOR . 'c_' . $siteId);
            }
        }
    }

    public static function getWebsiteInfo(string $site_id): array
    {
        $response = self::makeRequest('/api/sites/multiscreen/' . $site_id, 'GET', null, null, 200);
        return $response['data'] ?? [];
    }





    public static function getEditorSsoLink(int $user_id, ?int $team_id, $website_id): array
    {
        if (!CurrentUser::isLoggedIn()) {
            return ['message' => 'You must be logged in to edit websites.'];
        }
        $website = DB::table('website')->where('id', $website_id)->where('deleted', false)->first();
        if ($website === null) {
            return ['message' => 'Website not found'];
        }
        $userQuery = DB::table('user')->where('id', $user_id);
        if (Schema::hasColumn('user', 'deleted')) {
            $userQuery->where('deleted', false);
        }
        $user = $userQuery->first('id');
        if ($user === null) {
            return ['message' => 'User not found'];
        }



        return [];
    }




    public static function removeSiteFromDuda(string $site_id): void
    {
        self::makeRequest('/api/sites/multiscreen/' . $site_id, 'DELETE', null, null, 204);
        // todo: If the site is published it needs to be unpublished
        DB::table('website')->where('duda_id', $site_id)->update(['deleted' => true, 'deleted_at' => date('Y-m-d H:i:s')]);
    }







    public static function requestNewAiWebsite(int $user_id, string $name, string $category, string $description): void
    {
        $domain_prefix = Str::slug($name) . '-' . rand(1000, 9999);


        $json = <<<EOT
        {

            "default_domain_prefix": "{$domain_prefix}",
            "lang": "en",
            "business_data": {
                "name": "Acme Fitness",
                "category": "Gym / Fitness",
                "description": "Local gym with classes, personal training, and modern equipment.",
                "tone_of_voice": "CONVERSATIONAL"
            },
            "site_data": {
                "account_name": "avw_f4cace80ba78",
                "external_uid": "2",
                "site_seo": {
                    "title": "Acme Fitness | Your Local Gym",
                    "description": "Join Acme Fitness for classes, training, and more."
            }
        },
            "theme": {
                "colors": [
                    { "id": "color_1", "label": "Color 1", "value": "#dc2626" }
                ]
            }
        }
        EOT;


        $payload = json_decode($json, true);




        /*
        $payload = [
            'default_domain_prefix' => $domain_prefix,
            'lang' => 'en',
            'business_data' => [
                'name' => "theplushstore",
                'category' => "Catalogue Site",
                'description' => "This is a catalogue site displaying the many different collectable plushys that can be purchased",
                'tone_of_voice' => 'CONVERSATIONAL'
            ],
            "theme" => [
                'colors' => [
                    [
                        "id" => 'color_1',
                        "label" => "Color 1",
                        "value" => "#ffffff"
                    ]
                ]
            ]

        ];

        */

        $response = self::makeRequest('/async-tasks/generate-site-with-ai', 'POST', $payload);

        var_export($response);;

    }


    public static function makeRequest(string $uri, string $method = 'GET', ?array $payload = null, ?array $query = null, ?int $need_response_code = null): array
    {
        $duda_user = strval(getenv('DUDA_API_USER'));
        $duda_pass = strval(getenv('DUDA_API_PASS'));
        if ($duda_user == '' || $duda_pass == '') {
            throw new Exception("The Web builder API is not configured correctly.");
        }

        $url = 'https://api.duda.co/' . trim($uri, '/');

        $query = is_array($query) ? $query : [];
        if (sizeof($query) > 0) {
            $url .= '?' . http_build_query($query);
        }

        $headers = [
            'Authorization: Basic ' . base64_encode("{$duda_user}:{$duda_pass}"),
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, 0);
        if ($payload !== null) {
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $log_duda_api = boolval(getenv('DUDA_API_LOG'));
        $log_id = 0;
        if ($log_duda_api) {
            $log_id = DB::table('api_log')->insertGetId([
                'api_name' => 'duda',
                'timestamp' => date('Y-m-d H:i:s'),
                'url' => $url,
                'method' => $method,
                'headers' => ($headers === null) ? null : json_encode($headers, JSON_PRETTY_PRINT),
                'user_id' => 0,
                'payload_text' => ($payload === null) ? null : json_encode($payload, JSON_PRETTY_PRINT),
                'response_text' => null,
                'response_code' => null,
                'response_time' => null,
                'exception' => null,
                'session' => null
            ]);
        }

        try {

            $json = strval(curl_exec($ch));
            $code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
            $time = intval( floatval(curl_getinfo($ch, CURLINFO_TOTAL_TIME)) * 1000);

            if (intval($code / 100) != 2) {
                throw new Exception("Curl returned error code $code.");
            }
            if ($need_response_code !== null && $code != $need_response_code) {
                throw new Exception("The response code was not as required");
            }

            $data = json_decode(($json == '') ? '[]' : $json, true);
            if (!is_array($data)) {
                throw new Exception("The request to the editor api returned an invalid response.");
            }

            if ($log_duda_api) {
                DB::table('api_log')->where('id', $log_id)->update([
                    'response_text' => (!array($data)) ? $json : json_encode($data, JSON_PRETTY_PRINT),
                    'response_code' => $code,
                    'response_time' => $time,
                ]);
            }

            return [
                'code' => $code,
                'data' => $data,
            ];

        } catch (Exception $exception) {
            if ($log_duda_api) {
                DB::table('api_log')->where('id', $log_id)->update([
                    'response_text' => $json,
                    'response_code' => $code,
                    'response_time' => $time,
                    'exception' => $exception->getMessage(),
                ]);
            }
            throw $exception;
        }
    }

}
