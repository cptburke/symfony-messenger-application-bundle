<?php


namespace tests\CptBurke\Application\SymfonyMessengerBundle\DependencyInjection;


use CptBurke\Application\SymfonyMessengerBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;


class ConfigurationTest extends TestCase
{

    public function testEmptyConfig(): void
    {
        $sut = new Configuration();
        $config = $sut->getConfigTreeBuilder()->buildTree();
        $this->expectException(InvalidConfigurationException::class);
        $input = ['messenger_application'];
        $config->normalize($input);
    }

    public function testCompleteConfig(): void
    {
        $sut = new Configuration();
        $config = $sut->getConfigTreeBuilder()->buildTree();
        $this->expectException(InvalidConfigurationException::class);
        $input = [
            'messenger_application' => [
                'command_bus' => 'test',
                'application_event_bus' => 'test',
                'domain_event_bus' => [ 'before_handle' => ['test_middleware'], 'after_handle' => []],
                'query_bus' => [ 'before_handle' => [], 'after_handle' => ['test_middleware']],
            ],
        ];
        $normalized = $config->normalize($input);
        $finalized = $config->finalize($normalized);

        $this->assertEquals($input, $finalized);
    }

}
