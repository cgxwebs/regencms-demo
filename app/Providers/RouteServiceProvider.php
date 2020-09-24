<?php

namespace App\Providers;

use App\Channel;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
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
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/hq/stories';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapChannelRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    protected function mapChannelRoutes()
    {
        /**
         * This will map all channel routes if they exist on routes/channel_name.php
         * allowing multiple domain frontend.
         * Channel routes will only be added if hostname is set
         */

        Route::domain(config('regencms.hq_hostname'))
            ->middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/hq.php'));

        $channels = (env('APP_ENV') !== 'install') ? Channel::all() : [];
        foreach($channels as $c) {
            $route_file = base_path(sprintf('routes/channel_%s.php', $c->name));
            if (!empty($c->url) && is_readable($route_file)) {
                Route::domain($c->url)
                    ->middleware('web')
                    ->namespace($this->namespace)
                    ->group($route_file);
            }
        }
    }
}
