<?php
declare(strict_types=1);

namespace BCMarl\Drinks\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use BCMarl\Drinks\Models\Product;
use BCMarl\Drinks\Models\Favorite;

class ProductController extends BaseController
{
    public function getProducts(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $category = $params['category'] ?? null;
        $active = $params['active'] ?? 'true';
        
        $query = Product::query();
        
        if ($category) {
            $query->where('category', $category);
        }
        
        if ($active === 'true') {
            $query->where('active', true);
        }
        
        $products = $query->orderBy('name')->get();
        
        return $this->jsonSuccess($response, [
            'products' => $products->toArray(),
            'total' => $products->count()
        ]);
    }

    public function getProduct(Request $request, Response $response): Response
    {
        $productId = $request->getAttribute('id');
        
        $product = Product::find($productId);
        
        if (!$product) {
            return $this->jsonError($response, 'PRODUCT_NOT_FOUND', 'Product not found', 404);
        }
        
        return $this->jsonSuccess($response, [
            'product' => $product->toArray()
        ]);
    }

    public function getFavorites(Request $request, Response $response): Response
    {
        // Get userId from JWT token (would be middleware in production)
        $userId = $this->getUserIdFromRequest($request);
        
        $favorites = Favorite::where('userId', $userId)
            ->with('product')
            ->get()
            ->pluck('product')
            ->where('active', true);
        
        return $this->jsonSuccess($response, [
            'products' => $favorites->values()->toArray()
        ]);
    }

    public function addFavorite(Request $request, Response $response): Response
    {
        $userId = $this->getUserIdFromRequest($request);
        $data = $request->getParsedBody();
        
        if (empty($data['productId'])) {
            return $this->jsonError($response, 'MISSING_PRODUCT_ID', 'Product ID is required', 400);
        }
        
        // Check if already favorite
        $existing = Favorite::where('userId', $userId)
                           ->where('productId', $data['productId'])
                           ->first();
        
        if ($existing) {
            return $this->jsonError($response, 'ALREADY_FAVORITE', 'Product is already in favorites', 409);
        }
        
        Favorite::create([
            'userId' => $userId,
            'productId' => $data['productId']
        ]);
        
        return $this->jsonSuccess($response, [
            'message' => 'Favorite added'
        ], 201);
    }

    public function removeFavorite(Request $request, Response $response): Response
    {
        $userId = $this->getUserIdFromRequest($request);
        $productId = $request->getAttribute('productId');
        
        $deleted = Favorite::where('userId', $userId)
                          ->where('productId', $productId)
                          ->delete();
        
        if ($deleted === 0) {
            return $this->jsonError($response, 'FAVORITE_NOT_FOUND', 'Favorite not found', 404);
        }
        
        return $this->jsonSuccess($response, [
            'message' => 'Favorite removed'
        ]);
    }

    public function createProduct(Request $request, Response $response): Response
    {
        // Admin only - would be checked by middleware
        $data = $request->getParsedBody();
        
        $product = Product::create([
            'id' => $this->generateUuid(),
            'name' => $data['name'],
            'icon' => $data['icon'],
            'priceCents' => $data['priceCents'],
            'category' => $data['category']
        ]);
        
        return $this->jsonSuccess($response, [
            'product' => $product->toArray()
        ], 201);
    }

    public function updateProduct(Request $request, Response $response): Response
    {
        // Admin only - would be checked by middleware
        $productId = $request->getAttribute('id');
        $data = $request->getParsedBody();
        
        $product = Product::find($productId);
        
        if (!$product) {
            return $this->jsonError($response, 'PRODUCT_NOT_FOUND', 'Product not found', 404);
        }
        
        $product->update($data);
        
        return $this->jsonSuccess($response, [
            'product' => $product->fresh()->toArray()
        ]);
    }
}

