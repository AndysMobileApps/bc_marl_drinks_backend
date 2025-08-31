<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use BCMarl\Drinks\Services\JwtService;

class JwtMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            return $this->unauthorizedResponse();
        }
        
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorizedResponse();
        }
        
        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
        
        try {
            $payload = JwtService::validateToken($token);
            
            // Add user info to request attributes for use in controllers
            $request = $request->withAttribute('userId', $payload['userId']);
            $request = $request->withAttribute('userRole', $payload['role']);
            $request = $request->withAttribute('userEmail', $payload['email']);
            
            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse();
        }
    }
    
    private function unauthorizedResponse(): Response
    {
        $response = new \Slim\Psr7\Response();
        $payload = json_encode([
            'success' => false,
            'error' => 'UNAUTHORIZED',
            'message' => 'Invalid or missing authorization token'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(401);
    }
}

