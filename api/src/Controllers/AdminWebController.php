<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminWebController extends BaseController
{
    public function dashboard(Request $request, Response $response): Response
    {
        $template = $this->renderTemplate('admin/dashboard', [
            'title' => 'Dashboard - BC Marl Drinks Admin',
            'page' => 'dashboard'
        ]);
        
        $response->getBody()->write($template);
        return $response->withHeader('Content-Type', 'text/html');
    }

    public function users(Request $request, Response $response): Response
    {
        $template = $this->renderTemplate('admin/users', [
            'title' => 'Benutzerverwaltung - BC Marl Drinks Admin',
            'page' => 'users'
        ]);
        
        $response->getBody()->write($template);
        return $response->withHeader('Content-Type', 'text/html');
    }

    public function products(Request $request, Response $response): Response
    {
        $template = $this->renderTemplate('admin/products', [
            'title' => 'Produktverwaltung - BC Marl Drinks Admin',
            'page' => 'products'
        ]);
        
        $response->getBody()->write($template);
        return $response->withHeader('Content-Type', 'text/html');
    }

    public function transactions(Request $request, Response $response): Response
    {
        $template = $this->renderTemplate('admin/transactions', [
            'title' => 'Transaktionen - BC Marl Drinks Admin',
            'page' => 'transactions'
        ]);
        
        $response->getBody()->write($template);
        return $response->withHeader('Content-Type', 'text/html');
    }

    public function bookings(Request $request, Response $response): Response
    {
        $template = $this->renderTemplate('admin/bookings', [
            'title' => 'Buchungen - BC Marl Drinks Admin',
            'page' => 'bookings'
        ]);
        
        $response->getBody()->write($template);
        return $response->withHeader('Content-Type', 'text/html');
    }

    public function statistics(Request $request, Response $response): Response
    {
        $template = $this->renderTemplate('admin/statistics', [
            'title' => 'Statistiken - BC Marl Drinks Admin',
            'page' => 'statistics'
        ]);
        
        $response->getBody()->write($template);
        return $response->withHeader('Content-Type', 'text/html');
    }

    public function login(Request $request, Response $response): Response
    {
        $template = $this->renderTemplate('admin/login', [
            'title' => 'Admin Login - BC Marl Drinks',
            'page' => 'login'
        ]);
        
        $response->getBody()->write($template);
        return $response->withHeader('Content-Type', 'text/html');
    }

    private function renderTemplate(string $template, array $data = []): string
    {
        // Set template variable for layout
        $data['template'] = $template;
        
        $layoutPath = __DIR__ . '/../../templates/layout.php';
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found: layout.php");
        }

        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include layout (which will include the specific template)
        include $layoutPath;
        
        // Get contents and clean buffer
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content ?: '';
    }
}
