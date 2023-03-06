<?php

namespace UnitTest\Controller\Admin;

use App\Controller\Admin\Deploy2FAController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @small
 * @group unitTests
 */
class Deploy2FAControllerTest extends TestCase
{

    public function testFeature()
    {
        $randomString = md5(time());
        $twigMock = $this->createMock(Environment::class);
        $twigMock->method('render')->willReturn($randomString);
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('has')->willReturn(true);
        $containerMock->method('get')->willReturnCallback(function ($string) use ($twigMock) {
            $mocks = [
                'twig' => $twigMock,
            ];

            return $mocks[$string];
        });

        $transMock = $this->createMock(TranslatorInterface::class);
        $transMock->method('trans')->willReturnArgument(0);

        $controller = new Deploy2FAController('hi', $transMock, true);
        $controller->setContainer($containerMock);
        $result = $controller->twofa();

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals($randomString, $result->getContent());

        $controller = new Deploy2FAController('hi', $transMock, false);
        $controller->setContainer($containerMock);
        $result = $controller->twofa();

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('feature_deactivated', $result->getContent());
    }

}
