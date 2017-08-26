<?php

namespace DuskPHP\Core;

use GuzzleHttp\Psr7\Response;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HomePage
 * Very simple middleware use as example.
 */
class HomePage implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * Print a basic HTML page
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
//        $delegate->process($request);

        $response = new Response();
        $body = $response->getBody();
        $body->write(file_get_contents(dirname(__DIR__) . '/assets/homepage.html'));

        return $response->withBody($body);
    }
}
