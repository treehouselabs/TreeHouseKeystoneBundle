<?php

namespace TreeHouse\KeystoneBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class HttpPostFactory implements SecurityFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return 'http';
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return 'keystone_user';
    }

    /**
     * @inheritdoc
     */
    public function addConfiguration(NodeDefinition $node)
    {
        // noop
    }

    /**
     * @inheritdoc
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'tree_house.keystone.authentication.provider.http_post.' . $id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('tree_house.keystone.authentication.provider.http_post'))
            ->replaceArgument(2, $id)
        ;

        $listenerId = 'tree_house.keystone.authentication.listener.http_post.'.$id;
        $container
            ->setDefinition($listenerId, new DefinitionDecorator('tree_house.keystone.authentication.listener.http_post'))
            ->replaceArgument(2, $id)
        ;

        return [$providerId, $listenerId, $defaultEntryPoint];
    }
}
