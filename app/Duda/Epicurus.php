<?php

namespace App\Duda;

use Exception;

class Epicurus extends Base
{
    private string $app_uuid;

    public function __construct()
    {
        parent::__construct('/api/integrationhub/');
        $this->app_uuid = strval(getenv("EPICURUS_APP_ID"));
        if ($this->app_uuid == '') {
            throw new Exception('app_uuid not set');
        }
    }

    public function getManifest(): array
    {

    }
}
