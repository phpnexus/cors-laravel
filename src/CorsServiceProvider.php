<?php
/**
 * CORS middleware service provider
 *
 * @package     phpnexus/cors-laravel
 * @author      Mark Prosser <markinjapan@users.noreply.github.com>
 * @copyright   Copyright (c) Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link        https://github.com/phpnexus/cors-laravel
 */

namespace PhpNexus\CorsLaravel;

use Illuminate\Support\ServiceProvider;
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
        $this->app->singleton(CorsMiddleware::class, function ($app) {
            // Load cors configuration
            $app->configure('cors');

            // Create new CORS middleware, with config
            return new CorsMiddleware($app['config']['cors']);
        });
    }
}