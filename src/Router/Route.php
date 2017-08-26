<?php
/**
 * Created by PhpStorm.
 * User: dederobert
 * Date: 25/08/17
 * Time: 00:39.
 */

namespace DuskPHP\Core\Router;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Route
 * A route associate a path with a middleware
 * The router search the matching route with the URI and call the middleware.
 */
class Route
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var MiddlewareInterface
     */
    private $middleware;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var array
     */
    private $matches = [];

    public function __construct(string $path, MiddlewareInterface $middleware)
    {
        $this->path = trim($path, '/');
        $this->middleware = $middleware;
    }

    /**
     * Add a regex to the path's parameters.
     *
     * eg: path: "/posts/:id-:slug" with id a integer
     * and slug a string, id's regex is "[0-9]+" and slug's
     * regex: "[a-z\-0-9]+".
     *
     * @param string $param The parameter's name
     * @param string $regex The associate regex which use to identify parameter
     *
     * @return $this The Route
     */
    public function with(string $param, string $regex)
    {
        $this->params[$param] = str_replace('(', '(?:', $regex);

        return $this;
    }

    /**
     * Check if the route match with the current URI.
     *
     * @param string $url The URI
     *
     * @return bool True if match
     */
    public function match(string $url): bool
    {
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $this->path);
        $regex = "#^$path$#i";

        if (!preg_match($regex, $url, $matches)) {
            return false;
        }
        array_shift($matches);
        $this->matches = $matches;

        return true;
    }

    private function paramMatch(array $match): string
    {
        if (isset($this->params[$match[1]])) {
            return '(' . $this->params[$match[1]] . ')';
        }

        return '([^/]+';
    }

    /**
     * Call the associate middleware.
     *
     * @param ServerRequestInterface $request  The current request
     * @param DelegateInterface      $delegate The delegate ware which call the next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface The response
     */
    public function call(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $this->middleware->process($request, $delegate);
    }

    public function getURL($params): string
    {
        $path = $this->path;
        foreach ($params as $k => $v) {
            $path = str_replace(":$k", $v, $path);
        }

        return $path;
    }
}
