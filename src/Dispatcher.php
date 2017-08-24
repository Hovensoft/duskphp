<?php

namespace DuskPHP\Core;

use GuzzleHttp\Psr7\Response;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Dispacher
 * @package DuskPHP
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

    private $response;

    function __construct()
    {
        $this->response = new Response();
    }

    /**
     * Save a new middleware
     *
     * @param callable|MiddlewareInterface $middleware
     */
    public function pipe($middleware)
    {
        $this->middlewares[] =$middleware;
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
        $this->index++;
        if (is_null($middleware))
            return $this->response;
        if(is_callable($middleware))
            return $middleware($request, $this->response, [$this, 'process']);
        if ($middleware instanceof MiddlewareInterface)
            return $middleware->process($request, $this);
    }

    private function getMiddleware()  {
        if (isset($this->middlewares[$this->index]))
            return $this->middlewares[$this->index];
        return null;
    }

}