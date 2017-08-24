<?php
/**
 * Created by PhpStorm.
 * User: dederobert
 * Date: 25/08/17
 * Time: 00:39
 */

namespace DuskPHP\Core  \Router;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    function __construct(string $path,MiddlewareInterface $middleware)
    {
        $this->path = trim($path,'/');
        $this->middleware = $middleware;
    }

    public function with($param, string $regex)
    {
        $this->params[$param] = str_replace('(', '(?:', $regex);
        return $this;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function match(string $url): bool {
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $this->path);
        $regex = "#^$path$#i";


        if (!preg_match($regex, $url, $matches))
            return false;
        array_shift($matches);
        $this->matches = $matches;
        return true;
    }

    private function paramMatch(array $match): string {
        if (isset($this->params[$match[1]])) {
            return '(' . $this->params[$match[1]] . ')';
        }
        return '([^/]+';
    }

    public function call(ServerRequestInterface $request, DelegateInterface $delegate) {
//        if (is_string($this->middleware)) {
//            $params = explode('#', $this->middleware);
//            $controller = "App\\Controller\\" . $params[0]."Controller";
//            $controller = new $controller();
//            return call_user_func_array([$controller, $params[1], $this->matches]);
//        }
        return $this->middleware->process($request, $delegate);
//        return call_user_func_array($this->middleware, $this->matches);
    }

    public function getURL($params): string {
        $path = $this->path;
        foreach ($params as $k => $v)
            $path = str_replace(":$k", $v, $path);
        return $path;
    }

}