<?php
/**
 * CORS middleware service provider
 *
 * @package     markinjapan/laravel-cors
 * @author      Mark Prosser <markinjapan@users.noreply.github.com>
 * @copyright   Copyright (c) Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link        https://github.com/markinjapan/laravel-cors
 */

namespace MarkInJapan\LaravelCors;

use Illuminate\Support\ServiceProvider;

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
        $this->app->singleton('MarkInJapan\LaravelCors\Middleware\Cors', function ($app) {
            // Load cors configuration
            $app->configure('cors');

            // Create new CORS middleware, with config
            return new Middleware\Cors($app['config']['cors']);
        });
    }
}