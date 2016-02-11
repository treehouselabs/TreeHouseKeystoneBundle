<?php

namespace TreeHouse\KeystoneBundle\Tests\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use TreeHouse\KeystoneBundle\Entity\Token;
use TreeHouse\KeystoneBundle\Manager\TokenManager;
use TreeHouse\KeystoneBundle\Security\Encoder\TokenEncoder;
use TreeHouse\KeystoneIntegrationBundle\Entity\User;

class TokenManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $secret = 'sup3rs3cr4t';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private $doctrine;

    /**
     * @var TokenManager
     */
    private $manager;

    protected function setUp()
    {
        $encoder = new TokenEncoder($this->secret);
        $this->doctrine = $this->createDoctrineMock();
        $this->doctrine
            ->expects($this->any())
            ->method('getManager')
            ->willReturn($this->getMockForAbstractClass(EntityManagerInterface::class))
        ;

        $this->manager = new TokenManager($encoder, $this->doctrine);
    }

    /**
     * @test
     */
    public function it_can_create_a_token()
    {
        $user = new User();
        $ttl = 600;

        $token = $this->manager->createToken($user, $ttl);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertNotEmpty($token->getHash());
        $this->assertEquals($ttl, $token->getExpiresAt()->getTimestamp() - time(), 'Token should be created with TTL', 2);
    }

    /**
     * @test
     */
    public function it_can_check_for_expired_tokens()
    {
        $this->assertFalse($this->manager->isExpired((new Token())->setExpiresAt(new \DateTime('10 seconds'))));
        $this->assertTrue($this->manager->isExpired((new Token())->setExpiresAt(new \DateTime('-10 seconds'))));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ManagerREgistry
     */
    private function createDoctrineMock()
    {
        return $this
            ->getMockBuilder(ManagerRegistry::class)
            ->getMockForAbstractClass()
        ;
    }
}
