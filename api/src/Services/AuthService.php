<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Services;

class AuthService
{
    public static function hashPin(string $pin): string
    {
        $rounds = (int)($_ENV['PIN_HASH_ROUNDS'] ?? 12);
        return password_hash($pin, PASSWORD_DEFAULT, ['cost' => $rounds]);
    }

    public static function verifyPin(string $pin, string $hash): bool
    {
        return password_verify($pin, $hash);
    }

    public static function validatePinFormat(string $pin): bool
    {
        return preg_match('/^\d{4}$/', $pin) === 1;
    }

    public static function generateResetToken(string $userId): string
    {
        // Generate secure reset token
        $data = [
            'userId' => $userId,
            'exp' => time() + 3600, // 1 hour expiry
            'type' => 'pin_reset'
        ];
        
        return base64_encode(json_encode($data) . '|' . hash_hmac('sha256', json_encode($data), $_ENV['JWT_SECRET']));
    }

    public static function validateResetToken(string $token): ?array
    {
        try {
            $decoded = base64_decode($token);
            [$data, $signature] = explode('|', $decoded, 2);
            
            $expectedSignature = hash_hmac('sha256', $data, $_ENV['JWT_SECRET']);
            
            if (!hash_equals($expectedSignature, $signature)) {
                return null;
            }
            
            $payload = json_decode($data, true);
            
            if ($payload['exp'] < time()) {
                return null; // Expired
            }
            
            return $payload;
        } catch (\Exception $e) {
            return null;
        }
    }
}


