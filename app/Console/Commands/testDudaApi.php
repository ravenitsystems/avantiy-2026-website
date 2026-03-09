<?php

namespace App\Console\Commands;

use App\Duda\BaseApi;
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
        $duda = new BaseApi(getenv("DUDA_API_USER"), getenv("DUDA_API_PASS"));

        $site = 'e4a01013';
        //$uri = $duda->buildUri('/api/accounts/{user}/permissions/{site}', ['user'=>'abcdefg', 'site'=>'hijklmo'], ['t1'=>5, 'name'=>'nick']);

        //$duda->get('/api/sites/multiscreen/plans');

        //$duda->get('/api/sites/multiscreen', [], ['limit'=>5]);

        //$r = $duda->post('/api/sites/multiscreen/create', ['template_id' => '1461176']);

        //$k = $duda->post('/api/sites/multiscreen/{site_name}/blog', ['description'=>'This is a test blog to see how to create one by api', 'name'=>'api_blog', 'title'=>'The API Blog'], ['site_name' => $site]);

        $k = $duda->apiRequest('/api/sites/multiscreen/{site_name}/blog', 'GET', ['site_name' => $site]);



        var_export($k);





      //  print $uri;






    }
}
