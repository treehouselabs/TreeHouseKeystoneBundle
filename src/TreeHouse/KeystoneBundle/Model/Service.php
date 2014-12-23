<?php

namespace TreeHouse\KeystoneBundle\Model;

class Service
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $grants;

    /**
     * @var Endpoint[]
     */
    protected $endpoints;

    /**
     * @param string $name
     * @param string $type
     * @param string $grants
     */
    public function __construct($name, $type, $grants)
    {
        $this->name      = $name;
        $this->type      = $type;
        $this->grants    = $grants;
        $this->endpoints = [];
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $grants
     *
     * @return $this
     */
    public function setGrants($grants)
    {
        $this->grants = $grants;

        return $this;
    }

    /**
     * @return string
     */
    public function getGrants()
    {
        return $this->grants;
    }

    /**
     * Add endpoint
     *
     * @param string $publicUrl
     * @param string $adminUrl
     *
     * @return $this
     */
    public function addEndpoint($publicUrl, $adminUrl)
    {
        $endpoint = new Endpoint();
        $endpoint->setPublicUrl($publicUrl);
        $endpoint->setAdminUrl($adminUrl);
        $endpoint->setService($this);

        $this->endpoints[] = $endpoint;

        return $this;
    }

    /**
     * Get endpoints
     *
     * @return Endpoint[]
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }
}
