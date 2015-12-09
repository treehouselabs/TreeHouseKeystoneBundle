<?php

namespace TreeHouse\KeystoneBundle\Manager;

use Symfony\Component\HttpFoundation\Request;
use TreeHouse\KeystoneBundle\Model\Service;

class ServiceManager
{
    /**
     * @var string[]
     */
    protected $types = [];

    /**
     * @var Service[]
     */
    protected $services = [];

    /**
     * @param array $types
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Add service.
     *
     * @param Service $service
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * Returns all services.
     *
     * @return Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param string $endpoint
     *
     * @throws \OutOfBoundsException When there is no service for the endpoint
     *
     * @return Service
     */
    public function findServiceByEndpoint($endpoint)
    {
        $url = $this->getNormalizedUrl($endpoint);

        foreach ($this->services as $service) {
            foreach ($service->getEndpoints() as $endpoint) {
                $endpointUrl = $this->getNormalizedUrl($endpoint->getPublicUrl());
                if (substr($url, 0, strlen($endpointUrl)) === $endpointUrl) {
                    return $service;
                }
            }
        }

        throw new \OutOfBoundsException(sprintf('There is no service for endpoint %s', $url));
    }

    /**
     * @param string $name
     *
     * @throws \OutOfBoundsException When there is no service with this name
     *
     * @return Service
     */
    public function findServiceByName($name)
    {
        foreach ($this->services as $service) {
            if ($service->getName() === $name) {
                return $service;
            }
        }

        throw new \OutOfBoundsException(sprintf('There is no service named %s', $name));
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function getNormalizedUrl($url)
    {
        return Request::create($url)->getUri();
    }
}
