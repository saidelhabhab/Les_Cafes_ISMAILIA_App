<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\ReturnItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        // Fetch all return items
        $returns = ReturnItem::with('invoice')->get();
        return response()->json($returns, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
   
     public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric',
            'total_amount_with_tva' => 'required|numeric',
            'items.*.price' => 'required|numeric',
            'deleted_invoice_item_ids.*' => 'exists:invoice_items,id', // Optional, for removed items
        ]);

        DB::transaction(function () use ($request) {
            // Fetch the invoice and retrieve TVA rate
            $invoice = Invoice::findOrFail($request->invoice_id);
            $tvaRate = 0.2; // Extracted TVA rate from invoice once

            // Fetch the client once to avoid re-fetching in each loop iteration
            $client = Client::findOrFail($invoice->client_id);
            $totalDeductionForClient = 0;  // Track total deduction across items

            foreach ($request->items as $item) {
                // Convert quantity to kg if necessary
               

                // Calculate the total price for the item
                $itemTotalPrice = $item['price'] *  $item['quantity'];

                // Calculate TVA if applicable
                $tvaAmount = $itemTotalPrice * $tvaRate;
                $calculAmountTva =  $invoice->amount - $itemTotalPrice;
                $invoice->amount_tva =  ($calculAmountTva) *  $tvaRate;


                // Deduct item total price and TVA from invoice amounts
                $invoice->amount -= $itemTotalPrice;
                $invoice->total_amount_with_tva -= ($itemTotalPrice - $tvaAmount);

                // Adjust final_price if applicable
                if (isset($invoice->final_price)) {
                    $invoice->final_price -= ($itemTotalPrice - $tvaAmount);
                }

                // Accumulate deduction for the client’s final_price
                $totalDeductionForClient += ($itemTotalPrice + $tvaAmount);

                $invoice->save();

                // Create the return item
                $returnItem = new ReturnItem();
                $returnItem->invoice_id = $request->invoice_id;
                $returnItem->product_id = $item['product_id'];
                $returnItem->quantity = $item['quantity'];
                $returnItem->save();

                // Update product quantity in stock in kilograms
                $product = Product::find($item['product_id']);
                $product->quantity +=  $item['quantity']; // Increase stock with converted quantity in kg
                $product->save();
            }

            // Deduct the accumulated amount from the client’s final_price once
            $client->final_price -= $itemTotalPrice;
            $client->save();

            // Remove deleted invoice items if specified
            if ($request->has('deleted_invoice_item_ids')) {
                InvoiceItem::destroy($request->deleted_invoice_item_ids);
            }
        });

        return response()->json([
            'message' => 'Return created successfully!',
        ], 201);
    }

     

    // Display the specified resource.
    public function show($id)
    {
        $returnItem = ReturnItem::with('invoice')->findOrFail($id);
        return response()->json($returnItem, 200);
    }



    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        // Update logic if needed
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        try {
            $returnItem = ReturnItem::findOrFail($id); // Find the item by ID
            $returnItem->delete(); // Delete the item

            return response()->json(['message' => 'Item deleted successfully.'], 200); // Success response
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Item not found.'], 404); // Item not found
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete the item.'], 500); // General error
        }
    }


    public function returnToStock(Request $request)
    {
        // Log the incoming request data for debugging
        Log::info('Request Data:', $request->all());
    
        // Validate incoming request
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric',
            'invoice_item_id' => 'required|exists:invoice_items,id',
            'price' => 'required|numeric',
        ]);
    
        // Extracting the input values
        $productId = $request->input('product_id');
        $returnQuantity = $request->input('quantity');
        $invoiceItemId = $request->input('invoice_item_id');
        $invoiceId = $request->input('invoice_id');
        $price = $request->input('price');
    
        DB::beginTransaction(); // Start a transaction
    
        try {
            // Find the product
            $product = Product::findOrFail($productId);
    
            // Find the invoice item and decrease its quantity
            $invoiceItem = InvoiceItem::findOrFail($invoiceItemId);


            
            
            // Increase the stock of the product in kilograms
            $product->quantity += $returnQuantity;
            $product->save();
    
            // Decrease the quantity of the invoice item by the original unit quantity
            $invoiceItem->quantity -= $returnQuantity;
    
            // Calculate the total deduction for the invoice item
            $totalDeduction = $returnQuantity * $price;
    
            // Adjust the total amount on the invoice item
            $invoiceItem->total -= $totalDeduction;
    
            // Update the invoice amounts
            $invoice = Invoice::findOrFail($invoiceId);
            $tvaRate = 0.2; // Extracted TVA rate from invoice once

            // Fetch the client once to avoid re-fetching in each loop iteration
            $client = Client::findOrFail($invoice->client_id);
            $totalDeductionForClient = 0;  // Track total deduction across items


            // Calculate TVA if applicable

            $tvaAmount = $totalDeduction * $tvaRate;

            $calculAmountTva =  $invoice->amount - $totalDeduction;
            $invoice->amount_tva =  ($calculAmountTva) *  $tvaRate;

            // Deduct item total price and TVA from invoice amounts
            $invoice->amount -= $totalDeduction;
            $invoice->total_amount_with_tva -= ($totalDeduction - $tvaAmount); 

            
            // Adjust final_price if applicable
            if (isset($invoice->final_price)) {
                $invoice->final_price -= ($totalDeduction - $tvaAmount);
            }

            // Accumulate deduction for the client’s final_price
            $totalDeductionForClient += ($totalDeduction);

            $invoice->save();

            // Deduct the accumulated amount from the client’s final_price once
            $client->final_price -= $totalDeduction;
            $client->save();

    
            // If quantity in invoice item is zero or less, delete it; otherwise, save
            if ($invoiceItem->quantity <= 0) {
                $invoiceItem->delete();
            } else {
                $invoiceItem->save();
            }

            
    
            // Create a new return record including invoice_id
            ReturnItem::create([
                'product_id' => $productId,
                'quantity' => $returnQuantity,
                'invoice_item_id' => $invoiceItemId,
                'invoice_id' => $invoiceId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            DB::commit(); // Commit the transaction
    
            return response()->json(['message' => 'Product returned to stock successfully!'], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            Log::error('Error in returnToStock: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to return product to stock.', 'error' => $e->getMessage()], 500);
        }
    }
    
}
