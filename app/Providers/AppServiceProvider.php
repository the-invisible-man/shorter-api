<?php

namespace App\Providers;

use App\Http\V1\Requests\Request;
use App\Packages\Voyager\VoyagerService;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configureRequests();

        $this->app->singleton(\Redis::class, function () {
            $redis = new \Redis;

            $host = config('database.redis.default.host');
            $port = config('database.redis.default.port');

            $redis->connect($host, $port);

            return $redis;
        });

        $this->app->singleton(VoyagerService::class, function () {
            $client = new Client([
                'http_errors' => false,
            ]);

            return new VoyagerService($client);
        });
    }

    /**
     * Configure the request related services.
     */
    protected function configureRequests(): void
    {
        $this->app->resolving(Request::class, function (Request $request, $app) {
            $this->initializeRequest($request, $app['request']);

            $request->setContainer($app);
        });
    }

    /**
     * This allows us to use custom request classes in our controllers.
     * This is executed when the implicit bindings are being resolved by the framework.
     *
     * Our controller methods type-hint the request implementation
     * we want to use. When the service container resolves the class, we intercept the Symfony
     * request class and copy its values into our custom request implementation.
     *
     * @param Request        $request
     * @param SymfonyRequest $current
     */
    protected function initializeRequest(Request $request, SymfonyRequest $current): void
    {
        $files = $current->files->all();
        $files = array_filter($files);

        $request->initialize(
            $current->query->all(),
            $current->request->all(),
            $current->attributes->all(),
            $current->cookies->all(),
            $files,
            $current->server->all(),
            $current->getContent()
        );

        if ($current->hasSession()) {
            $request->setSession($current->getSession());
        }

        $request->setRouteResolver($current->getRouteResolver());
    }
}
