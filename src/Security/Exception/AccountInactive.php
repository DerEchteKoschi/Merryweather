<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountInactive extends AccountStatusException
{

    public function __construct()
    {
        parent::__construct('Dieser Zugang ist nicht aktiv');
    }
}
