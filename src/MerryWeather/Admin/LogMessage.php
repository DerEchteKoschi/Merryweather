<?php

namespace App\MerryWeather\Admin;

class LogMessage
{
    public function __construct(
        private readonly string $channel,
        private readonly string $message,
        private readonly int $level,
        private readonly string $level_name,
        private readonly \DateTimeImmutable $datetime
    ) {
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getLevelName(): string
    {
        return $this->level_name;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDatetime(): \DateTimeImmutable
    {
        return $this->datetime;
    }
}
