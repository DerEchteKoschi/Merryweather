<?php

namespace App\Entity;

use App\Repository\SlotRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SlotRepository::class)]
class Slot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Version, ORM\Column(type: 'integer')]
    private int $version;
    #[ORM\ManyToOne(inversedBy: 'slots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Distribution $distribution = null;
    #[ORM\ManyToOne]
    private ?User $user = null;
    #[ORM\Column(length: 255)]
    private ?string $text = null;
    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?DateTimeImmutable $startAt = null;

    public function getDistribution(): ?Distribution
    {
        return $this->distribution;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartAt(): DateTimeImmutable
    {
        return $this->startAt;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    public function setDistribution(?Distribution $distribution): self
    {
        $this->distribution = $distribution;

        return $this;
    }

    public function setStartAt(DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
