<?php

namespace UnitTest\Controller\Admin;

use App\Controller\Admin\LogsController;
use DG\BypassFinals;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

/**
 * @small
 * @group unitTests
 */
class LogsControllerTest extends TestCase
{
    private const LOG_FILE = <<<LOGFILE
{"message":"info","context":[{}],"level":200,"level_name":"INFO","channel":"app","datetime":"2023-01-31T11:10:25.297550+01:00","extra":{}}
{"message":"error","context":[{}],"level":400,"level_name":"ERROR","channel":"app","datetime":"2023-01-31T11:10:25.297966+01:00","extra":{}}
{"message":"alert","context":[{}],"level":550,"level_name":"ALERT","channel":"app","datetime":"2023-01-31T11:10:25.298064+01:00","extra":{}}
{"message":"critical","context":[{}],"level":500,"level_name":"CRITICAL","channel":"app","datetime":"2023-01-31T11:10:25.298131+01:00","extra":{}}
{"message":"emergency","context":[{}],"level":600,"level_name":"EMERGENCY","channel":"app","datetime":"2023-01-31T11:10:25.298190+01:00","extra":{}}
{"message":"notice","context":[{}],"level":250,"level_name":"NOTICE","channel":"app","datetime":"2023-01-31T11:10:25.298247+01:00","extra":{}}
{"message":"warning","context":[{}],"level":300,"level_name":"WARNING","channel":"app","datetime":"2023-01-31T11:10:25.298305+01:00","extra":{}}
{"message":"start of logs for test","context":{},"level":250,"level_name":"NOTICE","channel":"app","datetime":"2023-01-31T11:31:01.219748+01:00","extra":{}}
LOGFILE;

    public function testIndex()
    {
        BypassFinals::enable();

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


        $controller = new LogsController(sys_get_temp_dir() . '/logs');
        $controller->setContainer($containerMock);
        $aug = $this->createMock(AdminUrlGenerator::class);
        $result = $controller->logs(new Request(query: ['log' => 'test.log']), $aug);
        $this->assertEquals(200, $result->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();
        if (!file_exists(sys_get_temp_dir() . '/logs')) {
            mkdir(sys_get_temp_dir() . '/logs');
        }
        if (file_exists(sys_get_temp_dir() . '/logs/test.log')) {
            unlink(sys_get_temp_dir() . '/logs/test.log');
        }
        file_put_contents(sys_get_temp_dir() . '/logs/test.log', self::LOG_FILE);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unlink(sys_get_temp_dir() . '/logs/test.log');
        rmdir(sys_get_temp_dir() . '/logs');
    }

}
