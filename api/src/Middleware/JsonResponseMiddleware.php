<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JsonResponseMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        
        // Add JSON content type with UTF-8 charset if not already set
        if (!$response->hasHeader('Content-Type')) {
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        }
        
        return $response;
    }
}


