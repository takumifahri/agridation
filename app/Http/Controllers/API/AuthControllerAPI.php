<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;

class AuthControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(Request $request)
    {
        // 
        try {
            $validate = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone_number' => 'nullable|string|max:255',
                'asal_sekolah' => 'required|string|max:255',
                'role' => 'required|string|in:peserta,panitia,juri',
                'password' => ['required', 'confirmed', RulesPassword::defaults()],
            ]);
           
    
            $user = User::create([
                'name' => $validate['name'],
                'email' => $validate['email'],
                'phone_number' => $validate['phone_number'],
                'asal_sekolah' => $validate['asal_sekolah'],
                'role' => $validate['role'],
                'password' => Hash::make($validate['password']),
            ]);
    
            event(new Registered($user));
    
            Auth::login($user);
    
            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'User registration failed',
                'errors' => $e->errors(),
            ], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function login(Request $request)
    {
        //Login user
        $validate = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Check credentials
        if (!Auth::attempt($validate)) {
            return response()->json([
            'status' => 'error',
            'message' => 'The provided credentials do not match our records.'
            ], 401);
        }

        // Retrieve the authenticated user
        // Ambil user yang berhasil login
        $user = User::where('email', $request->email)->firstOrFail();
        
        // Revoke old tokens if any
        $user->tokens()->delete();
        
        // Generate a new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful, Welcome back!',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function logout(Request $request)
    {
        //
        $request->user()->tokens()->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function me(Request $request)
    {
        // Ambil user yang sedang login dari request
        $user = $request->user();
            
        // Jika tidak ada user yang login, kembalikan error
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        // Tambahkan return statement untuk mengembalikan data user
        return response()->json([
            'status' => 'success',
            'user' => $user
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function updateProfile(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function DeleteAccount(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
