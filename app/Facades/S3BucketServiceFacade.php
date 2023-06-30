<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class S3BucketServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'S3BucketService'; // The binding identifier in the service container
    }
}
