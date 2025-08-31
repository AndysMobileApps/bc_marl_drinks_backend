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
        // Debug logging
        error_log("AdminMiddleware: Processing request to " . $request->getUri()->getPath());
        
        // Check if user role is available (should be set by JwtMiddleware)
        $userRole = $request->getAttribute('userRole');
        $userId = $request->getAttribute('userId');
        $userEmail = $request->getAttribute('userEmail');
        
        error_log("AdminMiddleware: userRole = " . ($userRole ?? 'NULL'));
        error_log("AdminMiddleware: userId = " . ($userId ?? 'NULL'));
        error_log("AdminMiddleware: userEmail = " . ($userEmail ?? 'NULL'));
        
        if ($userRole !== 'admin') {
            error_log("AdminMiddleware: Access denied - role is '$userRole', expected 'admin'");
            return $this->forbiddenResponse();
        }
        
        error_log("AdminMiddleware: Access granted for admin user");
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

