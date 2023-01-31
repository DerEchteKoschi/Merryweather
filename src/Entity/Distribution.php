<?php

namespace App\Entity;

use App\Repository\DistributionRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DistributionRepository::class)]
class Distribution implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $active_from = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $active_till = null;

    #[ORM\Column(length: 255)]
    private ?string $text = null;

    /**
     * @var Collection<int, Slot>
     */
    #[ORM\OneToMany(mappedBy: 'distribution', targetEntity: Slot::class, orphanRemoval: true)]
    #[ORM\OrderBy(["startAt" => "ASC"])]
    private Collection $slots;

    public function __construct()
    {
        $this->slots = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s [%s]', $this->text, $this->active_till->format('d.m.Y'));
    }

    public function addSlot(Slot $slot): self
    {
        if (!$this->slots->contains($slot)) {
            $this->slots->add($slot);
            $slot->setDistribution($this);
        }

        return $this;
    }

    public function getActiveFrom(): ?DateTimeInterface
    {
        return $this->active_from;
    }

    public function getActiveTill(): ?DateTimeInterface
    {
        return $this->active_till;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Slot>
     */
    public function getSlots(): Collection
    {
        return $this->slots;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function removeSlot(Slot $slot): self
    {
        // set the owning side to null (unless already changed)
        if ($this->slots->removeElement($slot) && $slot->getDistribution() === $this) {
            $slot->setDistribution(null);
        }

        return $this;
    }

    public function setActiveFrom(DateTimeInterface $active_from): self
    {
        $this->active_from = $active_from;

        return $this;
    }

    public function setActiveTill(DateTimeInterface $active_till): self
    {
        $this->active_till = $active_till;

        return $this;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }
}
