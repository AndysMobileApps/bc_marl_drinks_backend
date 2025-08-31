<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;

abstract class BaseController
{
    protected function jsonSuccess(Response $response, array $data, int $status = 200): Response
    {
        $payload = json_encode(['success' => true] + $data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    protected function jsonError(Response $response, string $error, string $message, int $status = 400): Response
    {
        $payload = json_encode([
            'success' => false,
            'error' => $error,
            'message' => $message
        ]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    protected function getUserIdFromRequest(Request $request): ?string
    {
        // Extract user ID from JWT token
        $token = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $token);
        
        try {
            $payload = \BCMarl\Drinks\Services\JwtService::validateToken($token);
            return $payload['userId'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function isAdminRequest(Request $request): bool
    {
        $token = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $token);
        
        return \BCMarl\Drinks\Services\JwtService::isAdminToken($token);
    }

    protected function generateUuid(): string
    {
        return Uuid::uuid4()->toString();
    }

    protected function validateRequired(array $data, array $required): ?string
    {
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return "Field '{$field}' is required";
            }
        }
        return null;
    }
}
