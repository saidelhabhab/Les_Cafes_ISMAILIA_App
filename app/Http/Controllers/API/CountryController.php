<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    // Get all countries
    public function index()
    {
        $countries = Country::all();
        return response()->json($countries);
    }

    // Store a new country
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $country = Country::create([
            'name' => $request->name,
        ]);

        return response()->json($country, 201);
    }

    // Update a country
    public function update(Request $request, $id)
    {
        $country = Country::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $country->update([
            'name' => $request->name,
        ]);

        return response()->json($country);
    }

    // Delete a country
    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        $country->delete();

        return response()->json(null, 204);
    }
}
