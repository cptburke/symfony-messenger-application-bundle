<?php


declare(strict_types=1);


namespace CptBurke\Application\SymfonyMessengerBundle\DependencyInjection;


use CptBurke\Application\Command\CommandHandler;
use CptBurke\Application\Domain\DomainEventSubscriber;
use CptBurke\Application\Event\ApplicationEventSubscriber;
use CptBurke\Application\Query\QueryHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;


class MessengerApplicationExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->registerTags($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        if (isset($config['command_bus'])) {
            $container->getDefinition('messenger_application.command.bus')
                ->replaceArgument('$bus', new Reference($config['command_bus']));
        }
        if (isset($config['application_event_bus'])) {
            $container->getDefinition('messenger_application.application_event.bus')
                ->replaceArgument('$bus', new Reference($config['application_event_bus']));
        }

        $configure_before = fn (string $service_id, array $stack, ContainerBuilder $container) =>
            $this->configureHandlers($service_id, '$before_handle', $stack, $container);
        $configure_after = fn (string $service_id, array $stack, ContainerBuilder $container) =>
            $this->configureHandlers($service_id, '$after_handle', $stack, $container);

        if (isset($config['query_bus'])) {
            $configure_before(
                'messenger_application.query.internal_bus',
                $config['query_bus']['before_handle'],
                $container
            );
            $configure_after(
                'messenger_application.query.internal_bus',
                $config['query_bus']['after_handle'],
                $container
            );
        }
        if (isset($config['domain_event_bus'])) {
            $configure_before(
                'messenger_application.domain_event.internal_bus',
                $config['domain_event_bus']['before_handle'],
                $container
            );
            $configure_after(
                'messenger_application.domain_event.internal_bus',
                $config['domain_event_bus']['after_handle'],
                $container
            );
        }
    }

    public function getAlias(): string
    {
        return 'messenger_application';
    }

    public function configureHandlers(string $service_id, string $arg, array $stack, ContainerBuilder $container): void
    {
        $container->getDefinition($service_id)
            ->setArgument($arg, array_map(static fn (string $id) => new Reference($id), $stack));
    }

    public function registerTags(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(QueryHandler::class)
            ->addTag('messenger_application.query.handler');
        $container->registerForAutoconfiguration(CommandHandler::class)
            ->addTag('messenger_application.command.handler');
        $container->registerForAutoconfiguration(ApplicationEventSubscriber::class)
            ->addTag('messenger_application.application_event.subscriber');
        $container->registerForAutoconfiguration(DomainEventSubscriber::class)
            ->addTag('messenger_application.domain_event.subscriber');
    }

}
