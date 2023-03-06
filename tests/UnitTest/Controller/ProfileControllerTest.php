<?php

namespace UnitTest\Controller;

use App\Controller\ProfileController;
use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @small
 * @group unitTests
 */
class ProfileControllerTest extends TestCase
{
    public function changePasswordData()
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('123');
        $cv = new ConstraintViolation('hi', null, [], '', null, '', null, null, new class extends Constraint {
        }, null);
        yield [new User()];
        yield [new User(), false];
        yield [$user];
        yield [new User(), true, $cv];
    }

    /**
     * @param $user
     * @dataProvider changePasswordData
     *
     */
    public function testChangePassword(UserInterface $user, $valid = true, ConstraintViolation $cv = null)
    {
        $routerMock = $this->createMock(Router::class);
        $routerMock->method('generate')->willReturnArgument(0);
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getFlashBag')->willReturn(new FlashBag());
        $stackMock = $this->createMock(RequestStack::class);
        $stackMock->method('getSession')->willReturn($sessionMock);
        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenMock->method('getUser')->willReturn($user);

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('has')->willReturn(true);
        $containerMock->method('get')->willReturnCallback(function ($string) use ($stackMock, $tokenStorageMock, $routerMock) {
            $mocks = [
                'request_stack' => $stackMock,
                'security.token_storage' => $tokenStorageMock,
                'router' => $routerMock
            ];

            return $mocks[$string];
        });

        $validatorMock = $this->createMock(ValidatorInterface::class);
        if ($cv !== null) {
            $cv->getConstraint()->payload = ['severity' => 'danger'];
            $validatorMock->method('validate')->willReturn(new ConstraintViolationList([$cv]));

        }

        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userPasswordHasherMock = $this->createMock(UserPasswordHasherInterface::class);
        $userPasswordHasherMock->method('isPasswordValid')->willReturn($valid);
        $requestMock = new Request(request: ['_inputPasswordCurrent' => 'test', '_inputPasswordNew' => '', '_inputPasswordNewRepeat' => '']);

        $controller = new ProfileController($this->transLatorMock());
        $controller->setContainer($containerMock);
        $result = $controller->changePassword($validatorMock, $userRepositoryMock, $userPasswordHasherMock, $requestMock);
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('app_profile', $result->getTargetUrl());
        $this->assertEquals(302, $result->getStatusCode());
    }

    public function testIndex()
    {
        $twigMock = $this->createMock(Environment::class);
        $twigMock->method('render')->willReturn('done');
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('has')->willReturn(true);
        $containerMock->method('get')->willReturnCallback(function ($string) use ($twigMock) {
            $mocks = [
                'twig' => $twigMock,
            ];

            return $mocks[$string];
        });


        $controller = new ProfileController($this->transLatorMock());
        $controller->setContainer($containerMock);
        $result = $controller->index();
        $this->assertEquals('done', $result->getContent());
        $this->assertEquals(200, $result->getStatusCode());
    }

    private function transLatorMock()
    {
        $mock = $this->createMock(TranslatorInterface::class);
        $mock->method('trans')->willReturnArgument(0);

        return $mock;
    }
}
