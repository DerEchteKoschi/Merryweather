<?php

namespace tests\Controller;

use App\Controller\SessionController;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class SessionControllerTest extends TestCase
{
    public function scenarioProvider(): \Generator
    {
        yield [null, false, false];
        yield [new User(), true, false];
        yield [null, false, true];
    }

    /**
     * @dataProvider scenarioProvider
     */
    public function testLogin($user, $redirect, $error)
    {
        $authenticationUtilsMock = $this->createMock(AuthenticationUtils::class);
        if ($error) {
            $authenticationUtilsMock->method('getLastAuthenticationError')->willReturn(new AuthenticationException('ERROR'));
        }
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getFlashBag')->willReturn(new FlashBag());
        $stackMock = $this->createMock(RequestStack::class);
        $stackMock->method('getSession')->willReturn($sessionMock);
        $tokenMock = $this->createMock(TokenInterface::class);
        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->method('getToken')->willReturn($tokenMock);
        $tokenMock->method('getUser')->willReturn($user);
        $twigMock = $this->createMock(Environment::class);
        $twigMock->method('render')->willReturn('done');
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('has')->willReturn(true);
        $containerMock->method('get')->willReturnCallback(function ($string) use ($tokenStorageMock, $twigMock, $stackMock) {
            $mocks = [
                'security.token_storage' => $tokenStorageMock,
                'twig' => $twigMock,
                'request_stack' => $stackMock
            ];

            return $mocks[$string];
        });


        $controller = new SessionController();
        $controller->setContainer($containerMock);
        $result = $controller->login($authenticationUtilsMock);
        if ($redirect) {
            $this->assertInstanceOf(RedirectResponse::class, $result);
        } else {
            $this->assertEquals('done', $result->getContent());

        }
    }

    public function testLogout()
    {
        $controller = new SessionController();
        $controller->logout();
        $this->assertTrue(true);
    }
}
