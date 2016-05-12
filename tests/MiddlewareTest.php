<?php
/**
 * CORS middleware test
 *
 * @package     phpnexus/cors-laravel
 * @copyright   Copyright (c) 2016 Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link        https://github.com/phpnexus/cors-laravel
 */

namespace PhpNexus\CorsLaravel\Tests;

use PhpNexus\Cors\CorsService;
use PhpNexus\CorsLaravel\Middleware as CorsLaravelMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CorsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return CorsService
     */
    public function build_middleware()
    {
        $corsService = new CorsService([
            'allowMethods'     => ['PATCH', 'DELETE'],
            'allowHeaders'     => ['Authorization'],
            'allowOrigins'     => ['http://example.com'],
            'allowCredentials' => true,
            'exposeHeaders'    => ['X-My-Custom-Header'],
            'maxAge'           => 3600,
        ]);

        return new CorsLaravelMiddleware($corsService);
    }

    /**
     * Test response headers are set from preflight request
     */
    public function test_preflight_request()
    {
        $request = new Request;
        $request->setMethod('OPTIONS');
        $request->headers->set('Origin', 'http://example.com');
        $request->headers->set('Access-Control-Request-Method', 'PATCH');
        $request->headers->set('Access-Control-Request-Headers', ['Authorization']);

        $middleware = $this->build_middleware();

        $response = $middleware->handle($request, function($request) {
            return new Response;
        });

        $this->assertEquals('http://example.com', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('true', $response->headers->get('Access-Control-Allow-Credentials'));
        $this->assertEquals(['PATCH', 'DELETE'], $response->headers->get('Access-Control-Allow-Methods', [], false));
        $this->assertEquals(['Authorization'], $response->headers->get('Access-Control-Allow-Headers', [], false));
        $this->assertEquals('3600', $response->headers->get('Max-Age'));
    }

    /**
     * Test response headers are set from actual request
     */
    public function test_actual_request()
    {
        $request = new Request;
        $request->setMethod('PATCH');
        $request->headers->set('Origin', 'http://example.com');

        $middleware = $this->build_middleware();

        $response = $middleware->handle($request, function($request) {
            return new Response;
        });

        $this->assertEquals('http://example.com', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('true', $response->headers->get('Access-Control-Allow-Credentials'));
        $this->assertEquals(['X-My-Custom-Header'], $response->headers->get('Access-Control-Expose-Headers', [], false));
    }
}