<?php
/**
 * Created by PhpStorm.
 * User: dederobert
 * Date: 25/08/17
 * Time: 00:38
 */

namespace DuskPHP\Core\Router;


use DuskPHP\Core\Exception\RouterException;
use GuzzleHttp\Psr7\Response;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Router
 * A router is a middleware which call an other middleware compared to the URI
 * @package DuskPHP\Core\Router
 */
class Router implements MiddlewareInterface
{
    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var array
     */
    private $namedRoutes = [];

    /**
     * Add a route associate to the get http method
     *
     * @param string $path The path's route, which match with the URI
     * @param MiddlewareInterface $middleware The associate middleware which will call if matching
     * @param string $name The route's name, use to get the URL
     * @return Route The created route
     */
    public function get(string $path,MiddlewareInterface $middleware,string $name): Route
    {
        return $this->add($path, $middleware, $name, 'GET');
    }

    /**
     * Add a route associate to the get http method
     *
     * @param string $path The path's route, which match with the URI
     * @param MiddlewareInterface $middleware The associate middleware which will call if matching
     * @param string $name The route's name, use to get the URL
     * @return Route The created route
     */
    public function post(string $path, MiddlewareInterface $middleware,string $name): Route
    {
        return $this->add($path, $middleware, $name, 'POST');
    }

    private function add(string $path, MiddlewareInterface $middleware,string $name, string $method): Route
    {
        $route = new Route($path, $middleware);
        $this->routes[$method][] = $route;

        if ($name)
            $this->namedRoutes[$name] = $route;
        else
            throw new RouterException('The name\'s route must be defined');

        return $route;
    }

    /**
     * Get the named route's URL and matching param's values
     *
     * @param string $name The route's name
     * @param array $params The param's values which will replace in the URL
     * @return string The route's URL
     * @throws RouterException when the given name doesn't match with route's name
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name]))
            throw new RouterException('No route matches this name');
        return $this->namedRoutes[$name]->getURl($params);
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * Search the route and call the associate middleware
     *
     * @param ServerRequestInterface $request The current request
     * @param DelegateInterface $delegate The next middleware
     * @return ResponseInterface The response
     * @throws RouterException when the http-method doesn't exist
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!isset($this->routes[$request->getMethod()]))
            throw new RouterException('The method '.$request->getMethod() . ', does not exist');

        foreach ($this->routes[$request->getMethod()] as $route)
            if ($route->match($request->getQueryParams()['url']))
                return $route->call($request, $delegate);

        $response = new Response();
        $response->getBody()->write('Not found');
        return $response->withStatus(404);

    }
}