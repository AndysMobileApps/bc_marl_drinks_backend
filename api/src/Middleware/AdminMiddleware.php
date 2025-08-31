<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AdminMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Check if user role is available (should be set by JwtMiddleware)
        $userRole = $request->getAttribute('userRole');
        
        if ($userRole !== 'admin') {
            return $this->forbiddenResponse();
        }
        
        return $handler->handle($request);
    }
    
    private function forbiddenResponse(): Response
    {
        $response = new \Slim\Psr7\Response();
        $payload = json_encode([
            'success' => false,
            'error' => 'FORBIDDEN',
            'message' => 'Admin privileges required'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(403);
    }
}

