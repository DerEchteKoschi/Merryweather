<?php

namespace UnitTest\Merryweather;

use App\Merryweather\AppConfig;
use App\Merryweather\Config\UnknownKeyException;
use App\Repository\AppConfigRepository;
use PHPUnit\Framework\TestCase;

/**
 * @small
 * @group unitTests
 */
class AppConfigTest extends TestCase
{
    public function cfgCallback($key)
    {
        $data = $this->getData();

        $this->assertArrayHasKey($key['configKey'], $data);

        return $data[$key['configKey']];
    }

    public function testGetConfig()
    {
        $appConfigRepositoryMock = $this->createMock(AppConfigRepository::class);
        $appConfigRepositoryMock->expects($this->exactly(count($this->getData())))->method('findOneBy')->willReturnCallback([$this, 'cfgCallback']);
        $appConfig = new AppConfig($appConfigRepositoryMock);
        $this->assertTrue($appConfig->isCronActive());
        $this->assertTrue($appConfig->isAdminCancelAllowed());
        $this->assertTrue($appConfig->isMercureActive());
        $this->assertSame([[1, 2, 3],[4,5]], $appConfig->getScoreConfig());
        $this->assertSame('1,2,3;4,5', $appConfig->getScoreConfigRaw());
        $this->assertEquals(6, $appConfig->getScoreRaiseStep());
        $this->assertEquals(5, $appConfig->getScoreLimit());
        $this->assertEquals(6, $appConfig->getMonthCount());
        $this->assertTrue($appConfig->isAdminShowPoints());
        //twice on purpose (coverage of cache)
        $this->assertEquals(6, $appConfig->getMonthCount());
        $this->expectException(UnknownKeyException::class);
        $appConfig->getConfigValue('##doesNotExist##');
    }

    public function testKeysTested()
    {
        foreach (AppConfig::CONFIG_DEFINITION as $key => $def) {
            $this->assertArrayHasKey($key, $this->getData());
        }
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

    private function getData()
    {
        return [
            'cronActive' => (new \App\Entity\AppConfig())->setValue('on'),
            'adminCancel' => (new \App\Entity\AppConfig())->setValue('on'),
            'adminShowPoints' => (new \App\Entity\AppConfig())->setValue('on'),
            'scoreRaiseStep' => (new \App\Entity\AppConfig())->setValue('6'),
            'scoreLimit' => null,
            'monthCount' => (new \App\Entity\AppConfig())->setValue('6'),
            'scoreDistribution' => (new \App\Entity\AppConfig())->setValue('1,2,3;4,5'),
            'mercure.active' => (new \App\Entity\AppConfig())->setValue('on'),
        ];
    }
}
