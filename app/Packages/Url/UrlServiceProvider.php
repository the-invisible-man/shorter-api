<?php

namespace App\Packages\Url;

use App\Packages\Url\Repositories\EloquentJobRepository;
use App\Packages\Url\Repositories\EloquentUrlRepository;
use App\Packages\Url\Repositories\JobRepository;
use App\Packages\Url\Repositories\UrlRepository;
use Illuminate\Support\ServiceProvider;

class UrlServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EloquentJobRepository::class);
        $this->app->singleton(EloquentUrlRepository::class);

        $this->app->alias(EloquentUrlRepository::class, UrlRepository::class);
        $this->app->alias(EloquentJobRepository::class, JobRepository::class);
    }
}
