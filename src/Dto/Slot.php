<?php

namespace App\Dto;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;

class Slot
{
    private function __construct(public string $id, public string $text, public DateTimeInterface $startAt, public User $user)
    {
    }

    /**
     * @param Collection<int, \App\Entity\Slot> $slotEntities
     * @return Slot[]
     */
    public static function fromList(Collection $slotEntities): array
    {
        $result = [];
        foreach ($slotEntities as $slotEntity) {
            $result[] = self::fromEntity($slotEntity);
        }

        return $result;
    }

    public static function fromEntity(\App\Entity\Slot $slotEntity): self
    {
        return new self(
            $slotEntity->getId(),
            $slotEntity->getText(),
            $slotEntity->getStartAt(),
            User::fromEntity($slotEntity->getUser()),
        );
    }
}
