<?php

namespace tests\Merryweather;

use App\MerryWeather\AppConfig;
use App\MerryWeather\Config\UnknownKeyException;
use App\Repository\AppConfigRepository;
use PHPUnit\Framework\TestCase;

class AppConfigTest extends TestCase
{
    public function testGetConfig()
    {
        $appConfigRepositoryMock = $this->createMock(AppConfigRepository::class);
        $appConfigRepositoryMock->expects($this->exactly(6))->method('findOneBy')->willReturnCallback([$this, 'cfgCallback']);
        $appConfig = new AppConfig($appConfigRepositoryMock);
        $this->assertTrue($appConfig->isCronActive());
        $this->assertTrue($appConfig->isAdminCancelAllowed());
        $this->assertSame([1, 2, 3], $appConfig->getScoreConfig());
        $this->assertEquals(6, $appConfig->getScoreRaiseStep());
        $this->assertEquals(5, $appConfig->getScoreLimit());
        $this->assertEquals(6, $appConfig->getMonthCount());
        //twice on purpose (coverage of cache)
        $this->assertEquals(6, $appConfig->getMonthCount());
        $this->expectException(UnknownKeyException::class);
        $appConfig->getConfigValue('##doesNotExist##');
    }

    public function testSetConfig()
    {
        $appConfigRepositoryMock = $this->createMock(AppConfigRepository::class);
        $appConfigRepositoryMock->expects($this->exactly(2))->method('findOneBy')->willReturnCallback([$this, 'cfgCallback']);
        $appConfigRepositoryMock->expects($this->exactly(2))->method('save');
        $appConfig = new AppConfig($appConfigRepositoryMock);
        $appConfig->setConfigValue(AppConfig::CONFIG_SCORE_LIMIT, '2');
        $appConfig->setConfigValue(AppConfig::CONFIG_MONTH_COUNT, '2');

    }

    public function cfgCallback($key)
    {


        $data = [
            'cronActive' => (new \App\Entity\AppConfig())->setValue('on'),
            'adminCancel' => (new \App\Entity\AppConfig())->setValue('on'),
            'scoreRaiseStep' => (new \App\Entity\AppConfig())->setValue('6'),
            'scoreLimit' => null,
            'monthCount' => (new \App\Entity\AppConfig())->setValue('6'),
            'scoreDistribution' => (new \App\Entity\AppConfig())->setValue('1,2,3'),

        ];

        $this->assertArrayHasKey($key['configKey'], $data);

        return $data[$key['configKey']];
    }
}
