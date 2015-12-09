<?php

namespace TreeHouse\KeystoneBundle\Security\User;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use TreeHouse\KeystoneBundle\Model\UserInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var string
     */
    protected $userClass;

    /**
     * @param ManagerRegistry $doctrine
     * @param string          $userClass Entity class, for example "Acme\YourBundle\Entity\User"
     */
    public function __construct(ManagerRegistry $doctrine, $userClass)
    {
        $this->doctrine = $doctrine;

        // normalize the user class
        $this->userClass = $this->getClassMetadata($userClass)->getName();
    }

    /**
     * @inheritdoc
     */
    public function supportsClass($class)
    {
        return $class === $this->userClass;
    }

    /**
     * @param string $username
     *
     * @return UserInterface
     */
    public function findUserByUsername($username)
    {
        return $this->findUserBy(['username' => $username]);
    }

    /**
     * @inheritdoc
     */
    public function loadUserByUsername($username)
    {
        if (null === $user = $this->findUserByUsername($username)) {
            throw new UsernameNotFoundException(sprintf('No user with name "%s" was found.', $username));
        }

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function refreshUser(BaseUserInterface $user)
    {
        if (!$user instanceof $this->class) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $repository = $this->getRepository();

        if ($repository instanceof UserProviderInterface) {
            $refreshedUser = $repository->refreshUser($user);
        } else {
            // The user must be reloaded via the primary key as all other data
            // might have changed without proper persistence in the database.
            $meta = $this->getClassMetadata($this->userClass);
            if (!$id = $meta->getIdentifierValues($user)) {
                throw new \InvalidArgumentException('You cannot refresh a user ' .
                    'from the EntityUserProvider that does not contain an identifier. ' .
                    'The user object has to be serialized with its own identifier ' .
                    'mapped by Doctrine.'
                );
            }

            $refreshedUser = $repository->find($id);
            if (null === $refreshedUser) {
                throw new UsernameNotFoundException(sprintf('User with id %s not found', json_encode($id)));
            }
        }

        return $refreshedUser;
    }

    /**
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->doctrine->getRepository($this->userClass);
    }

    /**
     * @param $criteria
     *
     * @return UserInterface|null
     */
    protected function findUserBy($criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @param string $class
     *
     * @return ClassMetadata
     */
    protected function getClassMetadata($class)
    {
        return $this->doctrine->getManagerForClass($class)->getClassMetadata($class);
    }
}
