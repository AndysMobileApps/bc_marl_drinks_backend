<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use BCMarl\Drinks\Models\User;
use BCMarl\Drinks\Models\Transaction;
use BCMarl\Drinks\Services\AuthService;

class UserController extends BaseController
{
    public function getProfile(Request $request, Response $response): Response
    {
        $userId = $this->getUserIdFromRequest($request);
        $user = User::find($userId);
        
        if (!$user) {
            return $this->jsonError($response, 'USER_NOT_FOUND', 'User not found', 404);
        }
        
        return $this->jsonSuccess($response, [
            'user' => $user->toArray()
        ]);
    }

    public function updateThreshold(Request $request, Response $response): Response
    {
        $userId = $this->getUserIdFromRequest($request);
        $data = $request->getParsedBody();
        
        if (!isset($data['lowBalanceThresholdCents'])) {
            return $this->jsonError($response, 'MISSING_FIELD', 'lowBalanceThresholdCents is required', 400);
        }
        
        $user = User::find($userId);
        if (!$user) {
            return $this->jsonError($response, 'USER_NOT_FOUND', 'User not found', 404);
        }
        
        $user->lowBalanceThresholdCents = (int)$data['lowBalanceThresholdCents'];
        $user->save();
        
        return $this->jsonSuccess($response, [
            'user' => $user->fresh()->toArray()
        ]);
    }

    public function changePin(Request $request, Response $response): Response
    {
        $userId = $this->getUserIdFromRequest($request);
        $data = $request->getParsedBody();
        
        if ($error = $this->validateRequired($data, ['currentPin', 'newPin'])) {
            return $this->jsonError($response, 'MISSING_FIELDS', $error, 400);
        }
        
        if (!AuthService::validatePinFormat($data['newPin'])) {
            return $this->jsonError($response, 'INVALID_PIN_FORMAT', 'PIN must be exactly 4 digits', 400);
        }
        
        $user = User::find($userId);
        if (!$user) {
            return $this->jsonError($response, 'USER_NOT_FOUND', 'User not found', 404);
        }
        
        if (!AuthService::verifyPin($data['currentPin'], $user->pinHash)) {
            return $this->jsonError($response, 'INVALID_CURRENT_PIN', 'Current PIN is incorrect', 400);
        }
        
        $user->pinHash = AuthService::hashPin($data['newPin']);
        $user->save();
        
        return $this->jsonSuccess($response, [
            'message' => 'PIN successfully changed'
        ]);
    }

    public function getAllUsers(Request $request, Response $response): Response
    {
        // Admin only
        if (!$this->isAdminRequest($request)) {
            return $this->jsonError($response, 'INSUFFICIENT_PERMISSIONS', 'Admin access required', 403);
        }
        
        $params = $request->getQueryParams();
        $query = User::query();
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('firstName', 'LIKE', "%{$search}%")
                  ->orWhere('lastName', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        if (isset($params['locked'])) {
            $query->where('locked', $params['locked'] === 'true');
        }
        
        $users = $query->orderBy('lastName')->orderBy('firstName')->get();
        
        return $this->jsonSuccess($response, [
            'users' => $users->toArray(),
            'total' => $users->count()
        ]);
    }

    public function createUser(Request $request, Response $response): Response
    {
        // Admin only
        if (!$this->isAdminRequest($request)) {
            return $this->jsonError($response, 'INSUFFICIENT_PERMISSIONS', 'Admin access required', 403);
        }
        
        $data = $request->getParsedBody();
        
        if ($error = $this->validateRequired($data, ['firstName', 'lastName', 'email', 'mobile'])) {
            return $this->jsonError($response, 'MISSING_FIELDS', $error, 400);
        }
        
        // Check for duplicates
        $existing = User::where('email', $data['email'])
                       ->orWhere('mobile', $data['mobile'])
                       ->first();
        
        if ($existing) {
            return $this->jsonError($response, 'USER_EXISTS', 'User with this email or mobile already exists', 409);
        }
        
        $user = User::create([
            'id' => $this->generateUuid(),
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'role' => $data['role'] ?? 'user',
            'balanceCents' => 0,
            'lowBalanceThresholdCents' => 500
        ]);
        
        return $this->jsonSuccess($response, [
            'user' => $user->toArray()
        ], 201);
    }

    public function unlockUser(Request $request, Response $response): Response
    {
        // Admin only
        if (!$this->isAdminRequest($request)) {
            return $this->jsonError($response, 'INSUFFICIENT_PERMISSIONS', 'Admin access required', 403);
        }
        
        $userId = $request->getAttribute('id');
        $user = User::find($userId);
        
        if (!$user) {
            return $this->jsonError($response, 'USER_NOT_FOUND', 'User not found', 404);
        }
        
        $user->unlock();
        
        return $this->jsonSuccess($response, [
            'user' => $user->fresh()->toArray()
        ]);
    }

    public function depositMoney(Request $request, Response $response): Response
    {
        // Admin only
        if (!$this->isAdminRequest($request)) {
            return $this->jsonError($response, 'INSUFFICIENT_PERMISSIONS', 'Admin access required', 403);
        }
        
        $userId = $request->getAttribute('id');
        $adminId = $this->getUserIdFromRequest($request);
        $data = $request->getParsedBody();
        
        if (empty($data['amountCents']) || $data['amountCents'] <= 0) {
            return $this->jsonError($response, 'INVALID_AMOUNT', 'Amount must be greater than 0', 400);
        }
        
        $user = User::find($userId);
        if (!$user) {
            return $this->jsonError($response, 'USER_NOT_FOUND', 'User not found', 404);
        }
        
        // Create deposit transaction
        $transaction = Transaction::create([
            'id' => $this->generateUuid(),
            'userId' => $userId,
            'type' => 'DEPOSIT',
            'amountCents' => $data['amountCents'],
            'timestamp' => now(),
            'enteredByAdminId' => $adminId
        ]);
        
        // Update user balance
        $user->balanceCents += $data['amountCents'];
        $user->save();
        
        return $this->jsonSuccess($response, [
            'transaction' => $transaction->toArray(),
            'newBalance' => $user->balanceCents
        ], 201);
    }

    public function getUserTransactions(Request $request, Response $response): Response
    {
        $userId = $this->getUserIdFromRequest($request);
        $params = $request->getQueryParams();
        
        $query = Transaction::where('userId', $userId);
        
        if (!empty($params['type'])) {
            $query->where('type', $params['type']);
        }
        
        if (!empty($params['from'])) {
            $query->where('timestamp', '>=', $params['from']);
        }
        
        if (!empty($params['to'])) {
            $query->where('timestamp', '<=', $params['to']);
        }
        
        $transactions = $query->orderBy('timestamp', 'desc')->get();
        
        return $this->jsonSuccess($response, [
            'transactions' => $transactions->toArray(),
            'total' => $transactions->count()
        ]);
    }

    public function exportTransactions(Request $request, Response $response): Response
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

