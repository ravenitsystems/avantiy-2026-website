<?php

namespace App\Duda;

use Exception;

class PartnerApi extends BaseApi
{
    public function __construct()
    {
        $user = strval(getenv('DUDA_API_USER') ?? '');
        $pass = strval(getenv('DUDA_API_PASS') ?? '');
        if ($user === '' || $pass === '') {
            throw new Exception("The DUDA API user and/or password is not configured");
        }
        parent::__construct($user, $pass);
    }
}
