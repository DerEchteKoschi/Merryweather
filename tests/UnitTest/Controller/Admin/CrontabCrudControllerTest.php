<?php

namespace UnitTest\Controller\Admin;

use App\Controller\Admin\CrontabCrudController;
use App\Entity\Crontab;
use App\Merryweather\Cronjobs;
use PHPUnit\Framework\TestCase;

/**
 * @small
 * @group unitTests
 */
class CrontabCrudControllerTest extends TestCase
{
    public function testIndex()
    {
        $this->assertEquals(Crontab::class, CrontabCrudController::getEntityFqcn());

        $cronjobsMock = $this->createMock(Cronjobs::class);
        $controller = new CrontabCrudController($cronjobsMock);

        $this->assertCount(7, $controller->configureFields('test'));

    }
}
