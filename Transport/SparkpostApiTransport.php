<?php

namespace Symfony\Component\Mailer\Bridge\Sparkpost\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractApiTransport;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SparkpostApiTransport extends AbstractApiTransport
{
    private const HOST = 'api.sparkpost.com';

    private string $key;

    public function __construct(string $key, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        $this->key = $key;

        parent::__construct($client, $dispatcher, $logger);
    }

    public function __toString(): string
    {
        return sprintf('sparkpost+api://%s', $this->getEndpoint());
    }

    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        $response = $this->client->request('POST', 'https://'.$this->getEndpoint().'/api/v1/transmissions', [
            'json' => $this->getPayload($email, $envelope),
            'auth_bearer' => $this->key,
        ]);

        $result = $response->toArray(false);
        if (200 !== $response->getStatusCode()) {
            throw new HttpTransportException(
                sprintf(
                    'Unable to send email: message - %s, description - %s, code - %d',
                    $result['errors'][0]['message'],
                    $result['errors'][0]['description'],
                    $result['errors'][0]['code']
                ),
                $response
            );
        }

        return $response;
    }

    protected function getRecipients(Email $email, Envelope $envelope): array
    {
        $recipients = [];
        foreach ($envelope->getRecipients() as $recipient) {
            $recipientPayload = [
                'address' => [
                    'email' => $recipient->getAddress(),
                ],
            ];

            if ('' !== $recipient->getName()) {
                $recipientPayload['address']['name'] = $recipient->getName();
            }

            $recipients[] = $recipientPayload;
        }

        return $recipients;
    }

    private function getEndpoint(): ?string
    {
        return ($this->host ?: self::HOST).($this->port ? ':'.$this->port : '');
    }

    private function getPayload(Email $email, Envelope $envelope): array
    {
        $payload = [
            'recipients' => $this->getRecipients($email, $envelope),
            'content' => [
                'from' => [
                    'email' => $envelope->getSender()->getAddress(),
                ],
                'subject' => $email->getSubject(),
                'html' => $email->getHtmlBody(),
                'attachments' => $this->getAttachments($email)
            ],
        ];

        if ('' !== $envelope->getSender()->getName()) {
            $payload['content']['from']['name'] = $envelope->getSender()->getName();
        }

        return $payload;
    }

    private function getAttachments(Email $email): array
    {
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();
            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');

            $att = [
                'name' => $filename,
                'type' => $attachment->getMediaSubtype(),
                'data' => str_replace("\r\n", '', $attachment->bodyToString()),
            ];

            $attachments[] = $att;
        }

        return $attachments;
    }
}
