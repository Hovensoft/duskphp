<?php

namespace DuskPHP\Core\Test;

use DuskPHP\Core\Router\Router;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private function makeRouter()
    {
        return new Router();
    }

    private function makeMiddleware()
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();

        return $middleware;
    }

    public function testGet()
    {
        $router = $this->makeRouter();
        $router->get('', $this->makeMiddleware(), 'test');
        $this->assertInstanceOf(Router::class, $router);
    }
}
