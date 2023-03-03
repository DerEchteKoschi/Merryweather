<?php

namespace tests\Security;

use App\Entity\User;
use App\Security\Exception\AccountInactive;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    public function testUserCheckerSuccess(){
        $user = new User();
        $user->setActive(true);
        $uc = new UserChecker();
        $uc->checkPreAuth($user);
        $uc->checkPostAuth($user);
        $this->assertTrue(true);
    }

    public function testUserCheckerSuccessWrongClass(){
        $user = $this->createMock(UserInterface::class);
        $uc = new UserChecker();
        $uc->checkPreAuth($user);
        $uc->checkPostAuth($user);
        $this->assertTrue(true);
    }

    public function testUserCheckerFail(){
        $this->expectException(AccountInactive::class);
        $user = new User();
        $user->setActive(false);
        $uc = new UserChecker();
        $uc->checkPreAuth($user);
        $uc->checkPostAuth($user);
    }
}
