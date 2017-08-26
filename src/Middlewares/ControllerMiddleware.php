<?php
/**
 * Created by PhpStorm.
 * User: dederobert
 * Date: 26/08/17
 * Time: 12:53.
 */

namespace DuskPHP\Core\Middlewares;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     */
    private $action;

    private $response;

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $this->response = $delegate->process($request);
        $action = $this->action;

        return $action();
    }

    public function setAction(callable $action)
    {
        $this->action = $action;
    }
}
