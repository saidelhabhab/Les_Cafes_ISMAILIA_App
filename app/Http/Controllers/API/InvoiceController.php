<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the invoices.
     */
    public function index()
    {
        // List all invoices
        $invoices = Invoice::with('invoice_items', 'client')->get();
       
        return response()->json($invoices);
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request)
    {
         // Validate incoming request data
        $validatedData = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'amount' => 'required|numeric|min:0',
            'total_amount_with_tva' => 'required|numeric|min:0',
            'status' => 'required|string|in:paid,pending',
            'due_date' => 'required|date',
            'checkDate' => 'nullable|date',
            'payment_type' => 'required|string|in:cash,check',
            'tva' => 'nullable|numeric|min:0',
            'final_price' => 'required|numeric|min:0',
            'remaining_price' => 'required|numeric|min:0',
            'invoice_items' => 'required|array|min:1',
            'invoice_items.*.product_id' => 'required|integer|exists:products,id',
            'invoice_items.*.quantity' => 'required|numeric|min:1', // Allow numeric for conversion
            'invoice_items.*.unit' => 'required|string|in:kg,ton,g', // Validate unit
            'invoice_items.*.price' => 'required|numeric|min:0',
        ]);

        // Additional Validation: If payment_type is 'check', checkDate must be provided
        if ($validatedData['payment_type'] === 'check' && empty($validatedData['checkDate'])) {
            return response()->json(['error' => 'Please provide a check cashing date.'], 422);
        }
        
        // Automatically generate a unique factor code with a maximum of 5 attempts
        $maxAttempts = 5;
        $attempt = 0;
        $factor_code = null;

        do {
            $factor_code = Str::upper(Str::random(10, '0123456789')); // Generates a random uppercase string of length 10
            $exists = Invoice::where('factor_code', $factor_code)->exists();
            $attempt++;
            if ($attempt > $maxAttempts) {
                Log::error('Failed to generate unique factor code after ' . $maxAttempts . ' attempts.');
                return response()->json(['error' => 'Failed to generate unique factor code. Please try again.'], 500);
            }
        } while ($exists);

        // Initialize factor_bar_code as null
        $factor_bar_code_path = null;

        // Generate the barcode
        try {
            $generator = new BarcodeGeneratorPNG();
            $image = $generator->getBarcode($factor_code, $generator::TYPE_CODE_128);

            // Define a unique file name
            $barcodeFileName = 'barcodes/invoices/' . $factor_code . '.png';
            Storage::put($barcodeFileName, $image);

            $factor_bar_code_path = $barcodeFileName; // Store the path
        } catch (\Exception $e) {
            Log::error('Barcode generation failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to generate barcode. Please try again.'], 500);
        }
        
        // Start a transaction
        DB::beginTransaction();
        try {
            // Create the invoice
            $invoice = Invoice::create([
                'client_id' => $validatedData['client_id'],
                'amount' => $validatedData['amount'],
                'total_amount_with_tva' => $validatedData['total_amount_with_tva'],
                'status' => $validatedData['status'],
                'due_date' => $validatedData['due_date'],
                'checkDate' => $validatedData['payment_type'] === 'check' ? $validatedData['checkDate'] : null,
                'factor_code' => $factor_code,
                'factor_bar_code' => $factor_bar_code_path,
                'payment_type' => $validatedData['payment_type'],
                'tva' => $validatedData['tva'] ?? 0,
                'final_price' => $validatedData['final_price'],
                'remaining_price' => $validatedData['remaining_price'],
            ]);

            // Update client financials
            $client = Client::findOrFail($validatedData['client_id']);
            $client->final_price += $validatedData['final_price'];
            $client->remaining_price += $validatedData['remaining_price'];
            $client->save();

            // Process invoice items with conversion logic
            foreach ($validatedData['invoice_items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Convert quantity to kilograms based on the unit
                $quantityInKg = $this->convertToKg($item['quantity'], $item['unit']);

                // Check if there is enough stock
                if ($product->quantity < $quantityInKg) {
                    throw new \Exception("Not enough stock for product ID: {$item['product_id']}");
                }

                // Create the invoice item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'], // Save the converted quantity
                    'price' => $item['price'],
                    'total' => $quantityInKg * $item['price'], // Calculate total based on kg
                    'unit' =>  $item['unit']

                ]);

                // Decrease product quantity
                $product->quantity -= $quantityInKg;
                $product->save();
            }

            // Commit the transaction
            DB::commit();

            // Return a success response
            return response()->json(['message' => 'Invoice created successfully', 'invoice' => $invoice], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if there's an error
            DB::rollBack();

            // Log the error for debugging
            Log::error('Error creating invoice: ' . $e->getMessage());

            // Return an error response
            return response()->json(['error' => 'Failed to save invoice. Please check your input in Laravel.'], 500);
        }
    }
    

        /**
         * Convert the quantity to kilograms based on the unit
         *
         * @param float $quantity
         * @param string $unit
         * @return float
         */


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

    /**
     * Display the specified invoice.
     */
    public function show($id): JsonResponse
    {
        $invoice = Invoice::with('invoice_items', 'client')->findOrFail($id);

        if (!$invoice) {
            $invoice->factor_bar_code = asset('storage/' . $invoice->factor_bar_code);
            return response()->json([
                'message' => 'Invoice not found.'
            ], 404);
        }
    

        return response()->json($invoice);
    }

    /**
     * Update the specified invoice in storage.
     */

     public function update(Request $request, $id)
     {
         // Log incoming request data for debugging
         Log::info('Update Invoice Request Data: ', $request->all());
     
         try {
             // Step 1: Validate the incoming request data
             $validatedData = $request->validate([
                 'client_id' => 'required|exists:clients,id',
                 'amount' => 'required|numeric',
                 'total_amount_with_tva' => 'required|numeric',
                 'status' => 'required|string',
                 'due_date' => 'required|date',
                 'final_price' => 'required|numeric|min:0',
                 'remaining_price' => 'required|numeric|min:0',
                 'checkDate' => 'nullable|date',
                 'payment_type' => 'required|string|in:cash,check',
                 'tva' => 'nullable|numeric|min:0',
                 'invoice_items' => 'required|array',
                 'invoice_items.*.product_id' => 'required|exists:products,id',
                 'invoice_items.*.quantity' => 'required|numeric',
                 'invoice_items.*.unit' => 'required|string',
             ]);
     
             // Step 2: Find the invoice
             $invoice = Invoice::findOrFail($id);
     
            // Step 3: Restore stock quantities for each old item and delete them
                foreach ($invoice->invoice_items as $oldItem) {
                    // Log the old item information for debugging
                    Log::info('Restoring stock for item: ', $oldItem->toArray());

                    $product = Product::find($oldItem->product_id);

                    if ($product) {
                        // Convert quantity to kilograms (if needed)
                        try {
                            $quantityInKg = $this->convertToKg($oldItem->quantity, $oldItem->unit);
                            // Log the converted quantity for debugging
                            Log::info('Converted quantity in kg: ' . $quantityInKg);

                            // Increase product quantity in stock
                            $product->quantity += $quantityInKg;
                            $product->save();
                            Log::info('Updated product quantity: ' . $product->quantity);
                        } catch (\Exception $e) {
                            Log::error('Error converting quantity or updating product: ' . $e->getMessage());
                            return response()->json(['error' => 'Unable to restore stock for item.'], 500);
                        }
                    } else {
                        Log::warning('Product not found for old item: ' . $oldItem->product_id);
                    }

                    // Attempt to delete the old item and catch any errors
                    try {
                        $oldItem->delete();
                        Log::info('Deleted old invoice item: ' . $oldItem->id);
                    } catch (\Exception $e) {
                        Log::error('Error deleting old invoice item: ' . $e->getMessage());
                        return response()->json(['error' => 'Unable to delete old invoice item.'], 500);
                    }
                }

     
             // Step 4: Update the invoice properties
             $invoice->fill($validatedData);
             $invoice->save();
     
             // Step 5: Add new invoice items
             foreach ($request->invoice_items as $newItem) {
                 $item = new InvoiceItem();
                 $item->invoice_id = $invoice->id;
                 $item->product_id = $newItem['product_id'];
                 $item->quantity = $newItem['quantity'];
                 $item->unit = $newItem['unit'];
                 $item->price = $newItem['price'];
                 $item->total = $newItem['total'];
                 $item->save();
     
                 // Step 6: Update product stock based on new invoice items
                 $product = Product::find($newItem['product_id']);
                 if ($product) {
                     // Convert quantity to kilograms (if needed)
                     $quantityInKg = $this->convertToKg($newItem['quantity'], $newItem['unit']);
                     
                     // Decrease product quantity in stock
                     $product->quantity -= $quantityInKg;
                     $product->save();
                 }
             }
     
             // Step 7: Return a response
             return response()->json(['message' => 'Invoice updated successfully!', 'invoice' => $invoice], 200);
         } catch (\Exception $e) {
             // Log the error
             Log::error('Invoice Update Error: ' . $e->getMessage());
             return response()->json(['error' => 'Unable to update invoice.'], 500);
         }
     }
     

     
     
     
    

    

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy($id): JsonResponse
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found.'
            ], 404);
        }

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully.'
        ]);
    }


    public function getProductsByInvoice($id)
    {
        // Fetch products related to a specific invoice
        $invoice = Invoice::with('invoice_Items.product')->findOrFail($id);
        return response()->json($invoice->invoice_Items);
    }

    public function destroyItems(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'deleted_invoice_item_ids' => 'required|array',
            'deleted_invoice_item_ids.*' => 'integer|exists:invoice_items,id',
        ]);
    
        // Remove the specified invoice items
        InvoiceItem::destroy($request->deleted_invoice_item_ids);
    
        return response()->json(['message' => 'Old items deleted successfully.'], 200);
    }

    public function getMonthlyData(Request $request)
    {
        $year = $request->input('year', date('Y')); // Get the year from the request or default to the current year

        // Get all months for the current year
        $months = collect([
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ]);

        // Get total invoice amounts grouped by month for the specified year
        $monthlyData = Invoice::select(DB::raw('SUM(total_amount_with_tva) as total_amount'), DB::raw('MONTH(created_at) as month'))
            ->whereYear('created_at', $year) // Specify year from request
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_amount', 'month')
            ->toArray();

        // Fill in months with 0 if no data exists
        $results = [];
        foreach ($months as $index => $month) {
            $results[$month] = $monthlyData[$index + 1] ?? 0; // +1 because months are 1-indexed
        }

        // Calculate total amount for the selected year
        $totalAmount = Invoice::whereYear('created_at', $year)
            ->sum('total_amount_with_tva'); // Adjust field name if necessary

        // Return both monthly data and total amount
        return response()->json([
            'monthly_data' => $results,
            'total_amount' => $totalAmount,
        ]);
    }


    public function getYears()
    {
        $years = Invoice::select(DB::raw('YEAR(created_at) as year'))
            ->distinct()
            ->orderBy('year', 'asc')
            ->pluck('year');
    
        return response()->json($years);
    }

    
    public function getTotalRevenue()
    {
          // Sum the 'total_amount_with_tva' column
          $totalRevenue = Invoice::sum('total_amount_with_tva');
        
          // Return the result (you can return it to the view or API response)
          return response()->json(['total_revenue' => $totalRevenue]);
    }



       /* public function update(Request $request, $id)
     {
         // Start transaction to ensure data integrity
         DB::beginTransaction();
     
         try {
             // Find the invoice
             $invoice = Invoice::findOrFail($id);
     
             // Step 1: Restore stock quantities for each old item and delete them
             foreach ($invoice->invoice_items as $oldItem) {
                 $product = Product::find($oldItem->product_id);
                 
                 if ($product) {
                     // Convert quantity to kilograms (if needed)
                     $quantityInKg = $this->convertToKg($oldItem->quantity, $oldItem->unit);
                     
                     // Increase product quantity in stock
                     $product->quantity += $quantityInKg;
                     $product->save();
                 }
     
                 // Explicitly delete each item
                 $oldItem->delete();
             }
     
             // Step 2: Update the invoice fields
             $invoice->update([
                 'client_id' => $request->input('client_id'),
                 'amount' => $request->input('amount'),
                 'total_amount_with_tva' => $request->input('total_amount_with_tva'),
                 'status' => $request->input('status'),
                 'due_date' => $request->input('due_date'),
                 'checkDate' => $request->input('checkDate'),
                 'factor_code' => $request->input('factor_code'),
                 'factor_bar_code' => $request->input('factor_bar_code'),
                 'final_price' => $request->input('final_price'),
                 'remaining_price' => $request->input('remaining_price'),
                 'payment_type' => $request->input('payment_type'),
                 'tva' => $request->input('tva'),
             ]);
     
             // Step 3: Add new invoice items and deduct quantities from stock
             foreach ($request->input('invoice_items') as $itemData) {
                 $product = Product::findOrFail($itemData['product_id']);
                 $quantityInKg = $this->convertToKg($itemData['quantity'], $itemData['unit']);
     
                 if ($product->quantity < $quantityInKg) {
                     throw new \Exception("Not enough stock for product ID: {$itemData['product_id']}");
                 }
     
                 // Create new invoice item
                 $invoiceItem = new InvoiceItem([
                     'invoice_id' => $invoice->id,
                     'product_id' => $itemData['product_id'],
                     'quantity' => $itemData['quantity'],
                     'unit' => $itemData['unit'],
                     'price' => $itemData['price'],
                     'total' => $itemData['total'],
                 ]);
                 $invoiceItem->save();
     
                 // Deduct stock quantity from product
                 $product->quantity -= $quantityInKg;
                 $product->save();
             }
     
             // Commit transaction
             DB::commit();
             
             return response()->json(['message' => 'Invoice updated successfully', 'invoice' => $invoice], 200);
     
         } catch (\Exception $e) {
             // Rollback transaction if there is an error
             DB::rollBack();
             
             // Log error for debugging
             Log::error('Error updating invoice: ' . $e->getMessage());
             Log::error('Stack Trace: ' . $e->getTraceAsString());
             
             return response()->json(['error' => 'Failed to update invoice. Please try again.'], 500);
         }
     }
     

     */
     
}
