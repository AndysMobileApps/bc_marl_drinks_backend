<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;

class DatabaseConnection
{
    private static ?Capsule $capsule = null;

    public static function initialize(): void
    {
        if (self::$capsule !== null) {
            return;
        }

        self::$capsule = new Capsule();
        
        self::$capsule->addConnection([
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'mysql',
            'database' => $_ENV['DB_NAME'] ?? 'bcmarl_drinks',
            'username' => $_ENV['DB_USER'] ?? 'bcmarl_user',
            'password' => $_ENV['DB_PASS'] ?? 'bcmarl_pass_2025',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ]);

        self::$capsule->setAsGlobal();
        self::$capsule->bootEloquent();
    }

    public static function getConnection(): Capsule
    {
        if (self::$capsule === null) {
            self::initialize();
        }
        
        return self::$capsule;
    }
}
