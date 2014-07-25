<?php

namespace TreeHouse\KeystoneIntegrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="keystone_integration_token")
 */
class Token extends \TreeHouse\KeystoneBundle\Model\Token
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
