<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use BCMarl\Drinks\Models\User;

class JwtService
{
    public static function generateToken(User $user): string
    {
        $payload = [
            'iss' => 'bcmarl-drinks-api',
            'aud' => 'bcmarl-drinks-app',
            'iat' => time(),
            'exp' => time() + (int)($_ENV['JWT_EXPIRY'] ?? 86400),
            'userId' => $user->id,
            'role' => $user->role,
            'email' => $user->email
        ];

        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }

    public static function validateToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            $payload = (array) $decoded;
            
            // Handle both token structures for backward compatibility
            // Old structure: data.userId, data.role + iss="bcmarl" 
            // New structure: direct userId, role + iss="bcmarl-drinks-api"
            if (isset($payload['data']) && is_object($payload['data'])) {
                // Convert old nested structure to new flat structure
                $data = (array) $payload['data'];
                $payload['userId'] = $data['userId'] ?? null;
                $payload['role'] = $data['role'] ?? null;
                $payload['email'] = $data['email'] ?? null;
            }
            
            return $payload;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid or expired token: ' . $e->getMessage());
        }
    }

    public static function extractUserIdFromToken(string $token): ?string
    {
        try {
            $payload = self::validateToken($token);
            return $payload['userId'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function isAdminToken(string $token): bool
    {
        try {
            $payload = self::validateToken($token);
            return ($payload['role'] ?? '') === 'admin';
        } catch (\Exception $e) {
            return false;
        }
    }
}



