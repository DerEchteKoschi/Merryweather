<?php

namespace App\Merryweather;

use App\Merryweather\Config\DataType;
use App\Merryweather\Config\UnknownKeyException;
use App\Repository\AppConfigRepository;

class AppConfig
{
    public const TYPE = 0;
    public const DEFAULT = 1;

    public const CONFIG_CRON_ACTIVE = 'cronActive';
    public const CONFIG_MONTH_COUNT = 'monthCount';
    public const CONFIG_SCORE_LIMIT = 'scoreLimit';
    public const CONFIG_SCORE_RAISE_STEP = 'scoreRaiseStep';
    public const CONFIG_SCORE_DISTRIBUTION = 'scoreDistribution';
    public const CONFIG_ADMIN_CANCEL_ALLOWED = 'adminCancel';
    public const CONFIG_ADMIN_SHOW_POINTS = 'adminShowPoints';
    public const CONFIG_MERCURE_ACTIVE = 'mercure.active';

    public const CONFIG_DEFINITION = [
        self::CONFIG_MONTH_COUNT => [DataType::Integer, 3],
        self::CONFIG_SCORE_LIMIT => [DataType::Integer, 5],
        self::CONFIG_SCORE_RAISE_STEP => [DataType::Integer, 1],
        self::CONFIG_SCORE_DISTRIBUTION => [DataType::IntArrayArray, [[2, 1, 0, 0]]],
        self::CONFIG_ADMIN_CANCEL_ALLOWED => [DataType::Boolean, false],
        self::CONFIG_ADMIN_SHOW_POINTS => [DataType::Boolean, true],
        self::CONFIG_CRON_ACTIVE => [DataType::Boolean, false],
        self::CONFIG_MERCURE_ACTIVE => [DataType::Boolean, false],
    ];

    /**
     * @var mixed[]
     */
    private array $cache = [];

    public function __construct(private readonly AppConfigRepository $configRepository)
    {
    }

    /**
     * @param string $key
     * @param bool   $forceFresh
     * @return string|bool|int|float|mixed[]|null
     * @throws UnknownKeyException
     */
    public function getConfigValue(string $key, bool $forceFresh = false): null|string|bool|int|float|array
    {
        if (!isset(self::CONFIG_DEFINITION[$key])) {
            throw new UnknownKeyException($key);
        }
        if (!$forceFresh && isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $result = $this->configRepository->findOneBy(['configKey' => $key]);
        if ($result === null) {
            $value = self::CONFIG_DEFINITION[$key][self::DEFAULT];
        } else {
            /** @var DataType $type */
            $type = self::CONFIG_DEFINITION[$key][self::TYPE];
            $value = match ($type) {
                DataType::Integer => (int)$result->getValue(),
                DataType::Boolean => $result->getValue() === 'on',
                DataType::String => (string)$result->getValue(),
                DataType::Float => (float)$result->getValue(),
                DataType::IntArray => $this->toIntArray($result->getValue()),
                DataType::IntArrayArray => $this->toIntArrayArray($result->getValue())
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

    /**
     * @return int[][]
     * @throws UnknownKeyException
     */
    public function getScoreConfig(): array
    {
        return $this->getConfigValue(self::CONFIG_SCORE_DISTRIBUTION);
    }

    public function getScoreConfigRaw(): string
    {
        $cfg = $this->getScoreConfig();
        $t = [];
        foreach ($cfg as $day) {
            $t[] = implode(',', $day);
        }
        $cfg = implode(';', $t);

        return $cfg;
    }

    /**
     * @throws UnknownKeyException
     */
    public function getScoreLimit(): int
    {
        return $this->getConfigValue(self::CONFIG_SCORE_LIMIT);
    }

    /**
     * @throws UnknownKeyException
     */
    public function getScoreRaiseStep(): int
    {
        return $this->getConfigValue(self::CONFIG_SCORE_RAISE_STEP);
    }

    /**
     * @throws UnknownKeyException
     */
    public function isAdminCancelAllowed(): bool
    {
        return $this->getConfigValue(self::CONFIG_ADMIN_CANCEL_ALLOWED);
    }

    /**
     * @throws UnknownKeyException
     */
    public function isAdminShowPoints(): bool
    {
        return $this->getConfigValue(self::CONFIG_ADMIN_SHOW_POINTS);
    }

    /**
     * @throws UnknownKeyException
     */
    public function isCronActive(): bool
    {
        return $this->getConfigValue(self::CONFIG_CRON_ACTIVE);
    }

    /**
     * @throws UnknownKeyException
     */
    public function isMercureActive(): bool
    {
        return $this->getConfigValue(self::CONFIG_MERCURE_ACTIVE);
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

    /**
     * @param string $value
     * @return int[]
     */
    private function toIntArray(string $value): array
    {
        $result = explode(',', $value);
        foreach ($result as $k => $v) {
            $result[$k] = (int)$v;
        }

        return $result;
    }

    /**
     * @param string $value
     * @return int[][]
     */
    private function toIntArrayArray(string $value): array
    {
        $result = explode(';', $value);
        foreach ($result as $k => $v) {
            $result[$k] = $this->toIntArray($v);
        }

        return $result;
    }
}
