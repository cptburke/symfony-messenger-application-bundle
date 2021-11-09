<?php


declare(strict_types=1);


namespace CptBurke\Application\SymfonyMessengerBundle;


use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class MessengerApplicationBundle extends Bundle
{

    public function getContainerExtension(): bool|DependencyInjection\MessengerApplicationExtension|ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new DependencyInjection\MessengerApplicationExtension();
        }

        return $this->extension;
    }

}
