<?php

namespace CptBurke\Application\SymfonyMessengerBundle\Factory;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;

class SenderMiddlewareFactory
{
    private ContainerInterface $container;

    private bool $allowNoSenders;

    public function __construct(ContainerInterface $container, bool $allowNoSenders = true)
    {
        $this->container = $container;
        $this->allowNoSenders = $allowNoSenders;
    }

    /**
     * @param string[][] $locators
     * @return SendMessageMiddleware
     */
    public function createFromSenders(array $locators): SendMessageMiddleware
    {
        return new SendMessageMiddleware(
            new SendersLocator($locators, $this->container),
            null,
            $this->allowNoSenders,
        );
    }
}
