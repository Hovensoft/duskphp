<?php

namespace DuskPHP\Core;

use GuzzleHttp\Psr7\Response;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Dispacher
 * A dispatcher call all saved middleware to create a response.
 */
class Dispatcher implements DelegateInterface
{
    /**
     * @var array
     */
    private $middlewares = [];

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var Response
     */
    private $response;

    public function __construct()
    {
        $this->response = new Response();
    }

    /**
     * Save a new middleware.
     *
     * @param callable|MiddlewareInterface $middleware
     */
    public function pipe($middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Dispatch the next available middleware and return the response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request)
    {
        $middleware = $this->getMiddleware();
        ++$this->index;

        //When all saved middleware were called
        if (null === $middleware) {
            return $this->response;
        }
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }
    }

    private function getMiddleware()
    {
        if (isset($this->middlewares[$this->index])) {
            return $this->middlewares[$this->index];
        }

        return null;
    }
}
