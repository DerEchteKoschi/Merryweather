<?php

namespace App\MerryWeather\Admin;

use App\Repository\AppConfigRepository;

class AppConfig
{
    public const TYPE = 0;
    public const DEFAULT = 1;

    public const CONFIG_CRON_ACTIVE = 'cronActive';
    public const CONFIG_MONTH_COUNT = 'monthCount';
    public const CONFIG_SCORE_LIMIT = 'scoreLimit';
    public const CONFIG_SCORE_RAISE_STEP = 'scoreRaiseStep';

    public const CONFIG_KEYS = [
        self::CONFIG_MONTH_COUNT => 'Anzahl an Monaten im Dashboard',
        self::CONFIG_CRON_ACTIVE => 'Cron funktionalität aktivieren (webcron)',
        self::CONFIG_SCORE_LIMIT => 'Maximale Punkte die ein User haben kann',
        self::CONFIG_SCORE_RAISE_STEP => 'Wert um die der CronJob die Punkte erhöht',
    ];
    public const CONFIG_DEFINITION = [
        self::CONFIG_MONTH_COUNT => [DataType::Integer, 3],
        self::CONFIG_CRON_ACTIVE => [DataType::Boolean, false],
        self::CONFIG_SCORE_LIMIT => [DataType::Integer, 5],
        self::CONFIG_SCORE_RAISE_STEP => [DataType::Integer, 1],
    ];

    /**
     * @var mixed[]
     */
    private array $cache = [];

    public function __construct(private readonly AppConfigRepository $configRepository)
    {
    }

    /**
     * @throws UnknownKeyException
     */
    public function getConfigValue(string $key, bool $forceFresh = false): null|string|bool|int|float
    {
        if (!isset(self::CONFIG_KEYS[$key])) {
            throw new UnknownKeyException($key);
        }
        if (!$forceFresh && isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $result = $this->configRepository->findBy(['configKey' => $key]);
        if (empty($result)) {
            $value = self::CONFIG_DEFINITION[$key][self::DEFAULT];
        } else {
            /** @var DataType $type */
            $type = self::CONFIG_DEFINITION[$key][self::TYPE];
            $value = match ($type) {
                DataType::Integer => (int)$result[0]->getValue(),
                DataType::Boolean => $result[0]->getValue() === 'on',
                DataType::String => (string)$result[0]->getValue(),
                DataType::Float => (float)$result[0]->getValue()
            };
        }
        $this->cache[$key] = $value;

        return $value;
    }

    /**
     * @throws UnknownKeyException
     */
    public function getMonthCount(): int
    {
        return $this->getConfigValue(self::CONFIG_MONTH_COUNT);
    }

    public function getScoreLimit(): int
    {
        return $this->getConfigValue(self::CONFIG_SCORE_LIMIT);
    }

    public function isCronActive(): bool
    {
        return $this->getConfigValue(self::CONFIG_CRON_ACTIVE);
    }

    public function getScoreRaiseStep(): int
    {
        return $this->getConfigValue(self::CONFIG_SCORE_RAISE_STEP);
    }

    public function setConfigValue(int|string $key, string $value): void
    {
        $cfg = $this->configRepository->findOneBy(['configKey' => $key]);
        if ($cfg === null) {
            $cfg = new \App\Entity\AppConfig();
            $cfg->setConfigKey($key);
        }
        $cfg->setValue($value);
        $this->configRepository->save($cfg, true);
    }
}
