<?php


namespace CptBurke\Application\SymfonyMessengerBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder('messenger_application');
        $root = $tree->getRootNode();
        $root->children()
            ->append($this->createDefaultsNode())
            ->append($this->createQueryNode())
            ->scalarNode('command_bus')->end()
            ->scalarNode('application_event_bus')->end()
            ->append($this->createDomainNode())
        ;

        return $tree;
    }

    public function createQueryNode(): NodeDefinition
    {
        $tree = new TreeBuilder('query_bus');
        $root = $tree->getRootNode();
        $root->children()
            ->arrayNode('before_handle')
                ->scalarPrototype()->end()
            ->end()
            ->arrayNode('after_handle')
                ->scalarPrototype()->end()
            ->end()
        ;

        return $root;
    }

    public function createDomainNode(): NodeDefinition
    {
        $tree = new TreeBuilder('domain_event_bus');
        $root = $tree->getRootNode();
        $root->children()
            ->arrayNode('before_handle')
                ->scalarPrototype()->end()
            ->end()
            ->arrayNode('after_handle')
                ->scalarPrototype()->end()
            ->end()
        ;

        return $root;
    }

    public function createDefaultsNode(): NodeDefinition
    {
        $tree = new TreeBuilder('defaults');
        $root = $tree->getRootNode();
        $root->children()
            ->arrayNode('before_handle')
                ->scalarPrototype()->end()
            ->end()
            ->arrayNode('after_handle')
                ->scalarPrototype()->end()
            ->end()
        ;

        return $root;
    }

}
