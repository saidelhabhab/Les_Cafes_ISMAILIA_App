<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request): JsonResponse
    {
        // Initialize the product query
        $query = Product::query();
    
        // Apply search filter if 'search' parameter is present and not empty
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }
    
        // Paginate results with a default limit of 10 if not provided in the request
        $products = $query->paginate($request->get('limit', 10));
    
        // Return the paginated items and the total count in the response
        return response()->json([
            'items' => $products->items(),
            'total' => $products->total(),
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|numeric|min:0', // Allow decimals for precise conversion
            'unit'        => 'required|string|in:ton,kg,g', // Add unit validation
        ]);

        // Convert quantity to kilograms, if needed
        $quantityInKg = $this->convertToKg($request->quantity, $request->unit);

        // Generate a unique barcode
        $barcode = $this->generateBarcode();

        // Generate the barcode image
        try {
            $generator = new BarcodeGeneratorPNG();
            $image = $generator->getBarcode($barcode, $generator::TYPE_CODE_128);
            $barcodeFileName = 'barcodes/products/' . $barcode . '.png';
            Storage::put($barcodeFileName, $image);
        } catch (\Exception $e) {
            Log::error('Barcode generation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate barcode. Please try again.'], 500);
        }

        // Store product in the database, with quantity in kg and unit set to 'kg'
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $quantityInKg,
            'barcode' => $barcode,
            'unit' => 'kg', // Always store as 'kg'
        ]);

        return response()->json($product, 201);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|numeric|min:0', // Allow decimals for precise conversion
            'unit'        => 'required|string|in:ton,kg,g', // Add unit validation
        ]);

        $product = Product::findOrFail($id);

        // Convert quantity to kilograms, if needed
        $quantityInKg = $this->convertToKg($request->quantity, $request->unit);

        // Update product with the new data
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $quantityInKg,
            'unit' => 'kg', // Always store as 'kg'
        ]);

        return response()->json($product, 200);
    }

    /**
     * Convert quantity to kilograms based on unit.
     */
    private function convertToKg(float $quantity, string $unit): float
    {
        switch ($unit) {
            case 'ton':
                return $quantity * 1000; // 1 ton = 1000 kg
            case 'g':
                return $quantity / 1000; // 1 g = 0.001 kg
            case 'kg':
            default:
                return $quantity; // Already in kg
        }
    }

    /**
     * Generate a unique barcode.
     */
    private function generateBarcode(): string
    {
        $maxAttempts = 5;
        $attempt = 0;
        $barcode = null;

        do {
            $barcode = Str::upper(Str::random(10, '0123456789'));
            $exists = Product::where('barcode', $barcode)->exists();
            $attempt++;
        } while ($exists && $attempt <= $maxAttempts);

        return $barcode;
    }



    /**
     * Display the specified product.
     */
    public function show($id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.'
            ], 404);
        }

        return response()->json($product);
    }
 
   

     /**
     * Remove the specified product from storage.
     */
    public function destroy($id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.'
        ]);
    }


    /**
     * Add quantity to a specified product.
     */
    public function addQuantity(Request $request, $id): JsonResponse
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'unit'     => 'required|string|in:ton,kg,g',
        ]);

        // Find the product
        $product = Product::findOrFail($id);

        // Convert quantity to kilograms
        $quantityInKg = $this->convertToKg($validated['quantity'], $validated['unit']);

        // Update the product's quantity
        $product->quantity += $quantityInKg; // Add new quantity to existing quantity
        $product->save(); // Save the changes

        return response()->json(['message' => 'Quantity added successfully'], 200);
    }


}
