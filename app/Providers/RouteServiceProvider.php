<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        Route::get('ping', ['as' => 'ping', 'uses' => '\App\Http\StatusController@ping']);

        $this->mapApiRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::group([], function () {
            $this->mapShorteningAPIRoutes();
            $this->mapAnalyticsAPIRoutes();
            $this->mapAccessAPIRoutes();
        });
    }

    protected function mapShorteningAPIRoutes(): void
    {
        Route::group(['as' => 'shorten::v1::', 'prefix' => 'shorten/v1'], function (Router $router) {
            $router->group(['prefix' => 'urls'], function (Router $router) {
                $router->post('/', ['as' => 'urls.create', 'uses' => '\App\Packages\Url\Http\Controllers\V1\UrlController@create']);
                $router->post('jobs', ['as' => 'urls.jobs.create', 'uses' => '\App\Packages\Url\Http\Controllers\V1\JobController@create']);

                $router->get('/jobs/download/{id}', ['as' => 'urls.jobs.download', 'uses' => '\App\Packages\Url\Http\Controllers\V1\JobController@download']);
                $router->get('jobs/{id}', ['as' => 'urls.jobs.find', 'uses' => '\App\Packages\Url\Http\Controllers\V1\JobController@find']);
            });
        });
    }

    protected function mapAccessAPIRoutes(): void
    {
        Route::group(['as' => 'v1::router::', 'prefix' => 'r'], function (Router $router) {
            $router->get('{path}', ['as' => 'route', 'uses' => '\App\Packages\Url\Http\Controllers\V1\UrlController@route']);
        });
    }

    protected function mapAnalyticsAPIRoutes(): void
    {
        Route::group(['as' => 'analytics::v1::', 'prefix' => 'analytics/v1'], function (Router $router) {
            $router->group(['prefix' => 'metrics'], function (Router $router) {
                $router->get('{path}', ['as' => 'metric.get', 'uses' => '\App\Packages\Analytics\Http\V1\Controllers\AnalyticsController@find']);
            });
        });
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->map();
    }
}
