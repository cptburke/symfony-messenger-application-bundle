<?php


declare(strict_types=1);


namespace CptBurke\Application\SymfonyMessengerBundle\Factory;


use CptBurke\Application\Reflection\CallableExtractor;
use CptBurke\Application\Reflection\HandlerException;
use Exception;
use ReflectionException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;


class HandlerMiddlewareStackFactory
{

    private CallableExtractor $extractor;

    /**
     * @param CallableExtractor $extractor
     */
    public function __construct(CallableExtractor $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * @param MiddlewareInterface[] $before_handle
     * @param iterable $callables
     * @param MiddlewareInterface[] $after_handle
     * @param bool $allow_no_handler
     * @return MiddlewareInterface[]
     * @throws HandlerException
     * @throws ReflectionException
     */
    public function createCallablesStack(
        array $before_handle,
        iterable $callables,
        array $after_handle,
        bool $allow_no_handler = false
    ): array
    {
        return [
            ...$before_handle,
            new HandleMessageMiddleware(
                new HandlersLocator(
                    $this->extractor->fromCallables($callables)
                ),
                $allow_no_handler
            ),
            ...$after_handle,
        ];
    }

    /**
     * @param MiddlewareInterface[] $before_handle
     * @param iterable $subscribers
     * @param MiddlewareInterface[] $after_handle
     * @param bool $allow_no_handler
     * @return MiddlewareInterface[]
     * @throws HandlerException
     * @throws Exception
     */
    public function createSubscribersStack(
        array $before_handle,
        iterable $subscribers,
        array $after_handle,
        bool $allow_no_handler = true
    ): array
    {
        return [
            ...$before_handle,
            new HandleMessageMiddleware(
                new HandlersLocator(
                    $this->extractor->fromSubscribers($subscribers)
                ),
                $allow_no_handler
            ),
            ...$after_handle,
        ];
    }

    /**
     * @param iterable $callables
     * @param bool $allow_no_handler
     * @return HandleMessageMiddleware
     * @throws HandlerException
     * @throws ReflectionException
     */
    public function createCallables(
        iterable $callables,
        bool $allow_no_handler = false
    ): HandleMessageMiddleware
    {
        return new HandleMessageMiddleware(
            new HandlersLocator(
                $this->extractor->fromCallables($callables)
            ),
            $allow_no_handler
        );
    }

}
