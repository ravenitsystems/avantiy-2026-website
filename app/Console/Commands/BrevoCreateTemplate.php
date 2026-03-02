<?php

namespace App\Console\Commands;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateSmtpTemplate;
use Brevo\Client\Model\CreateSmtpTemplateSender;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class BrevoCreateTemplate extends Command
{
    protected $signature = 'brevo:create-template
                            {--name=Avantiy Default : Template name in Brevo}
                            {--tag=avantiy-default : Tag for the template}';

    protected $description = 'Create and register an Avantiy-branded email template with Brevo';

    public function handle(): int
    {
        $apiKey = config('services.brevo.key');
        if (empty($apiKey)) {
            $this->error('BREVO_API_KEY is not set. Add it to your .env or config/services.php.');
            return self::FAILURE;
        }

        $fromEmail = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        $fromName = config('mail.from.name', env('MAIL_FROM_NAME', env('APP_NAME', 'Avantiy')));

        $sender = new CreateSmtpTemplateSender([
            'name' => $fromName,
            'email' => $fromEmail,
        ]);

        $template = new CreateSmtpTemplate([
            'tag' => $this->option('tag'),
            'sender' => $sender,
            'templateName' => $this->option('name'),
            'subject' => 'Message from Avantiy',
            'htmlContent' => $this->getTemplateHtml(),
            'toField' => '{{ params.to_name }}',
            'isActive' => true,
        ]);

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $api = new TransactionalEmailsApi(new Client(), $config);

        try {
            $response = $api->createSmtpTemplate($template);
            $id = $response->getId();
            $this->info("Template created successfully!");
            $this->info("Template ID: {$id}");
            $this->newLine();
            $this->line('Use this template with SendMail::dispatch($email, $name, ' . $id . ', $params)');
            $this->line('Supported params: first_name, token, message, to_name');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to create template: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function getTemplateHtml(): string
    {
        $ctaColor = 'rgb(139, 61, 255)';
        $bgDark = '#0f0f0f';
        $bgCard = '#1a1a1a';
        $textHeading = '#e2e8f0';
        $textBody = 'rgb(100, 116, 139)';
        $border = '#334155';
        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avantiy</title>
</head>
<body style="margin:0; padding:0; background-color:{$bgDark}; font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:{$bgDark};">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px; width:100%;">
                    <tr>
                        <td align="center" style="padding: 0 0 32px 0;">
                            <span style="font-size: 24px; font-weight: 600; color:{$ctaColor};">Avantiy</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:{$bgCard}; border: 1px solid {$border}; border-radius: 12px; padding: 40px; box-sizing: border-box;">
                            <h1 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 600; color:{$textHeading};">
                                Hello {{ params.first_name }},
                            </h1>
                            <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 1.6; color:{$textBody};">
                                {{ params.message }}
                            </p>
                            <div style="margin: 0 0 24px 0; padding: 20px; background-color: rgba(139, 61, 255, 0.1); border-radius: 8px; text-align: center;">
                                <span style="font-size: 28px; font-weight: 600; letter-spacing: 4px; color:{$ctaColor}; font-family: monospace;">{{ params.token }}</span>
                            </div>
                            <p style="margin: 0 0 24px 0; font-size: 13px; color:{$textBody};">
                                This code will expire in 15 minutes. If you didn't request this, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 24px 0 0 0;">
                            <p style="margin: 0; font-size: 12px; color:{$textBody};">
                                &copy; {$year} Avantiy. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
