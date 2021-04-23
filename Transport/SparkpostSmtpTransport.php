<?php

namespace Symfony\Component\Mailer\Bridge\SparkPost\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SparkpostSmtpTransport extends EsmtpTransport
{
    public function __construct(string $key, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
    {
        parent::__construct('smtp.sparkpostmail.com', 587, true, $dispatcher, $logger);

        $this->setUsername('SMTP_Injection');
        $this->setPassword($key);
    }
}
