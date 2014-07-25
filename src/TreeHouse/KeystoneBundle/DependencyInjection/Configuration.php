<?php

namespace TreeHouse\KeystoneBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tree_house_keystone');

        $rootNode
            ->children()
                ->scalarNode('user_class')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('FQCN of the user class')
                ->end()

                ->scalarNode('user_provider_id')
                    ->defaultValue('tree_house.keystone.user_provider.default')
                    ->cannotBeEmpty()
                    ->info(<<<INFO
Service id of the user provider to use.

You can replace the default user provider with your own if you need to add logic to
load a user. For instance when your User class doesn't have a username field.
INFO
                    )
                ->end()

                ->arrayNode('service_types')
                    ->prototype('scalar')->end()
                    ->defaultValue(['compute', 'object-store'])
                    ->info('Defined service types')
                ->end()

                ->arrayNode('services')
                    ->info('Defined services')
                    ->example(<<<YAML
services:
  api:
    type: compute
    endpoint: http://api.example.org
YAML
                    )
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')
                                ->isRequired()
                                ->info('The service type, must be one of the defined service_types')
                            ->end()
                            ->scalarNode('role')
                                ->info('The required role for this service. Cannot be used with expression.')
                            ->end()
                            ->scalarNode('expression')
                                ->info('The required role for this service Cannot be used with role.')
                            ->end()
                            ->arrayNode('endpoint')
                                ->info('Defines an endpoint at which this service can be accessed')
                                // single endpoint, supplied as a string
                                ->beforeNormalization()
                                    ->ifString()->then(function ($v) { return array($v); })
                                ->end()
                                // single endpoint, supplied as an array
                                ->beforeNormalization()
                                    ->ifTrue(function ($v) { return is_array($v) && is_string(key($v)); })
                                    ->then(function ($v) { return [$v]; })
                                ->end()
                                ->prototype('array')
                                    ->beforeNormalization()
                                        ->ifTrue(function ($v) { return is_string($v); })
                                        ->then(function ($v) { return ['public_url' => $v, 'admin_url' => $v]; })
                                    ->end()
                                    ->children()
                                        ->scalarNode('public_url')
                                            ->info('The URL of the public-facing endpoint for the service')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                            ->validate()
                                                ->ifTrue(function ($v) { return !filter_var($v, FILTER_VALIDATE_URL); })
                                                ->thenInvalid('Invalid url: %s')
                                            ->end()
                                        ->end()
                                        ->scalarNode('admin_url')
                                            ->info(<<<INFO
The URL for the admin endpoint for the service.

This can be different from public_url if you want to separate for instance read/write access,
but for most use cases they will be the same.
INFO
                                            )
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                            ->validate()
                                                ->ifTrue(function ($v) { return !filter_var($v, FILTER_VALIDATE_URL); })
                                                ->thenInvalid('Invalid url: %s')
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
