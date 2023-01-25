<?php

namespace App\Extensions;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordValidationContainer
{
    public function __construct(
        #[Assert\NotBlank(message: 'Das aktuelle Kennwort darf nicht leer sein')]
        #[Assert\NotNull(message: 'Das aktuelle Kennwort darf nicht leer sein')]
        public ?string $current,
        #[Assert\NotBlank(message: 'Das aktuelle Kennwort darf nicht leer sein')]
        #[Assert\NotNull(message: 'Das aktuelle Kennwort darf nicht leer sein')]
        #[Assert\Length(['min' => 10], minMessage: 'Das Kennwort ist nicht lang genug (mindestens 10 Zeichen)')]
        #[Assert\NotCompromisedPassword(message: 'Das Kennwort ist unsicher, es wurde in mindestens einem Kennwortdiebstahl gefunden, es wird empfohlen ein anderes zu wählen', payload: ['severity' => 'warning'])]
        public ?string $new,
        #[Assert\EqualTo([
            'propertyPath' => 'new'
        ], message: 'Das Wiederholung des Kennworts stimmt nicht überein')]
        #[Assert\NotNull(message: 'Das aktuelle Kennwort darf nicht leer sein')]
        public ?string $newRepeat
    ) {
    }
}
