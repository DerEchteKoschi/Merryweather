<?php

namespace UnitTest\Controller;

use App\Controller\IndexController;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @small
 * @group unitTests
 */
class IndexControllerTest extends TestCase
{
    public function testIndexRedirect()
    {
        $routerMock = $this->createMock(Router::class);
        $routerMock->method('generate')->willReturnArgument(0);
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('has')->willReturn(true);
        $containerMock->method('get')->willReturnCallback(function ($string) use ($routerMock) {
            $mocks = [
                'router' => $routerMock
            ];

            return $mocks[$string];
        });


        $index = new IndexController();
        $index->setContainer($containerMock);
        $response = $index->index();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('app_slots', $response->getTargetUrl());
        $this->assertEquals(302, $response->getStatusCode());
    }
}
