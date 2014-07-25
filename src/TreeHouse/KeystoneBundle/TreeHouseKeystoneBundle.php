<?php

namespace TreeHouse\KeystoneBundle;

use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use TreeHouse\KeystoneBundle\DependencyInjection\Security\Factory\HttpPostFactory;

class TreeHouseKeystoneBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new HttpPostFactory());
    }
}
