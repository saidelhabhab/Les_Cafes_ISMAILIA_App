<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    // Get purchases by country ID
    public function index($countryId)
    {
        $purchases = Purchase::where('country_id', $countryId)->get();
        return response()->json([
            'purchases' => $purchases,
            'country' => $purchases->first()->country, // Assuming 'country' is defined as a relationship in the Purchase model
        ]);
    }

    public function show($id)
    {
        try {
            $purchase = Purchase::with('country')->findOrFail($id); // Fetch purchase with its related country
            return response()->json($purchase);
        } catch (\Exception $e) {
            Log::error($e->getMessage()); // Log error for further debugging
            return response()->json(['message' => 'Could not retrieve purchase', 'error' => $e->getMessage()], 500);
        }
    }

    // Store a new purchase
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'quantity' => 'required|numeric|min:0', // Added min:0 for quantity
            'unit' => 'required|string|in:ton,kg,g',
            'price' => 'required|numeric|min:0', // Validate price
            'purchase_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Calculate total price
        $totalPrice = $request->input('quantity') * $request->input('price');

        // Create a new purchase with the total_price included
        $purchase = Purchase::create(array_merge($request->all(), ['total_price' => $totalPrice]));
        
        return response()->json($purchase, 201);
    }

    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|between:2000,2100',
            'month' => 'required|integer|between:1,12',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|in:ton,kg,g',
            'price' => 'required|numeric|min:0', // Validate price
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the purchase by ID
        $purchase = Purchase::find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found.'], 404);
        }

        // Update the purchase details
        $purchase->year = $request->input('year'); // Changed from 'selectedYear' to 'year'
        $purchase->month = $request->input('month'); // Changed from 'selectedMonth' to 'month'
        $purchase->quantity = $request->input('quantity');
        $purchase->unit = $request->input('unit');
        $purchase->price = $request->input('price'); // Update price

        // Calculate total price on update
        $purchase->total_price = $purchase->quantity * $purchase->price; 

        $purchase->save();

        return response()->json(['message' => 'Purchase updated successfully!', 'purchase' => $purchase], 200);
    }



    // Delete a purchase
    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase->delete();

        return response()->json(null, 204);
    }
}
