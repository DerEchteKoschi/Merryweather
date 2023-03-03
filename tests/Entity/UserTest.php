<?php

namespace tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testEntity()
    {
        $e = new User();
        $this->assertNull($e->getId());
        $this->assertNull($e->getPhone());
        $this->assertNull($e->getDisplayName());
        $this->assertNull($e->getScore());
        $this->assertNull($e->getLastname());
        $this->assertNull($e->getFirstname());
        $this->assertNull($e->getLastVisit());
        $this->assertNull($e->getLastLogin());
        $this->assertNull($e->getEmail());
        $this->assertIsArray($e->getRoles());
        $this->assertCount(1, $e->getRoles());
        $this->assertEquals('ROLE_USER', $e->getRoles()[0]);
        $this->assertEquals(false, $e->isActive());

        $e->setEmail('test@example.org')
        ->setPhone('01234')
        ->setDisplayName('dname')
        ->setPassword('hi')
        ->setActive(true)
        ->setRoles(['ROLE_USER', 'BARREL_ROLE'])
        ->setScore(10)
        ->setFirstname('first')
        ->setLastname('last')
        ->setLastLogin(new \DateTimeImmutable('01.01.2023 00:00'))
        ->setLastVisit(new \DateTimeImmutable('02.01.2023 00:00'))
        ->eraseCredentials(); //for coverage

        $this->assertIsArray($e->getRoles());
        $this->assertCount(2, $e->getRoles());
        $this->assertEquals(['ROLE_USER', 'BARREL_ROLE'], $e->getRoles());

        $this->assertEquals(true, $e->isActive());
        $this->assertEquals('01234', $e->getUserIdentifier());
        $this->assertEquals('test@example.org', $e->getEmail());
        $this->assertEquals('01234', $e->getPhone());
        $this->assertEquals('dname', $e->getDisplayName());
        $this->assertEquals('hi', $e->getPassword());
        $this->assertEquals(10, $e->getScore());
        $this->assertEquals('first', $e->getFirstname());
        $this->assertEquals('last', $e->getLastname());
        $this->assertEquals('01.01.2023 00:00', $e->getLastLogin()->format('d.m.Y H:i'));
        $this->assertEquals('02.01.2023 00:00', $e->getLastVisit()->format('d.m.Y H:i'));

        $this->assertEquals('dname (01234)', (string)$e);
    }
}
