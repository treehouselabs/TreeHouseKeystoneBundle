<?php

namespace TreeHouse\KeystoneBundle\Model;

class Token
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var \DateTime
     */
    protected $expiresAt;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $hash
     *
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param \DateTime $expiresAt
     *
     * @return $this
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }
}
