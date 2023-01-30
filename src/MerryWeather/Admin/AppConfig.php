<?php

namespace App\MerryWeather\Admin;

use App\Repository\AppConfigRepository;

class AppConfig
{
    public const TYPE = 0;
    public const DEFAULT = 1;
    public const TYPE_INT = 0;
    public const TYPE_STRING = 1;
    public const TYPE_BOOL = 2;
    public const TYPE_FLOAT = 3;

    public const CONFIG_APP_TITLE = 'appTitle';
    public const CONFIG_CRON_ACTIVE = 'cronActive';
    public const CONFIG_MONTH_COUNT = 'monthCount';

    public const CONFIG_KEYS = [
        self::CONFIG_MONTH_COUNT => 'Number of months on Dashboard',
        self::CONFIG_CRON_ACTIVE => 'Webcron Active (crons called via external service) ',
    ];
    public const CONFIG_DEFINITION = [
        self::CONFIG_MONTH_COUNT => [self::TYPE_INT, 3],
        self::CONFIG_CRON_ACTIVE => [self::TYPE_BOOL, false],
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
            $value = match (self::CONFIG_DEFINITION[$key][self::TYPE]) {
                self::TYPE_INT => (int)$result[0]->getValue(),
                self::TYPE_BOOL => $this->toBool($result[0]->getValue()),
                //deactivated as long as there ar no cfg values of that type
                //self::TYPE_FLOAT => (float)$result[0]->getValue(),
                //self::TYPE_STRING => (string)$result[0]->getValue(),
            };
            if (null === $value) {
                $value = self::CONFIG_DEFINITION[$key][self::DEFAULT];
            }
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

    public function isCronActive(): bool
    {
        return $this->getConfigValue(self::CONFIG_CRON_ACTIVE);
    }

    private function toBool(?string $value): ?bool
    {
        if (null === $value) {
            return null;
        }

        return in_array(strtolower($value), ['yes', 'true', 'wahr', 'ja', 'j', 'y', '1']);
    }
}
