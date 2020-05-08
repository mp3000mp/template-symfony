<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountDisabledException extends AccountStatusException
{

    public function getMessageKey()
    {
        return 'security.connexion.err.account_disabled';
    }

}
