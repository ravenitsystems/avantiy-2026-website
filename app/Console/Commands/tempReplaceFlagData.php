<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class tempReplaceFlagData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:temp-replace-flag-data';

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
        $output_path = database_path('/seeders/data/CountryFlagSvgData.php');
        $flag_seeder_data_path = database_path('/seeders/data/CountryListData.php');



        $flag_path = database_path('/seeders/data/flags');
        if (!file_exists($flag_path)) {
            throw new \Exception("Could not find the flags directory");
        }

        $output = [];

        $seeder_data = require($flag_seeder_data_path);


        foreach($seeder_data as $data) {

            $code = $data['alpha_2'];

            if (file_exists($flag_path . '/' . strtolower($code) . '.svg')) {
                $svg = file_get_contents($flag_path . '/' . strtolower($code) . '.svg');
                if (is_string($svg) && strlen($svg) > 1) {
                     $output[$code] = $svg;
                }
            }
        }

        ob_start();

        print <<<EOL
        <?php
        return [
        EOL;

        foreach($output as $code=>$svg) {
            print '"' .$code .'" => \''. $svg .'\',' . PHP_EOL;
        }

        print '];';

        file_put_contents($output_path, ob_get_clean());
















    }
}
