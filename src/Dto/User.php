<?php

namespace App\Dto;

class User
{
    /**
     * @param string|null    $id
     * @param string|null $displayName
     * @param string|null $phone
     */
    public function __construct(public ?string $id, public ?string $displayName, public ?string $phone)
    {
    }

    public static function fromEntity(?\App\Entity\User $getUser): self
    {
        return new self(
            $getUser?->getId(),
            $getUser?->getDisplayName(),
            $getUser?->getPhone()
        );
    }
}
