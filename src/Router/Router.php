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

class Router implements MiddlewareInterface
{
    /**
     * @var array
     */
    private $routes = [];

    private $namedRoutes = [];

    public function get(string $path,MiddlewareInterface $middleware,string $name): Route
    {
        return $this->add($path, $middleware, $name, 'GET');
    }

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

//    public function run(){
//        if (!isset($this->routes[$_SERVER['REQUEST_METHOD']])) {
//            throw new RouterException('REQUEST_METHOD does not exist');
//        }
//
//        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
//            if ($route->match($this->url)) {
//                return $route->call();
//            }
//        }
//
//        throw new RouterException('No matching routes');
//    }

    public function url($name, $params = []){
        if (!isset($this->namedRoutes[$name]))
            throw new RouterException('No route matches this name');
        return $this->namedRoutes[$name]->getURl($params);
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     * @throws RouterException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!isset($this->routes[$request->getMethod()]))
            throw new RouterException($request->getMethod() . ' does not exist');

        foreach ($this->routes[$request->getMethod()] as $route)
            if ($route->match($request->getQueryParams()['url']))
                return $route->call($request, $delegate);

        $response = new Response();
        $response->getBody()->write('Not found');
        return $response->withStatus(404);

    }
}