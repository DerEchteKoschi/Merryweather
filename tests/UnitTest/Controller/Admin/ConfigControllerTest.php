<?php

namespace UnitTest\Controller\Admin;

use App\Controller\Admin\ConfigController;
use App\Merryweather\AppConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @small
 * @group unitTests
 */
class ConfigControllerTest extends TestCase
{

    public function testFeature()
    {
        $randomString = md5(time());
        $twigMock = $this->createMock(Environment::class);
        $twigMock->method('render')->willReturn($randomString);
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('getFlashBag')->willReturn(new FlashBag());
        $stackMock = $this->createMock(RequestStack::class);
        $stackMock->method('getSession')->willReturn($sessionMock);
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('has')->willReturn(true);
        $containerMock->method('get')->willReturnCallback(function ($string) use ($twigMock, $stackMock) {
            $mocks = [
                'twig' => $twigMock,
                'request_stack' => $stackMock
            ];

            return $mocks[$string];
        });

        $transMock = $this->createMock(TranslatorInterface::class);
        $transMock->method('trans')->willReturnArgument(0);

        $appCfgMock = $this->createMock(AppConfig::class);
        $appCfgMock->method('getConfigValue')->willReturn('on');

        $controller = new ConfigController($appCfgMock, $transMock);
        $controller->setContainer($containerMock);
        $request = new Request(request: ['cfg' => [AppConfig::CONFIG_MONTH_COUNT => 1]]);
        $request->setMethod('POST');
        $result = $controller->config($request);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals($randomString, $result->getContent());

    }

}
