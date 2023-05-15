<?php

namespace App\Entity;

use App\Entity\Shared\UUID;
use App\Repository\AppConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppConfigRepository::class)]
class AppConfig
{
    use UUID;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $configKey = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $value = null;

    public function getConfigKey(): ?string
    {
        return $this->configKey;
    }

    public function setConfigKey(string $configKey): self
    {
        $this->configKey = $configKey;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
