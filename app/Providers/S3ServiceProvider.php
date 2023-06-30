<?php

namespace App\Providers;

use App\Services\S3Service;
use App\Interface\S3ServiceInterface;
use Illuminate\Support\ServiceProvider;


class S3ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(S3ServiceInterface::class, S3Service::class);
        $this->app->alias(S3ServiceInterface::class, 'S3BucketService');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
