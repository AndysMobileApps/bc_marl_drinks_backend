<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use BCMarl\Drinks\Models\Booking;
use BCMarl\Drinks\Models\Product;
use BCMarl\Drinks\Models\User;
use BCMarl\Drinks\Models\Transaction;

class BookingController extends BaseController
{
    public function createBooking(Request $request, Response $response): Response
    {
        $userId = $this->getUserIdFromRequest($request);
        $data = $request->getParsedBody();
        
        // Validate input
        if ($error = $this->validateRequired($data, ['productId', 'quantity'])) {
            return $this->jsonError($response, 'MISSING_FIELDS', $error, 400);
        }

        if ($data['quantity'] < 1) {
            return $this->jsonError($response, 'INVALID_QUANTITY', 'Quantity must be at least 1', 400);
        }

        // Find user and product
        $user = User::find($userId);
        $product = Product::find($data['productId']);

        if (!$user) {
            return $this->jsonError($response, 'USER_NOT_FOUND', 'User not found', 404);
        }

        if (!$product) {
            return $this->jsonError($response, 'PRODUCT_NOT_FOUND', 'Product not found', 404);
        }

        if (!$product->active) {
            return $this->jsonError($response, 'PRODUCT_INACTIVE', 'Product is not available', 400);
        }

        $totalCents = $product->priceCents * $data['quantity'];

        // Create booking
        $booking = Booking::create([
            'id' => $this->generateUuid(),
            'userId' => $userId,
            'productId' => $product->id,
            'quantity' => $data['quantity'],
            'unitPriceCents' => $product->priceCents,
            'totalCents' => $totalCents,
            'timestamp' => now(),
            'status' => 'booked'
        ]);

        // Create transaction
        $transaction = Transaction::create([
            'id' => $this->generateUuid(),
            'userId' => $userId,
            'type' => 'DEBIT',
            'amountCents' => $totalCents,
            'reference' => $booking->id,
            'timestamp' => now()
        ]);

        // Update user balance
        $user->balanceCents -= $totalCents;
        $user->save();

        $balanceBelowThreshold = $user->balanceCents < $user->lowBalanceThresholdCents;

        return $this->jsonSuccess($response, [
            'booking' => $booking->toArray(),
            'transaction' => $transaction->toArray(),
            'newBalance' => $user->balanceCents,
            'balanceBelowThreshold' => $balanceBelowThreshold
        ], 201);
    }

    public function getUserBookings(Request $request, Response $response): Response
    {
        $userId = $this->getUserIdFromRequest($request);
        $params = $request->getQueryParams();
        
        $includeVoided = ($params['includeVoided'] ?? 'false') === 'true';
        
        $query = Booking::where('userId', $userId)->with('product');
        
        if (!$includeVoided) {
            $query->where('status', 'booked');
        }
        
        if (!empty($params['from'])) {
            $query->where('timestamp', '>=', $params['from']);
        }
        
        if (!empty($params['to'])) {
            $query->where('timestamp', '<=', $params['to']);
        }
        
        $bookings = $query->orderBy('timestamp', 'desc')->get();
        
        return $this->jsonSuccess($response, [
            'bookings' => $bookings->toArray(),
            'total' => $bookings->count()
        ]);
    }

    public function getAllBookings(Request $request, Response $response): Response
    {
        // Admin only
        if (!$this->isAdminRequest($request)) {
            return $this->jsonError($response, 'INSUFFICIENT_PERMISSIONS', 'Admin access required', 403);
        }
        
        $params = $request->getQueryParams();
        
        $query = Booking::with(['product', 'user']);
        
        if (!empty($params['userId'])) {
            $query->where('userId', $params['userId']);
        }
        
        if (!empty($params['category'])) {
            $query->whereHas('product', function ($q) use ($params) {
                $q->where('category', $params['category']);
            });
        }
        
        if (!empty($params['from'])) {
            $query->where('timestamp', '>=', $params['from']);
        }
        
        if (!empty($params['to'])) {
            $query->where('timestamp', '<=', $params['to']);
        }
        
        $includeVoided = ($params['includeVoided'] ?? 'false') === 'true';
        if (!$includeVoided) {
            $query->where('status', 'booked');
        }
        
        $bookings = $query->orderBy('timestamp', 'desc')->get();
        
        return $this->jsonSuccess($response, [
            'bookings' => $bookings->toArray(),
            'total' => $bookings->count()
        ]);
    }

    public function voidBooking(Request $request, Response $response): Response
    {
        // Admin only
        if (!$this->isAdminRequest($request)) {
            return $this->jsonError($response, 'INSUFFICIENT_PERMISSIONS', 'Admin access required', 403);
        }
        
        $bookingId = $request->getAttribute('id');
        $adminId = $this->getUserIdFromRequest($request);
        
        $booking = Booking::find($bookingId);
        
        if (!$booking) {
            return $this->jsonError($response, 'BOOKING_NOT_FOUND', 'Booking not found', 404);
        }
        
        if ($booking->status === 'voided') {
            return $this->jsonError($response, 'ALREADY_VOIDED', 'Booking is already voided', 400);
        }
        
        // Void the booking
        $booking->void($adminId);
        
        // Create reversal transaction
        $transaction = Transaction::create([
            'id' => $this->generateUuid(),
            'userId' => $booking->userId,
            'type' => 'REVERSAL',
            'amountCents' => $booking->totalCents,
            'reference' => $booking->id,
            'timestamp' => now(),
            'enteredByAdminId' => $adminId
        ]);
        
        // Update user balance
        $user = User::find($booking->userId);
        $user->balanceCents += $booking->totalCents;
        $user->save();
        
        return $this->jsonSuccess($response, [
            'booking' => $booking->fresh()->toArray(),
            'reversalTransaction' => $transaction->toArray(),
            'newBalance' => $user->balanceCents
        ]);
    }

    public function exportBookings(Request $request, Response $response): Response
    {
        // Admin only - CSV export implementation
        if (!$this->isAdminRequest($request)) {
            return $this->jsonError($response, 'INSUFFICIENT_PERMISSIONS', 'Admin access required', 403);
        }
        
        // Implementation would generate CSV
        return $this->jsonSuccess($response, [
            'message' => 'CSV export functionality - to be implemented'
        ]);
    }
}



