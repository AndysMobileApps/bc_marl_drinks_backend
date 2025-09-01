<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use BCMarl\Drinks\Models\Booking;
use BCMarl\Drinks\Models\Product;
use Illuminate\Support\Facades\DB;

class StatsController extends BaseController
{
    public function getTopProducts(Request $request, Response $response): Response
    {
        // Admin only
        if (!$this->isAdminRequest($request)) {
            return $this->jsonError($response, 'INSUFFICIENT_PERMISSIONS', 'Admin access required', 403);
        }
        
        $params = $request->getQueryParams();
        $limit = (int)($params['limit'] ?? 10);
        
        $query = Booking::where('status', 'booked')
                       ->with('product');
        
        if (!empty($params['from'])) {
            $query->where('timestamp', '>=', $params['from']);
        }
        
        if (!empty($params['to'])) {
            $query->where('timestamp', '<=', $params['to']);
        }
        
        $bookings = $query->get();
        
        // Group by product and calculate totals
        $productStats = [];
        foreach ($bookings as $booking) {
            $productId = $booking->productId;
            if (!isset($productStats[$productId])) {
                $productStats[$productId] = [
                    'product' => $booking->product,
                    'quantitySold' => 0,
                    'totalRevenueCents' => 0
                ];
            }
            $productStats[$productId]['quantitySold'] += $booking->quantity;
            $productStats[$productId]['totalRevenueCents'] += $booking->totalCents;
        }
        
        // Sort by quantity sold and limit
        usort($productStats, function ($a, $b) {
            return $b['quantitySold'] <=> $a['quantitySold'];
        });
        
        $topProducts = array_slice($productStats, 0, $limit);
        
        return $this->jsonSuccess($response, [
            'topProducts' => $topProducts
        ]);
    }

    public function getRevenue(Request $request, Response $response): Response
    {
        // Admin only
        if (!$this->isAdminRequest($request)) {
            return $this->jsonError($response, 'INSUFFICIENT_PERMISSIONS', 'Admin access required', 403);
        }
        
        $params = $request->getQueryParams();
        $groupBy = $params['groupBy'] ?? 'day';
        
        $query = Booking::where('status', 'booked');
        
        if (!empty($params['from'])) {
            $query->where('timestamp', '>=', $params['from']);
        }
        
        if (!empty($params['to'])) {
            $query->where('timestamp', '<=', $params['to']);
        }
        
        $bookings = $query->get();
        
        $totalRevenueCents = $bookings->sum('totalCents');
        $bookingCount = $bookings->count();
        
        // Group by date (simplified - would need proper date grouping in production)
        $revenue = [];
        foreach ($bookings->groupBy(function ($booking) {
            return $booking->timestamp->format('Y-m-d');
        }) as $date => $dayBookings) {
            $revenue[] = [
                'date' => $date,
                'revenueCents' => $dayBookings->sum('totalCents'),
                'bookingCount' => $dayBookings->count()
            ];
        }
        
        return $this->jsonSuccess($response, [
            'revenue' => $revenue,
            'totalRevenueCents' => $totalRevenueCents,
            'totalBookingCount' => $bookingCount
        ]);
    }

    public function getCategoryBreakdown(Request $request, Response $response): Response
    {
        // Admin only
        if (!$this->isAdminRequest($request)) {
            return $this->jsonError($response, 'INSUFFICIENT_PERMISSIONS', 'Admin access required', 403);
        }
        
        $params = $request->getQueryParams();
        
        $query = Booking::where('status', 'booked')
                       ->with('product');
        
        if (!empty($params['from'])) {
            $query->where('timestamp', '>=', $params['from']);
        }
        
        if (!empty($params['to'])) {
            $query->where('timestamp', '<=', $params['to']);
        }
        
        $bookings = $query->get();
        
        // Group by category
        $categoryStats = [];
        $totalRevenue = $bookings->sum('totalCents');
        
        foreach ($bookings->groupBy('product.category') as $category => $categoryBookings) {
            $revenueCents = $categoryBookings->sum('totalCents');
            $quantitySold = $categoryBookings->sum('quantity');
            
            $categoryStats[] = [
                'category' => $category,
                'quantitySold' => $quantitySold,
                'revenueCents' => $revenueCents,
                'percentage' => $totalRevenue > 0 ? round(($revenueCents / $totalRevenue) * 100, 2) : 0
            ];
        }
        
        return $this->jsonSuccess($response, [
            'categories' => $categoryStats
        ]);
    }
}



