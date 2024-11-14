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
           // 'final_price' => 'nullable|numeric|min:0',
            'remaining_price' => 'nullable|numeric|min:0',
            'amount_in_words_en'=> 'required|string',
            'amount_in_words_fr'=> 'required|string',
            'amount_in_words_ar'=> 'required|string',
            'invoice_items' => 'required|array|min:1',
            'invoice_items.*.product_id' => 'required|integer|exists:products,id',
            'invoice_items.*.quantity' => 'required|numeric|min:1', // Allow numeric for conversion
           // 'invoice_items.*.unit' => 'nullable|string|in:kg,ton,g', // Validate unit
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


        $amount_tva = 0;
        if($validatedData['tva']> 0) {
           $amount_tva =  $validatedData['tva'] *  ( $validatedData['amount'] / 100);
        }
        
        // Start a transaction
        DB::beginTransaction();
        try {
            // Create the invoice
            $invoice = Invoice::create([
                'client_id' => $validatedData['client_id'],
                'amount' => $validatedData['amount'],
                'total_amount_with_tva' => $validatedData['total_amount_with_tva'],
                'amount_tva' => $amount_tva,
                'status' => $validatedData['status'],
                'due_date' => $validatedData['due_date'],
                'checkDate' => $validatedData['payment_type'] === 'check' ? $validatedData['checkDate'] : null,
                'factor_code' => $factor_code,
                'factor_bar_code' => $factor_bar_code_path,
                'payment_type' => $validatedData['payment_type'],
                'tva' => $validatedData['tva'] ?? 0,
                'remaining_price' => $validatedData['remaining_price'],
                'amount_in_words_en' => $validatedData['amount_in_words_en'],
                'amount_in_words_fr' => $validatedData['amount_in_words_fr'],
                'amount_in_words_ar' => $validatedData['amount_in_words_ar'],
            ]);

            // Update client financials
            $client = Client::findOrFail($validatedData['client_id']);
            $client->final_price += $validatedData['total_amount_with_tva'];
            $client->remaining_price += $validatedData['remaining_price'];
            $client->save();

            // Process invoice items with conversion logic
            foreach ($validatedData['invoice_items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Convert quantity to kilograms based on the unit
             //   $quantityInKg = $this->convertToKg($item['quantity'], $item['unit']);

                // Check if there is enough stock
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Not enough stock for product ID: {$item['product_id']}");
                }

                // Create the invoice item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'], // Save the converted quantity
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'], // Calculate total based on kg
                   // 'unit' =>  $item['unit']

                ]);

                // Decrease product quantity
                $product->quantity -= $item['quantity'];
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
                 'remaining_price' => 'nullable|numeric|min:0',
                 'checkDate' => 'nullable|date',
                 'payment_type' => 'required|string|in:cash,check',
                 'amount_in_words_en'=> 'required|string',
                 'amount_in_words_fr'=> 'required|string',
                 'amount_in_words_ar'=> 'required|string',
                 'tva' => 'nullable|numeric|min:0',
                 'invoice_items' => 'required|array',
                 'invoice_items.*.product_id' => 'required|exists:products,id',
                 'invoice_items.*.quantity' => 'required|numeric',
                // 'invoice_items.*.unit' => 'required|string',
             ]);
     
             // Step 2: Find the invoice
             $invoice = Invoice::findOrFail($id);

             // Step 3: Retrieve the old total amount with tva
             $oldTotalAmountWithTva = $invoice->total_amount_with_tva;
     
             // Step 4: Update client financials
             $client = Client::findOrFail($validatedData['client_id']);
             
             // Decrease old amount from client's final price
             $client->final_price -= $oldTotalAmountWithTva;
             
             // Increase by new amount with tva
             $client->final_price += $validatedData['total_amount_with_tva'];
     
             // Update remaining price if necessary
             $client->remaining_price += $validatedData['remaining_price'];
     
             // Save updated client
             $client->save();

    
            // Step 3: Restore stock quantities for each old item and delete them
                foreach ($invoice->invoice_items as $oldItem) {
                    // Log the old item information for debugging
                    Log::info('Restoring stock for item: ', $oldItem->toArray());

                    $product = Product::find($oldItem->product_id);

                    if ($product) {
                        // Convert quantity to kilograms (if needed)
                        try {
                           // $quantityInKg = $this->convertToKg($oldItem->quantity, $oldItem->unit);
                            // Log the converted quantity for debugging
                           // Log::info('Converted quantity in kg: ' . $quantityInKg);

                            // Increase product quantity in stock
                            $product->quantity += $oldItem->quantity;
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


            // Step 4: Calculate amount_tva
            $tva = $validatedData['tva'] ?? 0; // Get TVA or default to 0
            $amount = $validatedData['amount']; // Get amount
            $amount_tva = $amount * ($tva / 100); // Calculate amount_tva


     
             // Step 5: Update the invoice properties
             $invoice->fill($validatedData);
             $invoice->amount_tva = $amount_tva; // Store amount_tva in the invoice
             $invoice->save();
     
             // Step 6: Add new invoice items
             foreach ($request->invoice_items as $newItem) {
                 $item = new InvoiceItem();
                 $item->invoice_id = $invoice->id;
                 $item->product_id = $newItem['product_id'];
                 $item->quantity = $newItem['quantity'];
                // $item->unit = $newItem['unit'];
                 $item->price = $newItem['price'];
                 $item->total = $newItem['total'];
                 $item->save();
     
                 // Step 7: Update product stock based on new invoice items
                 $product = Product::find($newItem['product_id']);
                 if ($product) {
                     // Convert quantity to kilograms (if needed)
                   //  $quantityInKg = $this->convertToKg($newItem['quantity'], $newItem['unit']);
                     
                     // Decrease product quantity in stock
                     $product->quantity -= $newItem['quantity'];
                     $product->save();
                 }
             }
     
             // Step 8: Return a response
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
        $monthlyData = Invoice::select(DB::raw('SUM(total_amount_with_tva) as total_amount'), DB::raw('MONTH(due_date) as month'))
            ->whereYear('due_date', $year) // Specify year from request
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
        $totalAmount = Invoice::whereYear('due_date', $year)
            ->sum('total_amount_with_tva'); // Adjust field name if necessary

        // Return both monthly data and total amount
        return response()->json([
            'monthly_data' => $results,
            'total_amount' => $totalAmount,
        ]);
    }


    public function getYears()
    {
        $years = Invoice::select(DB::raw('YEAR(due_date) as year'))
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

    public function updateStatus(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->status = $request->input('status');
        $invoice->save();

        return response()->json(['message' => 'Status updated successfully.']);
    }

     
}
