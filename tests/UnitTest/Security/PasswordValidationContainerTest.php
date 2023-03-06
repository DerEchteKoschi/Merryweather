<?php

namespace UnitTest\Security;

use App\Security\PasswordValidationContainer;
use PHPUnit\Framework\TestCase;

/**
 * @small
 * @group unitTests
 */
class PasswordValidationContainerTest extends TestCase
{

    public function testConstruct()
    {
        //just testing the constructor
        $a = new PasswordValidationContainer(null, null, null);
        $b = new PasswordValidationContainer('null', 'null', 'null');
        $this->assertTrue(true);
    }
}
