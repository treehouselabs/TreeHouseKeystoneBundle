<?php

namespace TreeHouse\KeystoneBundle\Tests\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use TreeHouse\KeystoneBundle\DependencyInjection\TreeHouseKeystoneExtension;

class TreeHouseKeystoneExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testConfiguration()
    {
        $container = $this->getContainer('complete.yml');

        // test parameters
        $parameters = [
            'tree_house.keystone.model.user.class' => 'TreeHouse\KeystoneIntegrationBundle\Entity\User',
            'tree_house.keystone.user_provider.id' => 'my_user_provider',
            'tree_house.keystone.service_types' => ['foo', 'bar', 'baz'],
        ];

        foreach ($parameters as $name => $value) {
            $this->assertTrue($container->hasParameter($name));
            $this->assertEquals($value, $container->getParameter($name));
        }

        // test the service manager
        $this->assertTrue($container->hasDefinition('tree_house.keystone.service_manager'));
        $manager = $container->getDefinition('tree_house.keystone.service_manager');

        // test the loaded service types
        $this->assertEquals(
            ['setTypes', [['foo', 'bar', 'baz']]],
            $manager->getMethodCalls()[0]
        );

        // test the loaded service
        $this->assertTrue($container->hasDefinition('tree_house.keystone.service.foo'));

        // assert the class
        $this->assertTrue($container->hasDefinition('tree_house.keystone.service'));

        /** @var DefinitionDecorator $service */
        $service = $container->getDefinition('tree_house.keystone.service.foo');
        $this->assertEquals('tree_house.keystone.service', $service->getParent());

        // assert the constructor args
        $this->assertEquals('foo',   $service->getArgument(0));
        $this->assertEquals('bar',   $service->getArgument(1));
        $this->assertEquals('ROLE_FOO_USER', $service->getArgument(2));

        // test that an endpoint is added
        $this->assertEquals(
            ['addEndpoint', ['http://example.org', 'http://example.org']],
            $service->getMethodCalls()[0]
        );

        // test that the service is added to the manager
        list($method, $args) = $manager->getMethodCalls()[1];
        $this->assertEquals('addService', $method);
        $this->assertEquals('tree_house.keystone.service.foo', (string) current($args));
    }

    public function testEndpointNormalization()
    {
        $container = $this->getContainer('endpoints.yml');

        $service = $container->getDefinition('tree_house.keystone.service.api');
        $this->assertEquals(
            ['addEndpoint', ['https://api.example.org/', 'https://api.example.org/']],
            $service->getMethodCalls()[0]
        );

        $service = $container->getDefinition('tree_house.keystone.service.api2');
        $this->assertEquals(
            ['addEndpoint', ['http://api.example.org/', 'http://api.example.org/']],
            $service->getMethodCalls()[0]
        );
        $this->assertEquals(
            ['addEndpoint', ['https://api.example.org/', 'https://api.example.org/']],
            $service->getMethodCalls()[1]
        );

        $service = $container->getDefinition('tree_house.keystone.service.cdn');
        $this->assertEquals(
            ['addEndpoint', ['http://examplecdn.org/', 'https://admin.example.org/']],
            $service->getMethodCalls()[0]
        );

        $service = $container->getDefinition('tree_house.keystone.service.cdn2');
        $this->assertEquals(
            ['addEndpoint', ['http://examplecdn.org/', 'https://admin.example.org/']],
            $service->getMethodCalls()[0]
        );

        $service = $container->getDefinition('tree_house.keystone.service.cdn3');
        $this->assertEquals(
            ['addEndpoint', ['http://cdn.example.org/', 'https://admin.example.org/']],
            $service->getMethodCalls()[0]
        );

        $this->assertEquals(
            ['addEndpoint', ['http://examplecdn.org/', 'https://admin.examplecdn.org/']],
            $service->getMethodCalls()[1]
        );
    }

    /**
     * @expectedException        \LogicException
     * @expectedExceptionMessage Service must be one of the registered types
     */
    public function testInvalidServiceType()
    {
        $this->getContainer('invalid_service_type.yml');
    }

    public function testNoRoleOrExpression()
    {
        $container = $this->getContainer('role_nor_expression.yml');

        /** @var DefinitionDecorator $service */
        $service = $container->getDefinition('tree_house.keystone.service.foo');
        $this->assertEquals('tree_house.keystone.service', $service->getParent());

        // assert that when neither role nor expression is specified, we default to ROLE_USER
        $this->assertEquals('ROLE_USER', $service->getArgument(2));
    }

    /**
     * @expectedException        \LogicException
     * @expectedExceptionMessage You cannot set both a role and expression for a service
     */
    public function testRoleAndExpression()
    {
        $this->getContainer('role_and_expression.yml');
    }

    private function getContainer($file, $parameters = [], $debug = false)
    {
        $container = new ContainerBuilder(new ParameterBag(array_merge($parameters, ['kernel.debug' => $debug])));
        $container->registerExtension(new TreeHouseKeystoneExtension());

        $locator = new FileLocator(__DIR__ . '/fixtures');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load($file);

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();

        return $container;
    }
}
