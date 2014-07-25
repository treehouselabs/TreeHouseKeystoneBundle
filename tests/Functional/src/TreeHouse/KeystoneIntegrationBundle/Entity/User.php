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

    public function isSuperAdmin()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
    }
}
