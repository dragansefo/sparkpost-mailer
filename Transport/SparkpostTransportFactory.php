<?php

namespace Symfony\Component\Mailer\Bridge\Sparkpost\Transport;

use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

class SparkpostTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();
        $key = $this->getUser($dsn);

        if ('sparkpost+api' === $scheme) {
            return new SparkpostApiTransport($key, $this->client, $this->dispatcher, $this->logger);
        }

        if ('sparkpost+smtps' === $scheme || 'sparkpost' === $scheme) {
            return new SparkpostSmtpTransport($key, $this->dispatcher, $this->logger);
        }

        throw new UnsupportedSchemeException($dsn, 'sparkpost', $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['sparkpost+api', 'sparkpost+smtps', 'sparkpost'];
    }
}
