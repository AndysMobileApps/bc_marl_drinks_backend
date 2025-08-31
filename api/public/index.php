<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BCMarl\Drinks\App\Application;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Error reporting
if ($_ENV['API_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Start the application
$app = new Application();
$app->run();
