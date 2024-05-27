# Symfony Messenger Application Bundle
bundle to use symfony-messenger-application (query, command, event, ...) in your symfony application

## Installation
`composer require cptburke/symfony-messenger-application-bundle`

## Configuration

This bundle tags classes that implement one of these interfaces via autoconfiguration:
- `CptBurke\Application\Query\QueryHandler` gets tagged with `messenger_application.query.handler`
- `CptBurke\Application\Command\CommandHandler` gets tagged with `messenger_application.command.handler`
- `CptBurke\Application\Domain\DomainEventSubscriber` gets tagged with `messenger_application.domain_event.subscriber`
- `CptBurke\Application\Event\ApplicationEventSubscriber` gets tagged with `messenger_application.application_event.subscriber`

It also pre-configures several buses:
- `messenger_application.query.bus` Query bus with autoconfigured mapping for handlers that implement `CptBurke\Application\Query\QueryHandler`
- `messenger_application.domain_event.bus` Domain event bus with autoconfigured mapping for subscribers that implement `CptBurke\Application\Domain\DomainEventSubscriber`
- `messenger_application.command.bus` Command bus that takes an `Symfony\Messenger\MessageBusInterface` which can be configured through `services.yaml`, `messenger.yaml`
- `messenger_application.application_event.bus` Application event bus that takes an `Symfony\Messenger\MessageBusInterface` which can be configured through `services.yaml`, `messenger.yaml`
 
To use async transport for command or application event buses, you can leverage the `SendMessageMiddleware` configured by the transport config
- `messenger_application.transport.senders` takes a map of message classes to a list of transports

### `config/packages/messenger_application.yaml`

The minimal configuration contains services for the command bus and the application event bus (if you want to use them in your application).


### `config/services.yaml`

```yaml
    #...
    
    command.handler_middleware:
        factory: [CptBurke\Application\SymfonyMessengerBundle\Factory\HandlerMiddlewareStackFactory, createCallables]
        arguments:
          - !tagged_iterator messenger_application.command.handler
    
    #...
```

#### `config/packages/messenger.yaml`
```yaml

    buses:
    #...
      command.bus:
        default_middleware: false
        middleware:
          - doctrine_transaction
          - messenger_application.transport.senders
          - command.handler_middleware
```

```yaml
messenger_application:

    # service id of the configured symfony message bus
    command_bus: 'command.bus'
    
    # service id of the configured symfony message bus
    application_event_bus: 'app.async_bus'

    query_bus:
        # middleware before query gets handled
        before_handle:
            - Acme\Middleware\SomeMiddleware
        # middleware after query was handled
        after_handle: []
            
    domain_event_bus:
        before_handle:
            - 'app.middleware.some_middleware'
        after_handle:
            - Acme\Middleware\LoggerMiddleware

    # SomeCommand would be sent to DoctrineTransport service
    transport:
        senders:
            Acme\Command\SomeCommand: ['DoctrineTransport']
```

## Usage
Example
```php
<?php


use CptBurke\Application\Query\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class SampleController extends AbstractController
{

    public function sampleAction(QueryBus $bus)
    {
        $data = $bus->ask(new GetDataQuery());
        // or
        $data = $this->get(QueryBus::class)->ask(new GetDataQuery());
        // or
        $data = $this->get('messenger_application.query.bus')->ask(new GetDataQuery());
        
        return $this->json($data);
    }
