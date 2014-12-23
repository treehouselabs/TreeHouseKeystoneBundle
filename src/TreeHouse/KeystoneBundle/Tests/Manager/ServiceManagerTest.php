<?php

namespace TreeHouse\KeystoneBundle\Tests\Manager;

use TreeHouse\KeystoneBundle\Manager\ServiceManager;
use TreeHouse\KeystoneBundle\Model\Service;

class ServiceManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $manager = new ServiceManager();

        $this->assertInstanceOf(ServiceManager::class, $manager);
    }

    public function testTypes()
    {
        $types = [
            'compute',
            'object-store',
        ];

        $manager = new ServiceManager();
        $manager->setTypes($types);

        $this->assertEquals($types, $manager->getTypes());
    }

    public function testServices()
    {
        $name   = 'api';
        $type   = 'compute';
        $grants = 'ROLE_API_USER';

        $service = new Service($name, $type, $grants);

        $endpoint = 'http://localhost/api';
        $service->addEndpoint($endpoint, $endpoint);

        $manager = new ServiceManager();
        $manager->addService($service);

        $this->assertCount(1, $manager->getServices());
        $this->assertSame($service, $manager->getServices()[0]);

        $this->assertSame($service, $manager->findServiceByName('api'));
        $this->assertSame($service, $manager->findServiceByEndpoint($endpoint));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testFindServiceByNameNotFound()
    {
        $service = new Service('api', 'compute', 'ROLE_API_USER');

        $manager = new ServiceManager();
        $manager->addService($service);
        $manager->findServiceByName('cdn');
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testFindServiceByEndpointNotFound()
    {
        $service = new Service('api', 'compute', 'ROLE_API_USER');
        $service->addEndpoint('http://localhost/api', 'http://localhost/api');

        $manager = new ServiceManager();
        $manager->addService($service);
        $manager->findServiceByEndpoint('http://localhost/');
    }
}
