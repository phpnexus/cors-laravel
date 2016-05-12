<?php
/**
 * CORS middleware
 *
 * @package     phpnexus/cors-laravel
 * @copyright   Copyright (c) 2016 Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link        https://github.com/phpnexus/cors-laravel
 */

namespace PhpNexus\CorsLaravel;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use PhpNexus\Cors\CorsRequest;
use PhpNexus\Cors\CorsService;

/**
 * CORS middleware class
 */
class Middleware
{
    /** @var PhpNexus\Cors\CorsService */
    protected $cors;

    /**
     * @param PhpNexus\Cors\CorsService $cors
     */
    public function __construct(CorsService $cors)
    {
        $this->cors = $cors;
    }

    /**
     * Handle CORS request
     *
     * @param  Illuminate\Http\Request  $request
     * @param  Closure $next
     * @return Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Build CorsRequest from Illuminate Request
        $corsRequest = $this->buildCorsRequest($request);

        // If preflight request; skip $next action and build new response
        if ($corsRequest->isPreflight()) {
            $response = new Response;
        }
        // Else actual request; perform $next action and collect response
        else {
            $response = $next($request);
        }

        // Process CorsRequest
        $corsResponse = $this->cors->process($corsRequest);

        // Apply CORS response parameters to Illuminate Response
        $response = $this->applyResponseParams($corsResponse, $response);

        return $response;
    }

    /**
     * Build CorsRequest from Illuminate Request object
     *
     * @param Illuminate\Http\Request $request
     * @return PhpNexus\Cors\CorsRequest
     */
    protected function buildCorsRequest(Request $request)
    {
        // Create CorsRequest and set method
        $corsRequest = (new CorsRequest)
            ->setMethod($request->method());

        // Set Origin if header exists
        if ($request->headers->has('Origin')) {
            $corsRequest->setOrigin($request->header('Origin'));
        }

        // Set access control request method if header exists
        if ($request->headers->has('Access-Control-Request-Method')) {
            $corsRequest->setAccessControlRequestMethod($request->header('Access-Control-Request-Method'));
        }

        // Set access control request headers if header exists
        if ($request->headers->has('Access-Control-Request-Headers')) {
            $corsRequest->setAccessControlRequestHeaders($request->headers->get('Access-Control-Request-Headers', [], false));
        }

        return $corsRequest;
    }

    /**
     * Apply parameters from CORS response to Illuminate Response object
     *
     * @param array $corsResponse
     * @param Symfony\Component\HttpFoundation\Response $response
     * @return Illuminate\Http\Response
     */
    protected function applyResponseParams(array $corsResponse, SymfonyResponse $response)
    {
        // Set Access-Control-Allow-Credentials header if appropriate
        if (isset($corsResponse['access-control-allow-credentials'])) {
            $response->headers->set(
                'Access-Control-Allow-Credentials',
                $corsResponse['access-control-allow-credentials']
            );
        }

        // Set Access-Control-Allow-Headers header if appropriate
        if (isset($corsResponse['access-control-allow-headers'])) {
            $response->headers->set(
                'Access-Control-Allow-Headers',
                $corsResponse['access-control-allow-headers']
            );
        }

        // Set Access-Control-Allow-Methods header if appropriate
        if (isset($corsResponse['access-control-allow-methods'])) {
            $response->headers->set(
                'Access-Control-Allow-Methods',
                $corsResponse['access-control-allow-methods']
            );
        }

        // Set Access-Control-Allow-Origin header if appropriate
        if (isset($corsResponse['access-control-allow-origin'])) {
            $response->headers->set(
                'Access-Control-Allow-Origin',
                $corsResponse['access-control-allow-origin']
            );
        }

        // Set Access-Control-Expose-Headers header if appropriate
        if (isset($corsResponse['access-control-expose-headers'])) {
            $response->headers->set(
                'Access-Control-Expose-Headers',
                $corsResponse['access-control-expose-headers']
            );
        }

        // Set Max-Age header if appropriate
        if (isset($corsResponse['max-age'])) {
            $response->headers->set(
                'Max-Age',
                $corsResponse['max-age']
            );
        }

        return $response;
    }
}
