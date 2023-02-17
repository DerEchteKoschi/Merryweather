<?php

namespace App\Merryweather\Admin;

class LogMessage
{
    public function __construct(private string $channel, private string $message, private int $level, private string $level_name, private \DateTimeImmutable $datetime)
    {
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel(string $channel): void
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getLevelName(): string
    {
        return $this->level_name;
    }

    /**
     * @param string $level_name
     */
    public function setLevelName(string $level_name): void
    {
        $this->level_name = $level_name;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDatetime(): \DateTimeImmutable
    {
        return $this->datetime;
    }

    /**
     * @param \DateTimeImmutable $datetime
     */
    public function setDatetime(\DateTimeImmutable $datetime): void
    {
        $this->datetime = $datetime;
    }
}
