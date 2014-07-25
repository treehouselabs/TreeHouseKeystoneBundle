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
     * Add service
     *
     * @param Service $service
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * Returns all services
     *
     * @return Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param string $url
     *
     * @return Service
     */
    public function findServiceByEndpoint($url)
    {
        $url = $this->getNormalizedUrl($url);

        foreach ($this->services as $service) {
            foreach ($service->getEndpoints() as $endpoint) {
                $endpointUrl = $this->getNormalizedUrl($endpoint->getPublicUrl());
                if (substr($url, 0, strlen($endpointUrl)) === $endpointUrl) {
                    return $service;
                }
            }
        }

        return null;
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
