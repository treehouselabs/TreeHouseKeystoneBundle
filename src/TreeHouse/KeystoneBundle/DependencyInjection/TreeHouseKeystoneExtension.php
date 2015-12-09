<?php

namespace TreeHouse\KeystoneBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Kernel;
use TreeHouse\KeystoneBundle\Security\Authentication\LegacyTokenAuthenticator;

class TreeHouseKeystoneExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('security.yml');

        $container->setParameter('tree_house.keystone.model.user.class', $config['user_class']);
        $container->setParameter('tree_house.keystone.user_provider.id', $config['user_provider_id']);
        $container->setParameter('tree_house.keystone.service_types', $config['service_types']);

        $userProviderServiceId = $container->getParameter('tree_house.keystone.user_provider.id');
        $container->setAlias('tree_house.keystone.user_provider', $userProviderServiceId);

        $this->loadServices($container, $config['services'], $config['service_types']);
        $this->replaceLegacyTokenAuthenticator($container);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $services
     * @param array            $types
     *
     * @throws \LogicException
     */
    private function loadServices(ContainerBuilder $container, array $services, array $types)
    {
        $manager = $container->getDefinition('tree_house.keystone.service_manager');
        $manager->addMethodCall('setTypes', [$types]);

        foreach ($services as $name => $serviceConfig) {
            if (!in_array($serviceConfig['type'], $types)) {
                throw new \LogicException(
                    sprintf(
                        'Service must be one of the registered types (%s), "%s" given',
                        implode(', ', $types),
                        $serviceConfig['type']
                    )
                );
            }

            $grant = null;
            if (!empty($serviceConfig['role'])) {
                $grant = $serviceConfig['role'];
            }

            if (!empty($serviceConfig['expression'])) {
                if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                    throw new \LogicException(
                        'Unable to use expressions as the Symfony ExpressionLanguage component is not installed.'
                    );
                }

                if (null !== $grant) {
                    throw new \LogicException(<<<EOT
You cannot set both a role and expression for a service.
If you want to know how you can use the expression language
to check for roles, consult the documentation:

http://symfony.com/doc/current/book/security.html#complex-access-controls-with-expressions

EOT
                    );
                }

                $grant = new Definition('Symfony\Component\ExpressionLanguage\Expression');
                $grant->setPublic(false);
                $grant->addArgument($serviceConfig['expression']);
            }

            $id = sprintf('tree_house.keystone.service.%s', $name);
            $service = $container->setDefinition($id, new DefinitionDecorator('tree_house.keystone.service'));
            $service->replaceArgument(0, $name);
            $service->replaceArgument(1, $serviceConfig['type']);
            $service->replaceArgument(2, $grant ?: 'ROLE_USER');

            foreach ($serviceConfig['endpoint'] as $endpointConfig) {
                $service->addMethodCall('addEndpoint', [$endpointConfig['public_url'], $endpointConfig['admin_url']]);
            }

            $manager->addMethodCall('addService', [new Reference($id)]);
        }
    }

    /**
     * Replaces token authenticator service with legacy class for older Symfony versions.
     *
     * @todo remove when Symfony 2.6 and 2.7 are no longer supported
     *
     * @param ContainerBuilder $container
     */
    private function replaceLegacyTokenAuthenticator(ContainerBuilder $container)
    {
        if ((int) Kernel::MAJOR_VERSION === 2 && Kernel::MINOR_VERSION < 8) {
            $definition = $container->getDefinition('tree_house.keystone.token_authenticator');
            $definition->setClass(LegacyTokenAuthenticator::class);
        }
    }
}
