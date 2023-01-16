<?php

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\AccountInactive;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{

    /**
     * @inheritDoc
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive()){
            throw new AccountInactive();
        }
    }

    /**
     * @inheritDoc
     */
    public function checkPostAuth(UserInterface $user)
    {
        //not needed
    }
}
