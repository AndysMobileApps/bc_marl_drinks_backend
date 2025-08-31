<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use BCMarl\Drinks\Models\User;
use BCMarl\Drinks\Services\AuthService;
use BCMarl\Drinks\Services\JwtService;

class AuthController
{
    public function firstLogin(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Validate input
        if (empty($data['email']) || empty($data['mobile']) || empty($data['pin'])) {
            return $this->jsonError($response, 'MISSING_FIELDS', 'Email, mobile and PIN are required', 400);
        }

        // Validate PIN format
        if (!preg_match('/^\d{4}$/', $data['pin'])) {
            return $this->jsonError($response, 'INVALID_PIN_FORMAT', 'PIN must be exactly 4 digits', 400);
        }

        // Find user
        $user = User::where('email', $data['email'])
                   ->where('mobile', $data['mobile'])
                   ->first();

        if (!$user) {
            return $this->jsonError($response, 'USER_NOT_FOUND', 'User with this email and mobile not found', 400);
        }

        if ($user->locked) {
            return $this->jsonError($response, 'ACCOUNT_LOCKED', 'Account is locked. Contact administrator', 401);
        }

        if (!empty($user->pinHash)) {
            return $this->jsonError($response, 'PIN_ALREADY_SET', 'PIN already set. Use regular login', 400);
        }

        // Set PIN
        $user->pinHash = AuthService::hashPin($data['pin']);
        $user->save();

        // Generate token
        $token = JwtService::generateToken($user);

        return $this->jsonSuccess($response, [
            'token' => $token,
            'user' => $user->toArray(),
            'message' => 'PIN successfully set'
        ]);
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Validate input
        if (empty($data['email']) || empty($data['mobile']) || empty($data['pin'])) {
            return $this->jsonError($response, 'MISSING_FIELDS', 'Email, mobile and PIN are required', 400);
        }

        // Find user
        $user = User::where('email', $data['email'])
                   ->where('mobile', $data['mobile'])
                   ->first();

        if (!$user) {
            return $this->jsonError($response, 'INVALID_CREDENTIALS', 'Invalid credentials', 401);
        }

        if ($user->locked) {
            return $this->jsonError($response, 'ACCOUNT_LOCKED', 'Account is locked. Contact administrator', 401);
        }

        if (empty($user->pinHash)) {
            return $this->jsonError($response, 'PIN_NOT_SET', 'Please set your PIN first', 400);
        }

        // Verify PIN
        if (!AuthService::verifyPin($data['pin'], $user->pinHash)) {
            $user->incrementFailedAttempts();
            
            if ($user->locked) {
                return $this->jsonError($response, 'ACCOUNT_LOCKED', 'Too many failed attempts. Account locked', 401);
            }
            
            return $this->jsonError($response, 'INVALID_CREDENTIALS', 'Invalid PIN. Attempts remaining: ' . (3 - $user->failedLoginAttempts), 401);
        }

        // Reset failed attempts on successful login
        $user->resetFailedAttempts();

        // Generate token
        $token = JwtService::generateToken($user);

        return $this->jsonSuccess($response, [
            'token' => $token,
            'user' => $user->toArray()
        ]);
    }

    public function resetPinRequest(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        if (empty($data['email']) || empty($data['mobile'])) {
            return $this->jsonError($response, 'MISSING_FIELDS', 'Email and mobile are required', 400);
        }

        $user = User::where('email', $data['email'])
                   ->where('mobile', $data['mobile'])
                   ->first();

        if (!$user) {
            return $this->jsonError($response, 'USER_NOT_FOUND', 'User not found', 404);
        }

        // In production, send email here
        // For now, just return success

        return $this->jsonSuccess($response, [
            'message' => 'Reset email sent'
        ]);
    }

    public function resetPinConfirm(Request $request, Response $response): Response
    {
        // Implementation for PIN reset confirmation
        // Would verify reset token and set new PIN
        
        return $this->jsonSuccess($response, [
            'message' => 'PIN successfully reset'
        ]);
    }

    public function validateToken(Request $request, Response $response): Response
    {
        // This would be handled by JWT middleware
        // For now, simple implementation
        
        $token = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $token);
        
        try {
            $payload = JwtService::validateToken($token);
            $user = User::find($payload['userId']);
            
            if (!$user) {
                return $this->jsonError($response, 'USER_NOT_FOUND', 'User not found', 404);
            }

            return $this->jsonSuccess($response, [
                'valid' => true,
                'user' => $user->toArray()
            ]);
        } catch (\Exception $e) {
            return $this->jsonError($response, 'INVALID_TOKEN', 'Invalid or expired token', 401);
        }
    }

    private function jsonSuccess(Response $response, array $data, int $status = 200): Response
    {
        $payload = json_encode(['success' => true] + $data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function jsonError(Response $response, string $error, string $message, int $status = 400): Response
    {
        $payload = json_encode([
            'success' => false,
            'error' => $error,
            'message' => $message
        ]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
