<?php

namespace App\Jobs;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\ApiException;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateSmtpEmail;
use Brevo\Client\Model\SendSmtpEmail;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendMail implements ShouldQueue
{
    use Queueable;

    private string $to_email;
    private string $to_name;
    private int $template_id;
    private array $data;

    public function __construct(string $to_email, string $to_name, int $template_id, array $data = [])
    {
        $this->to_email = $to_email;
        $this->to_name = $to_name;
        $this->template_id = $template_id;
        $this->data = $data;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', env("BREVO_API_KEY"));
        $apiInstance = new TransactionalEmailsApi(new Client(), $config);
        $sendSmtpEmail = new SendSmtpEmail();
        $sendSmtpEmail['to'] = array(array('email' => $this->to_email, 'name' => $this->to_name));
        $sendSmtpEmail['templateId'] = $this->template_id;
        $this->data['to_name'] = $this->to_name;
        $sendSmtpEmail['params'] = $this->data;
        try {
            $response = $apiInstance->sendTransacEmail($sendSmtpEmail);
            if (!($response instanceof CreateSmtpEmail)) {
                throw new Exception("Bad Response type");
            }
            if (!$response->valid()) {
                throw new Exception("Not a valid response received");
            }
        } catch (Throwable $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
