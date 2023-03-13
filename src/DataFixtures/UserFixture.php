<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class UserFixture extends Fixture
{

    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {

        $manager->persist($this->getUser('1234', 'user1'));
        $manager->persist($this->getUser('2345', 'user2', roles: ['ROLE_USER', 'ROLE_ADMIN']));
        $manager->persist($this->getUser('3456', 'user3', false)->setActive(false));

        $manager->flush();
    }

    /**
     * @param string $phone
     * @param string $displayName
     * @return User
     */
    protected function getUser(string $phone, string $displayName, bool $isActive = true, array $roles = ['ROLE_USER']): User
    {
        $user = new User();
        $user->setPhone($phone);
        $user->setActive($isActive);
        $user->setDisplayName($displayName);
        $user->setRoles($roles);
        $user->setScore(5);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getDisplayName()));

        return $user;
    }
}
