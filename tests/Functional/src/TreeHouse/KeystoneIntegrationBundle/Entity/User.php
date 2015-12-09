<?php

namespace TreeHouse\KeystoneIntegrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="keystone_integration_user")
 */
class User extends \TreeHouse\KeystoneBundle\Model\User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var bool
     */
    protected $superAdmin;

    /**
     * @param bool $superAdmin
     */
    public function __construct($superAdmin = false)
    {
        $this->superAdmin = $superAdmin;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->superAdmin;
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
    }
}
