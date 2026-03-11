<?php

namespace App\Console\Commands;

use App\Duda\Base;
use Illuminate\Console\Command;

class EpicurusFlashManifest extends Command
{
    protected $signature = 'epicurus:flash-manifest';

    protected $description = 'Sets the manifest block for the Epicurus application';

    public function handle()
    {
        $manifest = <<<EOL
        {
            "uuid": "12c6c1c0-b96d-46fb-a7e8-643f9c0769ae",
            "public_key": "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAi\/9pPEs\/V4\/HTWA8sXwdXGXGbiopwzAdVN6phV\/JwITaV5ya3wmqq+20Clki5IAXoxvVVCvBoUskjdZg4ka7WCF\/7fRcXDUjlwtbbE9dUA98ctjAB8WDtaXFVndEihWBY8\/BZbFQ8rqqJNmuFwOYwmgSkjVfw7myDGhO7OWU7CQdyAKKydUrUAfHO2c5y9YTZmC3K2VYBZrRuk6oKS\/jAidjZ+iBoV0mNM8QDwga7RvDier3kO4dVW0rYJom5WSUMHjUOELxj6IO1foJFR077VWWC8g\/BJZsqieWsP6PHUUZAqpmSIh3kWtO88M3zvZJO2QSy3lE79p6xxWI1zMtTQIDAQAB",
            "installation_endpoint": "https:\/\/devl1.avantiy.com\/api\/epicurus\/notifyinstall",
            "uninstallation_endpoint": "https:\/\/devl1.avantiy.com\/api\/epicurus\/notifyuninstall",
            "updowngrade_installation_endpoint": "https:\/\/devl1.avantiy.com\/api\/epicurus\/notifychange",
            "base_sso_url": "https:\/\/devl1.avantiy.com\/api\/epicurus\/notifysso",
            "webhooks": {
                "endpoint": "https:\/\/devl1.avantiy.com\/api\/epicurus\/notifyhook",
                "events": []
            },
            "supported_locales": [
                "en"
            ],
            "app_profile": {
                "en": {
                    "app_name": "12c6c1c0-b96d-46fb-a7e8-643f9c0769ae",
                    "app_logo": "https:\/\/url.com\/imageLogo.png",
                    "app_short_description": "A short description goes here.",
                    "app_long_description": "# Headline here \\n - Markdown describing App",
                    "public_page": "https:\/\/url.com\/ourproduct",
                    "terms_of_service_page": "https:\/\/url.com\/terms_en",
                    "privacy_note_page": "https:\/\/url.com\/privacy_en",
                    "app_screenshots": [
                        {
                            "image_url": "https:\/\/something.com\/image.jpg",
                            "alt_text": "Description of image above"
                        },
                        {
                            "image_url": "https:\/\/something.com\/image2.jpg",
                            "alt_text": "Description of image above"
                        }
                    ]
                }
            },
            "wl_app_profile": {
                "en": {
                    "app_name": "12c6c1c0-b96d-46fb-a7e8-643f9c0769ae",
                    "app_logo": "https:\/\/url.com\/imageLogo.png",
                    "app_short_description": "A short description goes here.",
                    "app_long_description": "# Headline here \\n - Markdown describing App",
                    "public_page": "https:\/\/url.com\/ourproduct",
                    "terms_of_service_page": "https:\/\/url.com\/terms_en",
                    "privacy_note_page": "https:\/\/url.com\/privacy_en",
                    "app_screenshots": [
                        {
                            "image_url": "https:\/\/something.com\/image.jpg",
                            "alt_text": "Description of image above"
                        },
                        {
                            "image_url": "https:\/\/something.com\/image2.jpg",
                            "alt_text": "Description of image above"
                        }
                    ]
                }
            },
            "scopes": [
                "SITE_WIDE_HTML",
                "GET_WEBSITE",
                "GET_CONTENT_LIBRARY"
            ],
            "categories": [],
            "app_plans": [],
            "visible_to_clients": true,
            "default_plan_uuid": null,
            "window_type": "IFRAME",
            "required_fields": [],
            "is_in_beta": false
        }
        EOL;

       // $params = ['base_url'=>'https://devl1.avantiy.com'];
       // foreach($params as $key=>$value) {
      //      $manifest = str_replace('{'.$key.'}', $value, $manifest);
      //  }

        var_export(json_decode($manifest, true));



        $duda = new Base('/api/integrationhub/');

        $result = $duda->request('/application/{app_uuid}', 'POST', [], ['app_uuid'=>getenv("EPICURUS_APP_ID")], json_decode($manifest, true));

        var_export($result);



    }
}
