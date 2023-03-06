<?php

namespace tests\Controller;

use App\Controller\CronController;
use App\Entity\Crontab;
use App\Merryweather\AppConfig;
use App\Merryweather\SymfonyCli;
use App\Repository\CrontabRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CronControllerTest extends TestCase
{
    public function cronTabProvider()
    {
        yield [(new Crontab())->setExpression('@daily')];
        yield [(new Crontab())->setExpression('@daily')->setNextExecution(new \DateTimeImmutable('yesterday'))->setCommand('list')];
    }

    /**
     * @param Crontab $cronTab
     *
     * @dataProvider cronTabProvider
     *
     * @throws \Exception
     */
    public function testIndex(Crontab $cronTab)
    {
        $configMock = $this->createMock(AppConfig::class);
        $configMock->method('isCronActive')->willReturn(true);

        $crontabRepositoryMock = $this->createMock(CrontabRepository::class);
        $crontabRepositoryMock->method('findAll')->willReturn([$cronTab]);
        $symfonyCliMock = $this->createMock(SymfonyCli::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $controller = new CronController();
        $controller->setLogger($loggerMock);
        $result = $controller->index($configMock, $crontabRepositoryMock, $symfonyCliMock);
        $this->assertEquals('done', $result->getContent());
    }
}
