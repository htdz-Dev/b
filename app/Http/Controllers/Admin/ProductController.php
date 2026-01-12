<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category', 'images')
            ->latest()
            ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        $product = Product::create($validated);

        // Handle variants
        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                if (!empty($variant['size']) || !empty($variant['color'])) {
                    $product->variants()->create([
                        'size' => $variant['size'] ?? null,
                        'color' => $variant['color'] ?? null,
                        'stock_quantity' => $variant['stock_quantity'] ?? 0,
                        'price_adjustment' => $variant['price_adjustment'] ?? 0,
                        'sku' => strtoupper(Str::random(8)),
                    ]);
                }
            }
        }

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $isFirst = true;
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'is_primary' => $isFirst,
                ]);
                $isFirst = false;
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit créé avec succès!');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('variants', 'images');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        // Check if POST was truncated (happens when upload exceeds post_max_size)
        if (empty($request->all()) || (!$request->has('name') && !$request->hasFile('images'))) {
            return redirect()->back()
                ->with('error', 'Upload failed: files too large. Maximum total size is 8MB.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        $product->update($validated);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $isFirst = $product->images()->count() === 0;
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'is_primary' => $isFirst,
                ]);
                $isFirst = false;
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit mis à jour!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Produit supprimé!');
    }

    public function deleteImage(Product $product, $imageId)
    {
        $image = $product->images()->findOrFail($imageId);

        // Delete file from storage
        \Storage::disk('public')->delete($image->path);

        // If this was primary, set next image as primary
        $wasPrimary = $image->is_primary;
        $image->delete();

        if ($wasPrimary && $product->images()->count() > 0) {
            $product->images()->first()->update(['is_primary' => true]);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Image supprimée!');
    }

    public function updateImageColor(Request $request, $imageId)
    {
        $request->validate([
            'color' => 'nullable|string|max:255',
        ]);

        // Find image directly (we don't need product context for this simple update)
        // Adjust model path if needed
        $image = \App\Models\ProductImage::findOrFail($imageId);

        $image->update([
            'color' => $request->color
        ]);

        return response()->json(['success' => true]);
    }
    public function setPrimaryImage(Product $product, $imageId)
    {
        $image = $product->images()->findOrFail($imageId);

        // Reset all other images
        $product->images()->update(['is_primary' => false]);

        // Set new primary
        $image->update(['is_primary' => true]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Image principale mise à jour!');
    }
}
