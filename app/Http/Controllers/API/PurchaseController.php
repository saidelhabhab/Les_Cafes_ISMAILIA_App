<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


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
            'quantity' => 'required|numeric|min:0', // Quantité minimum : 0
            'unit' => 'required|string|in:ton,kg,g',
            'price' => 'required|numeric|min:0', // Prix minimum : 0
            'purchase_date' => 'required|date',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Calcul des montants
        $quantity = $request->input('quantity');
        $price = $request->input('price');
        $totalPrice = $quantity * $price; // Total hors taxe
        $priceTVA = $totalPrice * 0.2;    // TVA (20%)
        $totalHT = $totalPrice - $priceTVA; // Total TTC
    
        // Créer l'achat avec les champs calculés
        $purchase = Purchase::create(array_merge($request->all(), [
            'total_ht' => $totalHT,
            'price_tva' => $priceTVA,
            'total_price' => $totalPrice
        ]));
    
        return response()->json($purchase, 201);
    }
    

    public function update(Request $request, $id)
    {
        // Validation des données entrantes
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|between:2000,2100',
            'month' => 'required|integer|between:1,12',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|in:ton,kg,g',
            'price' => 'required|numeric|min:0', // Validation du prix
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Rechercher l'achat par ID
        $purchase = Purchase::find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found.'], 404);
        }

        // Mettre à jour les détails de l'achat
        $purchase->year = $request->input('year');
        $purchase->month = $request->input('month');
        $purchase->quantity = $request->input('quantity');
        $purchase->unit = $request->input('unit');
        $purchase->price = $request->input('price');

        // Calcul du Total HT, TVA et du Total TTC
        $totalPrice = $purchase->quantity * $purchase->price; // Total hors taxe
        $priceTVA = $totalPrice * 0.2; // TVA (20%)
        $totalHT = $totalPrice - $priceTVA; // Total TTC

        $purchase->total_ht = $totalHT;
        $purchase->price_tva = $priceTVA;
        $purchase->total_price = $totalPrice;

        $purchase->save();

        return response()->json([
            'message' => 'Purchase updated successfully!',
            'purchase' => $purchase
        ], 200);
    }




    // Delete a purchase
    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase->delete();

        return response()->json(null, 204);
    }


    public function getYears()
    {
        $years = Purchase::select(DB::raw('YEAR(due_date) as year'))
            ->distinct()
            ->orderBy('year', 'asc')
            ->pluck('year');
    
        return response()->json($years);
    }
}
