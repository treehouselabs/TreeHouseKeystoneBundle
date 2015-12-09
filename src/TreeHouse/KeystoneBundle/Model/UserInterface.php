<?php

namespace TreeHouse\KeystoneBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface as BaseInterface;

interface UserInterface extends BaseInterface
{
    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled);

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username);

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password);

    /**
     * @param string $salt
     *
     * @return $this
     */
    public function setSalt($salt);

    /**
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles(array $roles);

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role);

    /**
     * @param string $role
     *
     * @return $this
     */
    public function addRole($role);

    /**
     * @param string $role
     *
     * @return $this
     */
    public function removeRole($role);
}
