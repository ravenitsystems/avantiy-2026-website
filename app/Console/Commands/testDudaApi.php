<?php

namespace App\Console\Commands;

use App\Duda\Base;
use App\Duda\BaseApi;
use App\Duda\Epicurus;
use App\Duda\Partner;
use Illuminate\Console\Command;

class testDudaApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-duda-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $duda = new Partner();
        $r = $duda->getWebsiteDetails('8aec87182fb0483583f4897858bcf1eb');



        return;

        $site_id = '85f4678af845414fa12036f37f82b6a2';

        $duda = new Partner();

        //$r = $duda->grantWebsitePermissions('avw_ae146f11f4647e475fc0d1d0f88b91698a435262', '85f4678af845414fa12036f37f82b6a2', 'owner');

        $link = $duda->getWebsiteEditorLink('avw_ae146f11f4647e475fc0d1d0f88b91698a435262', '8c6de1a18d2f4311808fe6b192f1d98d');

        var_export($link);


        //$value =  config('duda.permissions.owner');
        //var_export($value);

        return;
        //$r = $d->createAccount('test@test.com', 'Testing', 'Testing');

       // $r = $d->createWebsiteFromTemplate(1046513);



        var_export($r);



       // $duda = new BaseApi(getenv("DUDA_API_USER"), getenv("DUDA_API_PASS"));

      //  $site = 'e4a01013';
        //$uri = $duda->buildUri('/api/accounts/{user}/permissions/{site}', ['user'=>'abcdefg', 'site'=>'hijklmo'], ['t1'=>5, 'name'=>'nick']);

        //$duda->get('/api/sites/multiscreen/plans');

        //$duda->get('/api/sites/multiscreen', [], ['limit'=>5]);

        //$r = $duda->post('/api/sites/multiscreen/create', ['template_id' => '1461176']);

        //$k = $duda->post('/api/sites/multiscreen/{site_name}/blog', ['description'=>'This is a test blog to see how to create one by api', 'name'=>'api_blog', 'title'=>'The API Blog'], ['site_name' => $site]);

     //   $k = $duda->apiRequest('/api/sites/multiscreen/{site_name}/blog', 'GET', ['site_name' => $site]);

      //  $duda = new Base('/api/integrationhub/');

      //  $duda->request('/application/{app_uuid}', 'GET', [], ['app_uuid'=>getenv("EPICURUS_APP_ID")]);


       // $k = $duda->request('/sites/multiscreen/templates', 'get', [], null, null, null);

    //    var_export($k);





      //  print $uri;


       // $app = new Epicurus();
    //    $m = $app->getManifest();

   //     var_export($m);





    }
}
