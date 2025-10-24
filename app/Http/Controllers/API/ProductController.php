<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{   
    /**
     * GET /api/products
     * Show all products
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // default 10
        $search = $request->get('search', '');
        $sortField = $request->get('sort_field', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = Product::query();

        if ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('sku', 'like', "%$search%");
        }

        $products = $query->orderBy($sortField, $sortOrder)->paginate($perPage);

        return response()->json($products);
    }

    /**
     * POST /api/products
     * Store new product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|unique:products',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'unit' => 'nullable|string'
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }
    
    /**
     * GET /api/products/{id}
     * Show single product
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Product retrieved successfully',
            'data' => $product
        ], 200);
    }

    /**
     * PUT /api/products/{id}
     * Update product
     */
    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'sku' => "required|unique:products,sku,$id",
            'name' => 'required|string',
            'description' => 'nullable|string',
            'unit' => 'nullable|string'
        ]);
        
        
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ], 200);
    }

    /**
     * DELETE /api/products/{id}
     * Delete product
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
