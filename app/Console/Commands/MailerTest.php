<?php

namespace App\Console\Commands;

use App\Jobs\SendMail;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateSmtpEmail;
use Brevo\Client\Model\SendSmtpEmail;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class MailerTest extends Command
{
    protected $signature = 'app:mailer-test {template_id} {json_data}';

    protected $description = 'Command description';

    private string $to_email = 'nicholas@ukwsc.com';
    private string $to_name = 'Nicholas';
    private int $template_id = 9;

    private array $data = ['code'=>'999999'];

    public function handle()
    {




        print str_pad("", 80, '#') . PHP_EOL;
        print "# Mailer Test Script" . PHP_EOL;
        print str_pad("", 80, '#') . PHP_EOL;
        print PHP_EOL;

        if (($template_id = intval($this->argument('template_id'))) == 0) {
            throw new Exception("Template ID can not be null");
        }
        print "template ID: {$template_id}" . PHP_EOL;

        if (!is_array($data = json_decode($this->argument('json_data'), true))) {
            throw new Exception("Invalid JSON data");
        }
        print "Data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;


        SendMail::dispatch('nicholas@ukwsc.com', 'Nick', 2, ['first_name'=>'Nicholas', 'token'=>'999999']);



    }
}
