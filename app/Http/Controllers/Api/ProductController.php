<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
//        maka liste produits
        $products =  Product::all();
        if (!$products) {
            return response()->json(['message' => 'Products not found'], 404);
        }
        return response()->json($products);
    }

    // public function index_details(): \Illuminate\Http\JsonResponse
    // {
    //     $products = Product::with('activePrice')->get();
    
    //     if ($products->isEmpty()) {
    //         return response()->json(['message' => 'Products not found'], 404);
    //     }
    
    //     // Si tu veux retourner avec ProductResource, il faut adapter la resource aussi
    //     return response()->json(ProductResource::collection($products));
    // }

    public function index_details(): \Illuminate\Http\JsonResponse
    {
        $products =  Product::with('activePrice')->get();

        if (!$products) {
            return response()->json(['message' => 'Products not found'], 404);
        }
        return response()->json(ProductResource::collection($products)) ;
    }
    
    
    public function show($id): \Illuminate\Http\JsonResponse
    {
        $product =  Product::findOrFail($id);
        return response()->json($product);
    }

    public function details_show($id): \Illuminate\Http\JsonResponse
    {
        $product =  Product::with('activePrice')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(new  ProductResource($product)) ;
    }

    // public function details_show($id): \Illuminate\Http\JsonResponse
    // {
    //     $product =  Product::with('activePrice')->findOrFail($id);
    //     return response()->json(new ProductResource($product));

    // }

    public function store(Request $request): \Illuminate\Http\JsonResponse
{
    $validated = $request->validate([
        'reference' => 'nullable|string|max:255',
        'name_fr' => 'required|string|max:255',
        'name_latin' => 'required|string|max:255',
        'name_en' => 'nullable|string|max:255',
        'description_fr' => 'nullable|string',
        'description_en' => 'nullable|string',
        'category_id' => 'required|exists:categories,id',
        'sub_category_id' => 'required|exists:sub_categories,id',
        'price' => 'required|numeric|min:0',
        'image_path' => 'nullable|file|image|max:4096', // 👈 fichier
    ]);

    if ($request->hasFile('image_path')) {
        $path = $request->file('image_path')->store('products', 'public');
        $validated['image_path'] = 'storage/' . $path;
    }

    DB::transaction(function () use ($validated, &$product) {
        $product = Product::create([
            'reference' => $validated['reference'] ?? null,
            'name_fr' => $validated['name_fr'],
            'name_latin' => $validated['name_latin'],
            'name_en' => $validated['name_en'] ?? null,
            'description_fr' => $validated['description_fr'] ?? null,
            'description_en' => $validated['description_en'] ?? null,
            'category_id' => $validated['category_id'],
            'sub_category_id' => $validated['sub_category_id'],
            'image_path' => $validated['image_path'] ?? null,
        ]);

        Price::create([
            'product_id' => $product->id,
            'amount' => $validated['price'],
            'amount_mga' => $validated['amount_mga'] ?? $validated['price'] * 4500,
            'type' => 'regular',
            'is_active' => true,
            'effective_date' => now(),
        ]);
    });

    return response()->json(new ProductResource($product), 201);
}


public function update(Request $request, $id): \Illuminate\Http\JsonResponse
{
    $product = Product::find($id);
    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    $validated = $request->validate([
        'reference' => 'nullable|string|max:255',
        'name_fr' => 'required|string|max:255',
        'name_latin' => 'required|string|max:255',
        'name_en' => 'nullable|string|max:255',
        'description_fr' => 'nullable|string',
        'description_en' => 'nullable|string',
        'category_id' => 'required|exists:categories,id',
        'sub_category_id' => 'required|exists:sub_categories,id',
        'price' => 'required|numeric|min:0',
        'image_path' => 'nullable|file|image|max:4096', 
    ]);

    // ⬇️ gérer l'upload nouvelle image
    if ($request->hasFile('image_path')) {
        $path = $request->file('image_path')->store('products', 'public');
        $validated['image_path'] = 'storage/' . $path;
    }

    DB::transaction(function () use ($validated, $product) {

        $product->update($validated);

        Price::where('product_id', $product->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        Price::create([
            'product_id' => $product->id,
            'amount' => $validated['price'],
            'amount_mga' => $validated['amount_mga'] ?? $validated['price'] * 4500,
            'type' => 'regular',
            'is_active' => true,
            'effective_date' => now(),
        ]);
    });

    return response()->json(new ProductResource($product));
}


    // Supprimer produit + prix
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete(); // Les prix associés sont supprimés automatiquement

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
