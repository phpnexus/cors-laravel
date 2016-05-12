<?php
/**
 * CORS service provider
 *
 * @package     phpnexus/cors-laravel
 * @copyright   Copyright (c) 2016 Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link        https://github.com/phpnexus/cors-laravel
 */

namespace PhpNexus\CorsLaravel;

use Illuminate\Support\ServiceProvider;
use PhpNexus\Cors\CorsService;
use PhpNexus\CorsLaravel\Middleware as CorsMiddleware;

/**
 * CORS service provider class
 */
class CorsServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container
     *
     * @return void
     */
    public function register()
    {
        // CORS service
        $this->app->singleton(CorsService::class, function ($app) {
            // Load cors configuration
            $app->configure('cors');

            return new CorsService($app['config']['cors']);
        });

        // CORS middleware
        $this->app->singleton(CorsMiddleware::class, function ($app) {
            // Create new CorsMiddleware, with CorsService
            return new CorsMiddleware($app->make(CorsService::class));
        });
    }
}