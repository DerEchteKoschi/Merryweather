<?php

namespace tests\Security;

use App\Security\PasswordSuggest;
use PHPUnit\Framework\TestCase;

class PasswordSuggestTest extends TestCase
{

    public function testPasswordSuggestion()
    {
        $password = PasswordSuggest::suggestPassword();
        $this->assertNotEmpty($password);
        $this->assertGreaterThan(6, strlen($password));
    }
}
