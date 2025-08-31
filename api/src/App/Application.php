<?php
declare(strict_types=1);

namespace BCMarl\Drinks\App;

use Slim\Factory\AppFactory;
use Slim\App;
use BCMarl\Drinks\Middleware\CorsMiddleware;
use BCMarl\Drinks\Middleware\JsonResponseMiddleware;
use BCMarl\Drinks\Database\DatabaseConnection;
use BCMarl\Drinks\Controllers\AuthController;
use BCMarl\Drinks\Controllers\UserController;
use BCMarl\Drinks\Controllers\ProductController;
use BCMarl\Drinks\Controllers\BookingController;
use BCMarl\Drinks\Controllers\StatsController;

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
        
        // API version prefix
        $app->group('/v1', function ($group) {
            
            // Health check
            $group->get('/health', function ($request, $response) {
                $response->getBody()->write(json_encode([
                    'status' => 'healthy',
                    'timestamp' => date('c'),
                    'version' => '1.0.0'
                ]));
                return $response->withHeader('Content-Type', 'application/json');
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
            }); // ->add(JwtMiddleware::class);
            
            // Product routes
            $group->get('/products', [ProductController::class, 'getProducts']);
            $group->get('/products/{id}', [ProductController::class, 'getProduct']);
            
            // Booking routes
            $group->post('/bookings', [BookingController::class, 'createBooking']);
            $group->get('/bookings', [BookingController::class, 'getAllBookings']); // Admin only
            $group->post('/bookings/{id}/void', [BookingController::class, 'voidBooking']); // Admin only
            
            // Admin routes
            $group->group('/admin', function ($admin) {
                $admin->get('/users', [UserController::class, 'getAllUsers']);
                $admin->post('/users', [UserController::class, 'createUser']);
                $admin->post('/users/{id}/unlock', [UserController::class, 'unlockUser']);
                $admin->post('/users/{id}/deposit', [UserController::class, 'depositMoney']);
                $admin->post('/products', [ProductController::class, 'createProduct']);
                $admin->patch('/products/{id}', [ProductController::class, 'updateProduct']);
            }); // ->add(AdminMiddleware::class);
            
            // Statistics routes
            $group->group('/stats', function ($stats) {
                $stats->get('/top-products', [StatsController::class, 'getTopProducts']);
                $stats->get('/revenue', [StatsController::class, 'getRevenue']);
                $stats->get('/categories', [StatsController::class, 'getCategoryBreakdown']);
            }); // ->add(AdminMiddleware::class);
            
            // Export routes
            $group->get('/export/bookings.csv', [BookingController::class, 'exportBookings']); // Admin only
            $group->get('/export/transactions.csv', [UserController::class, 'exportTransactions']); // Admin only
        });
    }

    public function run(): void
    {
        $this->app->run();
    }
}
