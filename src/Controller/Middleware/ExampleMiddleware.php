<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 11/04/2018
 * Time: 19:31
 */

namespace Pwbox\Controller\Middleware;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ExampleMiddleware{
    public function __invoke(Request $request, Response $response, callable $next){
        $response->getBody()->write('BEFORE');
        $next($request, $response);
        $response->getBody()->write('AFTER');
        return $response;
    }
}