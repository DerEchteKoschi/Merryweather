<?php

namespace App\Security;

use App\Merryweather\AppConfig;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordValidationContainer
{
    public function __construct(
        #[Assert\NotBlank(message: 'current_pw_not_empty')]
        #[Assert\NotNull(message: 'current_pw_not_empty')]
        public ?string $current,
        #[Assert\NotBlank(message: 'pw_not_empty')]
        #[Assert\NotNull(message: 'pw_not_empty')]
        #[Assert\Length(['min' => 10], minMessage: 'pw_not_long_enough')]
        public ?string $new,
        #[Assert\EqualTo([
            'propertyPath' => 'new'
        ], message: 'pw_repeat_not_equal')]
        #[Assert\NotEqualTo([
            'propertyPath' => 'current'
        ], message: 'pw_not_equal_current')]
        #[Assert\NotNull(message: 'pw_not_empty')]
        public ?string $newRepeat
    ) {
    }
}
