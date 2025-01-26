<?php

namespace App\Providers;

use App\Http\V1\Requests\Request;
use App\Packages\Auditing\AuditLogListener;
use App\Packages\Auditing\Events\ResourceCreated;
use App\Packages\Auditing\Events\ResourceDeleted;
use App\Packages\Auditing\Events\ResourceRestored;
use App\Packages\Auditing\Events\ResourceUpdated;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Event;
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

        $this->app->singleton(AuditLogListener::class, function () {
            $service = app(AuditLogService::class);

            return new AuditLogListener($service);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/audit-log.php' => config_path('audit-log.php'),
        ]);

        if (config('feature-gate.audit-log')) {
            Event::listen(ResourceCreated::class, AuditLogListener::class);
            Event::listen(ResourceUpdated::class, AuditLogListener::class);
            Event::listen(ResourceDeleted::class, AuditLogListener::class);
            Event::listen(ResourceRestored::class, AuditLogListener::class);
        }
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
