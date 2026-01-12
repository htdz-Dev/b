<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all active products with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'variants', 'primaryImage'])
            ->where('is_active', true);

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Get featured products for homepage.
     */
    public function featured(): JsonResponse
    {
        $products = Product::with(['category', 'variants', 'primaryImage'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->take(8)
            ->get();

        return response()->json([
            'data' => $products,
        ]);
    }

    /**
     * Get a single product by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['category', 'variants', 'images'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'data' => $product,
        ]);
    }

    /**
     * Get all active categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->withCount(['activeProducts as products_count'])
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get products by category slug.
     */
    public function byCategory(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $products = Product::with(['variants', 'primaryImage'])
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->paginate(12);

        return response()->json([
            'category' => $category,
            'products' => $products,
        ]);
    }
}
