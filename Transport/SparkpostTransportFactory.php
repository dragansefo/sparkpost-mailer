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
        if ('sparkpost+api' === $dsn->getScheme()) {
            return new SparkpostApiTransport($dsn->getUser());
        }

        throw new UnsupportedSchemeException($dsn, 'sparkpost', $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['sparkpost+api'];
    }
}
