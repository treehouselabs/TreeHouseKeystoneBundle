<?php

namespace TreeHouse\KeystoneBundle\Model;

class Endpoint
{
    /**
     * @var string
     */
    protected $adminUrl;

    /**
     * @var string
     */
    protected $publicUrl;

    /**
     * @var Service
     */
    protected $service;

    /**
     * @param string $publicUrl
     *
     * @return Endpoint
     */
    public function setPublicUrl($publicUrl)
    {
        $this->publicUrl = $publicUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublicUrl()
    {
        return $this->publicUrl;
    }

    /**
     * @param string $adminUrl
     *
     * @return Endpoint
     */
    public function setAdminUrl($adminUrl)
    {
        $this->adminUrl = $adminUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminUrl()
    {
        return $this->adminUrl;
    }

    /**
     * @param Service $service
     *
     * @return Endpoint
     */
    public function setService(Service $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }
}
