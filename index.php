<?php

require 'vendor/autoload.php';

use function Http\Response\send;

// Create a request using globals vars
$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
//Create a default response
$response = new \GuzzleHttp\Psr7\Response();
//Create a new dispatcher
$dispatcher = new \DuskPHP\Core\Dispatcher();

//Create middleware and pipe it in the dispatcher
//The router is a middleware which dispatch on the next middleware
$router = new \DuskPHP\Core\Router\Router();
//Add new route with the get http's method which route default path to homepage middleware
$router->get('/', new \DuskPHP\Core\HomePage(), 'homepage');

//Pipe router in the dispatcher
$dispatcher->pipe($router);

//Send to client the response
send($dispatcher->process($request, $response));
