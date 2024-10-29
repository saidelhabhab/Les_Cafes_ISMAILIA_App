<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * User Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create a token 
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create a cookie that lasts for 1 day
        $cookie = cookie('auth_token', $token, 1440); // 1440 minutes = 24 hours

        return response()->json([
            'message' => 'User logged in successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200)->cookie($cookie); // Attach the cookie to the response
    }

    /**
     * User Logout
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.'
        ]);
    }

    /**
     * Get Authenticated User
     */
    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'image' => $user->image,
        ]);
    }

    /**
     * Register New User (Optional)
     */
    public function register(Request $request)
    {
        // Optional: Implement user registration
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign role (assuming Spatie roles)
        $user->assignRole('client'); // or 'admin' based on your logic

        

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the token and user information
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames(),
            ],
        ], 201);
    }


    public function update(Request $request)
    {
        $user = $request->user(); // Get the authenticated user

        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        // Update the user details
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        return response()->json($user); // Return updated user details
    }



    public function updatePassword(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'], // Adjust rules as needed
        ]);

        $user = $request->user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password does not match our records.'],
            ]);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }

}
