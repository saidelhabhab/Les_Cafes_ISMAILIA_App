<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Client;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     */
    public function index(): JsonResponse
    {
        $clients = Client::all();
        return response()->json($clients);
        
    }

    /**
     * Store a newly created client in storage.
     */
      public function store(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'cin'    => 'nullable|string|max:255',
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|unique:clients,email',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'final_price'   => 'nullable|numeric|min:0',
            'remaining_price' => 'nullable|numeric|min:0',
        ]);

        // Create the client
        $client = Client::create($validated);

        // Return the created client with a 201 status code
        return response()->json($client, 201);
    }

    /**
     * Display the specified client.
     */
    public function show($id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'message' => 'Client not found.'
            ], 404);
        }

        return response()->json($client);
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'message' => 'Client not found.'
            ], 404);
        }

        // Validate the incoming request data
        $validated = $request->validate([
            'cin'    => 'sometimes|nullable|string|max:255',
            'name'    => 'sometimes|required|string|max:255',
            'email'   => 'sometimes|nullable|email|unique:clients,email,' . $id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'final_price'   => 'required|numeric|min:0',
            'remaining_price' => 'required|numeric|min:0',
        ]);

        // Update the client with validated data
        $client->update($validated);

        return response()->json($client);
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy($id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'message' => 'Client not found.'
            ], 404);
        }

        $client->delete();

        return response()->json([
            'message' => 'Client deleted successfully.'
        ]);
    }
}
