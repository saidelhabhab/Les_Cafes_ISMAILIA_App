<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
             'items.*.quantity' => 'required|integer|min:1',
             'total_amount_with_tva' => 'required|numeric',
             'items.*.price' => 'required|numeric',
             'items.*.unit' => 'required|string|in:ton,kg,g',
             'deleted_invoice_item_ids' => 'array', // Optional, for removed items
         ]);
     
         DB::transaction(function () use ($request) {
             // Update the invoice with the new total amount
             $invoice = Invoice::find($request->invoice_id);
             $invoice->total_amount_with_tva = $request->total_amount_with_tva;
              // Update total amount with TVA
            $invoice->total_amount_with_tva = $request->total_amount_with_tva;

            // Loop through items and subtract from invoice amount
            foreach ($request->items as $item) {
                $invoice->amount -= ($item['price'] * $item['quantity']);
            }
            $invoice->save();

            
             // Loop through the items and create return items
             foreach ($request->items as $item) {
                 $returnItem = new ReturnItem();
                 $returnItem->invoice_id = $request->invoice_id;
                 $returnItem->product_id = $item['product_id'];
                 $returnItem->quantity = $item['quantity'];
                 $returnItem->unit = $item['unit'];
                 $returnItem->save();
     
                 // Update product quantity in stock
                 $product = Product::find($item['product_id']);
                 $product->quantity += $item['quantity']; // Increase stock on return
                 $product->save();
             }
     
             // If there are deleted invoice item IDs, remove them
         //    if ($request->has('deleted_invoice_item_ids')) {
          //       InvoiceItem::destroy($request->deleted_invoice_item_ids); // Delete old invoice items
          //   }
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
        $returnItem = ReturnItem::findOrFail($id);
        $returnItem->delete();

        return response()->json(['message' => 'Return deleted successfully'], 200);
    }


    private function convertToKg($quantity, $unit)
    {
        switch ($unit) {
            case 'ton':
                return $quantity * 1000; // 1 ton = 1000 kg
            case 'kg':
                return $quantity; // already in kg
            case 'g':
                return $quantity / 1000; // convert grams to kg
            default:
                return 0; // handle unknown units
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
            
            // Convert the return quantity to kilograms if necessary
            $unit = $invoiceItem->unit; // Assuming 'unit' column exists in invoice_items
            $convertedQuantity = $returnQuantity;
    
            switch ($unit) {
                case 'ton':
                    $convertedQuantity = $returnQuantity * 1000; // Convert tons to kilograms
                    break;
                case 'gram':
                    $convertedQuantity = $returnQuantity / 1000; // Convert grams to kilograms
                    break;
                // kg case or unknown units will use returnQuantity as-is
            }
    
            // Increase the stock of the product in kilograms
            $product->quantity += $convertedQuantity;
            $product->save();
    
            // Decrease the quantity of the invoice item by the original unit quantity
            $invoiceItem->quantity -= $returnQuantity;
    
            // Calculate the total deduction for the invoice item
            $totalDeduction = $returnQuantity * $price;
    
            // Adjust the total amount on the invoice item
            $invoiceItem->total -= $totalDeduction;
    
            // Update the invoice amounts
            $invoice = Invoice::findOrFail($invoiceId);
            $invoice->amount -= $totalDeduction;
            $invoice->total_amount_with_tva -= $totalDeduction;
            $invoice->save();
    
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
                'unit' => $unit,
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
