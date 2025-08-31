<?php
declare(strict_types=1);

namespace BCMarl\Drinks\App;

use Slim\Factory\AppFactory;
use Slim\App;
use BCMarl\Drinks\Middleware\CorsMiddleware;
use BCMarl\Drinks\Middleware\JsonResponseMiddleware;
use BCMarl\Drinks\Middleware\JwtMiddleware;
use BCMarl\Drinks\Middleware\AdminMiddleware;
use BCMarl\Drinks\Database\DatabaseConnection;
use BCMarl\Drinks\Controllers\AuthController;
use BCMarl\Drinks\Controllers\UserController;
use BCMarl\Drinks\Controllers\ProductController;
use BCMarl\Drinks\Controllers\BookingController;
use BCMarl\Drinks\Controllers\StatsController;
use BCMarl\Drinks\Controllers\AdminWebController;

class Application
{
    private App $app;

    public function __construct()
    {
        // Create Slim app
        $this->app = AppFactory::create();
        
        // Add error middleware
        $this->app->addErrorMiddleware(true, true, true);
        
        // Setup database
        DatabaseConnection::initialize();
        
        // Add middleware
        $this->setupMiddleware();
        
        // Setup routes
        $this->setupRoutes();
    }

    private function setupMiddleware(): void
    {
        // CORS middleware
        $this->app->add(new CorsMiddleware());
        
        // JSON response middleware
        $this->app->add(new JsonResponseMiddleware());
        
        // Parse JSON bodies
        $this->app->addBodyParsingMiddleware();
    }

    private function setupRoutes(): void
    {
        $app = $this->app;
        
        // Root welcome endpoint
        $app->get('/', function ($request, $response) {
            $response->getBody()->write(json_encode([
                'message' => 'BC Marl Drinks API',
                'version' => '1.0.0',
                'status' => 'running',
                'timestamp' => date('c'),
                'endpoints' => [
                    'health' => '/v1/health',
                    'auth' => '/v1/auth/*',
                    'products' => '/v1/products',
                    'documentation' => 'See API specification for full endpoint list'
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        });
        
        // API version prefix
        $app->group('/v1', function ($group) {
            
            // Health check
            $group->get('/health', function ($request, $response) {
                $response->getBody()->write(json_encode([
                    'status' => 'healthy',
                    'timestamp' => date('c'),
                    'version' => '1.0.0'
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            });
            
            // Auth routes
            $group->group('/auth', function ($auth) {
                $auth->post('/first-login', [AuthController::class, 'firstLogin']);
                $auth->post('/login', [AuthController::class, 'login']);
                $auth->post('/reset-pin-request', [AuthController::class, 'resetPinRequest']);
                $auth->post('/reset-pin-confirm', [AuthController::class, 'resetPinConfirm']);
                $auth->get('/validate', [AuthController::class, 'validateToken']);
            });
            
            // User routes (require auth)
            $group->group('/me', function ($me) {
                $me->get('', [UserController::class, 'getProfile']);
                $me->patch('/threshold', [UserController::class, 'updateThreshold']);
                $me->patch('/pin', [UserController::class, 'changePin']);
                $me->get('/favorites', [ProductController::class, 'getFavorites']);
                $me->post('/favorites', [ProductController::class, 'addFavorite']);
                $me->delete('/favorites/{productId}', [ProductController::class, 'removeFavorite']);
                $me->get('/bookings', [BookingController::class, 'getUserBookings']);
                $me->get('/transactions', [UserController::class, 'getUserTransactions']);
            })->add(new JwtMiddleware());
            
            // Product routes
            $group->get('/products', [ProductController::class, 'getProducts']);
            $group->get('/products/{id}', [ProductController::class, 'getProduct']);
            
            // Booking routes
            $group->post('/bookings', [BookingController::class, 'createBooking'])
                ->add(new JwtMiddleware());
            $group->get('/bookings', [BookingController::class, 'getAllBookings']) // Admin only
                ->add(new AdminMiddleware())->add(new JwtMiddleware());
            $group->post('/bookings/{id}/void', [BookingController::class, 'voidBooking']) // Admin only
                ->add(new AdminMiddleware())->add(new JwtMiddleware());
            
            // Admin routes
            $group->group('/admin', function ($admin) {
                $admin->get('/users', [UserController::class, 'getAllUsers']);
                $admin->post('/users', [UserController::class, 'createUser']);
                $admin->post('/users/{id}/unlock', [UserController::class, 'unlockUser']);
                $admin->post('/users/{id}/deposit', [UserController::class, 'depositMoney']);
                $admin->post('/products', [ProductController::class, 'createProduct']);
                $admin->patch('/products/{id}', [ProductController::class, 'updateProduct']);
            })->add(new AdminMiddleware())->add(new JwtMiddleware());
            
            // Statistics routes
            $group->group('/stats', function ($stats) {
                $stats->get('/top-products', [StatsController::class, 'getTopProducts']);
                $stats->get('/revenue', [StatsController::class, 'getRevenue']);
                $stats->get('/categories', [StatsController::class, 'getCategoryBreakdown']);
            })->add(new AdminMiddleware())->add(new JwtMiddleware());
            
            // Export routes
            $group->get('/export/bookings.csv', [BookingController::class, 'exportBookings']) // Admin only
                ->add(new AdminMiddleware())->add(new JwtMiddleware());
            $group->get('/export/transactions.csv', [UserController::class, 'exportTransactions']) // Admin only
                ->add(new AdminMiddleware())->add(new JwtMiddleware());
        });
        
        // Admin Web Interface Routes
        $app->group('/admin', function ($admin) {
            $admin->get('/login', [AdminWebController::class, 'login']);
            $admin->get('', [AdminWebController::class, 'dashboard']);
            $admin->get('/', [AdminWebController::class, 'dashboard']);
            $admin->get('/users', [AdminWebController::class, 'users']);
            $admin->get('/products', [AdminWebController::class, 'products']);
            $admin->get('/transactions', [AdminWebController::class, 'transactions']);
            $admin->get('/bookings', [AdminWebController::class, 'bookings']);
            $admin->get('/statistics', [AdminWebController::class, 'statistics']);
        });
    }

    public function run(): void
    {
        $this->app->run();
    }
}

