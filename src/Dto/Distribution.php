<?php

namespace App\Dto;

use DateTimeInterface;
use Stringable;

class Distribution implements Stringable
{
    /**
     * @param int                    $id
     * @param string                 $text
     * @param DateTimeInterface|null $activeFrom
     * @param DateTimeInterface|null $activeTill
     * @param Slot[]                 $slots
     */
    private function __construct(public int $id, public string $text, public ?DateTimeInterface $activeFrom, public ?DateTimeInterface $activeTill, public array $slots)
    {
    }

    /**
     * @param \App\Entity\Distribution[] $distributionEntities
     * @return Distribution[]
     */
    public static function fromList(array $distributionEntities): array
    {
        $result = [];
        foreach ($distributionEntities as $distributionEntity) {
            $result[] = self::fromEntity($distributionEntity);
        }

        return $result;
    }

    private static function fromEntity(\App\Entity\Distribution $distributionEntity): Distribution
    {
        return new Distribution(
            $distributionEntity->getId(),
            $distributionEntity->getText(),
            $distributionEntity->getActiveFrom(),
            $distributionEntity->getActiveTill(),
            Slot::fromList($distributionEntity->getSlots())
        );
    }

    public function __toString(): string
    {
        return sprintf('%s [%s]', $this->text, $this->activeTill->format('d.m.Y'));
    }
}
