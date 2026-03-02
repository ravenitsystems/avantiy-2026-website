<?php

namespace App\Console\Commands;

use App\Services\Duda;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class TestAi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-ai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws Exception
     * @throws GuzzleException
     */
    public function handle(): int
    {

        var_export(Duda::getEditorSsoLink(2, 1, 2));

        //var_export(Duda::requestNewAiWebsite(1, "My Plush Store", "Cataloguge","An online shop selling plusheys for the sonic the headgehog video game"));

        //var_export(Duda::createDudaAccount('nicholas@ukwsc.com', 'Nicholas', 'DevTest'));


        return 0;
    }
}
