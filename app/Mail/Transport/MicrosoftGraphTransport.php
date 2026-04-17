<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

class MicrosoftGraphTransport extends AbstractTransport
{
    private string $tokenUrl;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $tenantId,
        private readonly string $fromEmail,
    ) {
        parent::__construct();
        $this->tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
    }

    protected function doSend(SentMessage $message): void
    {
        $token = $this->getAccessToken();
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $payload = $this->buildPayload($email);

        $response = Http::withToken($token)
            ->post("https://graph.microsoft.com/v1.0/users/{$this->fromEmail}/sendMail", $payload);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Microsoft Graph API error: ' . $response->status() . ' — ' . $response->body()
            );
        }
    }

    private function getAccessToken(): string
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => 'https://graph.microsoft.com/.default',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('No se pudo obtener el token OAuth2: ' . $response->body());
        }

        return $response->json('access_token');
    }

    private function buildPayload(Email $email): array
    {
        $toRecipients = array_map(
            fn(Address $addr) => ['emailAddress' => ['address' => $addr->getAddress(), 'name' => $addr->getName()]],
            $email->getTo()
        );

        $ccRecipients = array_map(
            fn(Address $addr) => ['emailAddress' => ['address' => $addr->getAddress(), 'name' => $addr->getName()]],
            $email->getCc()
        );

        $body = $email->getHtmlBody() ?? $email->getTextBody() ?? '';
        $contentType = $email->getHtmlBody() ? 'HTML' : 'Text';

        $payload = [
            'message' => [
                'subject' => $email->getSubject(),
                'body' => [
                    'contentType' => $contentType,
                    'content'     => $body,
                ],
                'toRecipients' => $toRecipients,
            ],
            'saveToSentItems' => false,
        ];

        if (!empty($ccRecipients)) {
            $payload['message']['ccRecipients'] = $ccRecipients;
        }

        return $payload;
    }

    public function __toString(): string
    {
        return 'microsoft-graph';
    }
}
