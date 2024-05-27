<?php


namespace tests\CptBurke\Application\SymfonyMessengerBundle\DependencyInjection;


use CptBurke\Application\Command\CommandBus;
use CptBurke\Application\Command\CommandHandler;
use CptBurke\Application\Domain\DomainEventBus;
use CptBurke\Application\Domain\DomainEventSubscriber;
use CptBurke\Application\Event\ApplicationEventBus;
use CptBurke\Application\Event\ApplicationEventSubscriber;
use CptBurke\Application\Query\QueryBus;
use CptBurke\Application\Query\QueryHandler;
use CptBurke\Application\SymfonyMessengerBundle\DependencyInjection\MessengerApplicationExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;


class MessengerApplicationExtensionTest extends TestCase
{

    public function testServicesAreConfigured(): void
    {
        $sut = new MessengerApplicationExtension();

        $container = new ContainerBuilder();
        $sut->load([], $container);

        $this->assertTrue($container->hasDefinition('messenger_application.query.bus'));
        $this->assertTrue($container->hasDefinition('messenger_application.command.bus'));
        $this->assertTrue($container->hasDefinition('messenger_application.transport.senders'));
        $this->assertTrue($container->hasDefinition('messenger_application.domain_event.bus'));
        $this->assertTrue($container->hasDefinition('messenger_application.application_event.bus'));

        $this->assertEquals('messenger_application.query.bus', (string)$container->getAlias(QueryBus::class));
        $this->assertEquals('messenger_application.command.bus', (string)$container->getAlias(CommandBus::class));
        $this->assertEquals(SendMessageMiddleware::class, $container->getDefinition('messenger_application.transport.senders')->getClass());
        $this->assertEquals('messenger_application.domain_event.bus', (string)$container->getAlias(DomainEventBus::class));
        $this->assertEquals('messenger_application.application_event.bus', (string)$container->getAlias(ApplicationEventBus::class));
    }

    public function testInstancesAreTagged(): void
    {
        $sut = new MessengerApplicationExtension();

        $container = new ContainerBuilder();
        $sut->load([], $container);

        $instances = $container->getAutoconfiguredInstanceof();
        $this->assertTrue($instances[QueryHandler::class]->hasTag('messenger_application.query.handler'));
        $this->assertTrue($instances[CommandHandler::class]->hasTag('messenger_application.command.handler'));
        $this->assertTrue($instances[ApplicationEventSubscriber::class]
            ->hasTag('messenger_application.application_event.subscriber'));
        $this->assertTrue($instances[DomainEventSubscriber::class]
            ->hasTag('messenger_application.domain_event.subscriber'));
    }

    public function testQueryBusConfig(): void
    {
        $sut = new MessengerApplicationExtension();

        $container = new ContainerBuilder();
        $sut->load(['messenger_application' => [
            'query_bus' => [
                'before_handle' => ['test_before_middleware'],
                'after_handle' => ['test_after_middleware'],
            ]
        ]], $container);
        $this->assertInstanceOf(TaggedIteratorArgument::class, $container->getDefinition('messenger_application.query.internal_bus')
            ->getArgument('$callables')
        );
        $this->assertContains('test_before_middleware',
            array_map(
                static fn (Reference $r) => (string)$r,
                $container
                    ->getDefinition('messenger_application.query.internal_bus')
                    ->getArgument('$before_handle'))
        );
        $this->assertContains('test_after_middleware',
            array_map(
                static fn (Reference $r) => (string)$r,
                $container
                    ->getDefinition('messenger_application.query.internal_bus')
                    ->getArgument('$after_handle'))
        );
    }

    public function testDomainEventBusConfig(): void
    {
        $sut = new MessengerApplicationExtension();

        $container = new ContainerBuilder();
        $sut->load(['messenger_application' => [
            'domain_event_bus' => [
                'before_handle' => ['test_before_middleware'],
                'after_handle' => ['test_after_middleware'],
            ]
        ]], $container);
        $this->assertInstanceOf(TaggedIteratorArgument::class, $container->getDefinition('messenger_application.domain_event.internal_bus')
            ->getArgument('$subscribers')
        );
        $this->assertContains('test_before_middleware',
            array_map(
                static fn (Reference $r) => (string)$r,
                $container
                    ->getDefinition('messenger_application.domain_event.internal_bus')
                    ->getArgument('$before_handle'))
        );
        $this->assertContains('test_after_middleware',
            array_map(
                static fn (Reference $r) => (string)$r,
                $container
                    ->getDefinition('messenger_application.domain_event.internal_bus')
                    ->getArgument('$after_handle'))
        );
    }


    public function testTransportConfig(): void
    {
        $sut = new MessengerApplicationExtension();

        $container = new ContainerBuilder();
        $sut->load(['messenger_application' => [
            'transport' => [
                'senders' => [
                    'CommandClass' => ['DbTransport'],
                ],
            ]
        ]], $container);
        $this->assertEquals(
            [ 'CommandClass' => ['DbTransport']],
            $container
                ->getDefinition('messenger_application.transport.senders')
                ->getArgument('$locators')
        );
    }
}
