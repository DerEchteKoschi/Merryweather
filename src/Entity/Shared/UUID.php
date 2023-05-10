<?php

namespace App\Entity\Shared;

use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;

trait UUID
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidV7Generator::class)]
    private UuidInterface|string $id;

    public function getId(): string
    {
        return $this->id;
    }
}