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

    public const CONFIG_DEFINITION = [
        self::CONFIG_MONTH_COUNT => [DataType::Integer, 3],
        self::CONFIG_SCORE_LIMIT => [DataType::Integer, 5],
        self::CONFIG_SCORE_RAISE_STEP => [DataType::Integer, 1],
        self::CONFIG_SCORE_DISTRIBUTION => [DataType::IntArray, [2,1,0,0]],
        self::CONFIG_ADMIN_CANCEL_ALLOWED => [DataType::Boolean, false],
        self::CONFIG_ADMIN_SHOW_POINTS => [DataType::Boolean, true],
        self::CONFIG_CRON_ACTIVE => [DataType::Boolean, false],
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
        $result = $this->configRepository->findBy(['configKey' => $key]);
        if (empty($result) || $result[0]->getValue() === null) {
            $value = self::CONFIG_DEFINITION[$key][self::DEFAULT];
        } else {
            /** @var DataType $type */
            $type = self::CONFIG_DEFINITION[$key][self::TYPE];
            $value = match ($type) {
                DataType::Integer => (int)$result[0]->getValue(),
                DataType::Boolean => $result[0]->getValue() === 'on',
                DataType::String => (string)$result[0]->getValue(),
                DataType::Float => (float)$result[0]->getValue(),
                DataType::IntArray => $this->toIntArray($result[0]->getValue())
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
     * @return int[]
     */
    public function getScoreConfig(): array
    {
        return $this->getConfigValue(self::CONFIG_SCORE_DISTRIBUTION);
    }

    public function getScoreLimit(): int
    {
        return $this->getConfigValue(self::CONFIG_SCORE_LIMIT);
    }

    public function getScoreRaiseStep(): int
    {
        return $this->getConfigValue(self::CONFIG_SCORE_RAISE_STEP);
    }

    public function isAdminCancelAllowed(): bool
    {
        return $this->getConfigValue(self::CONFIG_ADMIN_CANCEL_ALLOWED);
    }

    public function isAdminShowPoints(): bool
    {
        return $this->getConfigValue(self::CONFIG_ADMIN_SHOW_POINTS);
    }

    public function isCronActive(): bool
    {
        return $this->getConfigValue(self::CONFIG_CRON_ACTIVE);
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
}
