<?php


namespace CptBurke\Application\SymfonyMessengerBundle\Factory;


use CptBurke\Application\Reflection\HandlerException;
use ReflectionException;
use Symfony\Component\Messenger\MessageBus;


class SymfonyMessageBusFactory
{

    public function __construct(
        private HandlerMiddlewareStackFactory $stackFactory
    ) {}

    /**
     * @throws ReflectionException
     * @throws HandlerException
     */
    public function createCallablesBus(iterable $callables, array $before_handle = [], array $after_handle = []): MessageBus
    {
        return new MessageBus($this->stackFactory->createCallablesStack($before_handle, $callables, $after_handle));
    }

    /**
     * @throws HandlerException
     */
    public function createSubscribersBus(iterable $subscribers, array $before_handle = [], array $after_handle = []): MessageBus
    {
        return new MessageBus($this->stackFactory->createSubscribersStack($before_handle, $subscribers, $after_handle));
    }

}
