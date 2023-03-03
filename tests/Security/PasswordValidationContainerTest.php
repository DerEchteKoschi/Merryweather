<?php

namespace tests\Security;

use App\Security\PasswordValidationContainer;
use PHPUnit\Framework\TestCase;

class PasswordValidationContainerTest extends TestCase
{

    public function testConstruct(){
        //just testing the constructor
        $a = new PasswordValidationContainer(null, null, null);
        $b = new PasswordValidationContainer('null', 'null', 'null');
        $this->assertTrue(true);
    }
}
