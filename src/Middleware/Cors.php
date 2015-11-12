<?php
/**
 * CORS middleware
 *
 * @package     markinjapan/laravel-cors
 * @author      Mark Prosser <markinjapan@users.noreply.github.com>
 * @copyright   Copyright (c) Mark Prosser
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link        https://github.com/markinjapan/laravel-cors
 */

namespace MarkInJapan\LaravelCors\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * CORS middleware class
 */
class Cors
{
    /** @var array Allow origins */
    protected $allow_origins = [];

    /** @var array Allow headers */
    protected $allow_headers = [];

    /** @var bool Allow credentials? */
    protected $allow_credentials = false;

    /** @var array Allow methods */
    protected $allow_methods = [];

    /** @var array Expose headers */
    protected $expose_headers = [];

    /** @var int Number of seconds to cache OPTIONS response */
    protected $max_age = 0;

    /**
     * Class constructor
     */
    public function __construct(array $config)
    {
        // Set class properties from config (with basic typecasting)
        $this->allow_origins     = (array) $config['allow_origins'];
        $this->allow_headers     = (array) $config['allow_headers'];
        $this->allow_credentials = (bool)  $config['allow_credentials'];
        $this->allow_methods     = (array) $config['allow_methods'];
        $this->expose_headers    = (array) $config['expose_headers'];
        $this->max_age           = (int)   $config['max_age'];
    }

    /**
     * Handle CORS request
     *
     * @see    http://www.html5rocks.com/en/tutorials/cors/
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Get "Origin" header
        $origin = $request->header('origin');

        // If NOT a CORS request ("Origin" header NOT in request)
        if (empty($origin)) {
            /**
             * Pass request on and collect response
             */
            $response = $next($request);
        }
        // Else; CORS request
        else {
            // Get "Access-Control-Request-Method" header
            $request_method = $request->header('access-control-request-method');

            // If CORS "preflight" request (OPTIONS method and "Access-Control-Request-Method" header set)
            if ($request->isMethod('options') && !empty($request_method)) {
                // Create new response
                $response = new Response();

                // If request method NOT in whitelist
                if (!in_array($request_method, $this->allow_methods)) {
                    // Not a valid preflight request
                    return $response;
                }

                // If any request headers NOT in whitelist
                $request_headers = strtolower($request->header('access-control-request-headers'));
                if (!empty($request_headers)) {
                    if (array_diff(explode(', ', $request_headers), $this->allow_headers) !== []) {
                        // Not a valid preflight request
                        return $response;
                    }
                }

                // Set "Allow-Methods" header
                $response->header('Access-Control-Allow-Methods', implode(', ', $this->allow_methods));

                // Only set "Allow-Headers" header if "Request-Headers" header was in request
                if (!empty($request_headers)) {
                    $response->header('Access-Control-Allow-Headers', implode(', ', $this->allow_headers));
                }

                // Optionally set "Max-Age" cache header
                if (is_int($this->max_age) && $this->max_age > 0) {
                    $response->header('Access-Control-Max-Age', $this->max_age);
                }
            }
            // Else; not a CORS "preflight" request
            else {
                /**
                 * Pass on request and collect response
                 */
                $response = $next($request);

                // Optionally expose headers
                if ($this->expose_headers !== [] && is_array($this->expose_headers)) {
                    $response->header('Access-Control-Expose-Headers', implode(', ', $this->expose_headers));
                }
            }

            // If origin in whitelist, send Access-Control-Allow-Origin header
            if (in_array($origin, $this->allow_origins)) {
                $response->header('Access-Control-Allow-Origin', $origin);
            }

            // Optionally allow credentials
            if ($this->allow_credentials === true) {
                $response->header('Access-Control-Allow-Credentials', 'true');
            }
        }

        return $response;
    }
}