<?php

namespace TreeHouse\KeystoneBundle\Test;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use TreeHouse\KeystoneBundle\Manager\ServiceManager;
use TreeHouse\KeystoneBundle\Model\UserInterface;

abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @var string
     */
    protected static $username = 'test';

    /**
     * @var string
     */
    protected static $password = '1234';

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->client = $this->createClient();

        $container = static::$kernel->getContainer();

        /** @var EntityManagerInterface[] $managers */
        $managers = $container->get('doctrine')->getManagers();
        foreach ($managers as $manager) {
            $metadata = $manager->getMetadataFactory()->getAllMetadata();

            if (!empty($metadata)) {
                $tool = new SchemaTool($manager);
                $tool->dropSchema($metadata);
                $tool->createSchema($metadata);
            }
        }

        $class = $container->getParameter('tree_house.keystone.model.user.class');

        $salt = uniqid();

        /** @var UserInterface $user */
        $user = new $class();
        $user->setEnabled(true);
        $user->setUsername('test');
        $user->addRole('ROLE_USER');
        $user->setSalt($salt);

        /** @var EncoderFactoryInterface $encoder */
        $encoder = $container->get('security.encoder_factory');
        $password = $encoder->getEncoder($class)->encodePassword(static::$password, $user->getSalt());
        $user->setPassword($password);

        /** @var ManagerRegistry $doctrine */
        $doctrine = static::$kernel->getContainer()->get('doctrine');
        $manager = $doctrine->getManagerForClass($class);
        $manager->persist($user);
        $manager->flush($user);
        $manager->refresh($user);

        $this->user = $user;
    }

    /**
     * @return UserProviderInterface
     */
    protected function getUserProvider()
    {
        return static::$kernel->getContainer()->get('tree_house.keystone.user_provider');
    }

    /**
     * @return ServiceManager
     */
    protected function getServiceManager()
    {
        return static::$kernel->getContainer()->get('tree_house.keystone.service_manager');
    }

    /**
     * @return UserInterface
     */
    protected function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    public function getRoute($name, array $parameters = [])
    {
        return static::$kernel->getContainer()->get('router')->generate($name, $parameters);
    }

    /**
     * Creates a new user and requests a valid token.
     *
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    protected function requestToken()
    {
        $data = [
            'auth' => [
                'passwordCredentials' => [
                    'username' => static::$username,
                    'password' => static::$password,
                ],
            ],
        ];

        $this->client->request('POST', $this->getRoute('get_token'), [], [], [], json_encode($data));

        $result = json_decode($this->client->getResponse()->getContent(), true);

        if (is_array($result)) {
            return $result;
        }

        throw new \UnexpectedValueException(
            sprintf(
                'Unexpected response (%d): %s',
                $this->client->getResponse()->getStatusCode(),
                $this->client->getResponse()->getContent()
            )
        );
    }

    /**
     * @param string  $method
     * @param string  $uri
     * @param array   $parameters
     * @param array   $files
     * @param array   $server
     * @param string  $content
     * @param boolean $changeHistory
     *
     * @return Crawler
     */
    protected function requestWithValidToken(
        $method,
        $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        $content = null,
        $changeHistory = true
    ) {
        $server = array_merge(
            $server,
            ['HTTP_X-Auth-Token' => $this->requestToken()['access']['token']['id']]
        );

        return $this->client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
    }
}
