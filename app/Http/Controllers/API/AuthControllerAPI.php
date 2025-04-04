<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

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
                'asal_sekolah' => 'nullable|string|max:255',
                'role' => 'required|string|in:peserta,panitia,juri',
                'password' => ['required', 'confirmed', RulesPassword::defaults()],
            ]);
           
    
            $user = User::create([
                'name' => $validate['name'],
                'email' => $validate['email'],
                'phone_number' => $validate['phone_number'] ?? null,
                'asal_sekolah' => $validate['asal_sekolah'] ?? null,
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

// Redirect to Google OAuth page
    // public function redirectToGoogle()
    // {
    //     return Socialite::driver('google')->redirect();
    // }

        
    // // Handle the callback from Google
   
    // public function handleGoogleCallback()
    // {
    //     try {
    //         $googleUser = Socialite::driver('google')->user(); // buat real production
            
    //         $user = User::updateOrCreate(
    //             ['email' => $googleUser->email],
    //             [
    //                 'name' => $googleUser->name,
    //                 'google_id' => $googleUser->id,
    //                 'role' => 'peserta',
    //                 'asal_sekolah' => '',
    //                 'password' => null
    //             ]
    //         );

    //         if ($user->wasRecentlyCreated) {
    //             event(new Registered($user));
    //         }

    //         Auth::login($user);
            
    //         return response()->json([
    //             'status' => 'success',
    //             'user' => $user->only('id', 'name', 'email', 'role'),
    //             'access_token' => $user->createToken('auth_token')->plainTextToken,
    //             'token_type' => 'Bearer'
    //         ]);

    //     } catch (\Exception $e) {
    //         logger()->error('Google Auth Failed: ' . $e->getMessage());
            
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Autentikasi gagal. Silakan coba lagi.'. $e->getMessage()
    //         ], 401);
    //     }
    // }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            // Removed stateless() as it is not defined
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Validate state parameter for security
            if (!$request->has('state') || !$request->has('code')) {
                throw new \Exception('Invalid OAuth response');
            }


            // In your controller, before using Socialite
            \Illuminate\Support\Facades\Config::set('services.google.guzzle', [
                'verify' => false
            ]);

            // Get authenticated Google user
            $googleUser = Socialite::driver('google')->user();
                // ->stateless() // Important for API usage
                
            // Validate required fields
            if (empty($googleUser->email)) {
                throw new \Exception('Email not provided by Google');
            }

            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name ?? 'No Name Provided',
                    'google_id' => $googleUser->id,
                    'email_verified_at' => now(), // Mark email as verified
                    'role' => 'peserta',
                    'asal_sekolah' => '',
                    // 'password' => null,
                    'password' => Hash::make(Str::random(16)), // Optional: Generate a random password
                ]
            );

            // Trigger registered event if new user
            if ($user->wasRecentlyCreated) {
                event(new Registered($user));
            }

            // Log the user in
            Auth::login($user);
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $token = $user->createToken('auth_token')->plainTextToken;
            return redirect("{$frontendUrl}/dashboard");
            // return response()->json([
            //     'status' => 'success',
            //     'user' => [
            //         'id' => $user->id,
            //         'name' => $user->name,
            //         'email' => $user->email,
            //         'role' => $user->role
            //     ],
            //     'access_token' => $user->createToken(
            //         'google_oauth_token',
            //         ['*'], // Scopes if needed
            //         now()->addWeek() // Token expiry
            //     )->plainTextToken,
            //     'token_type' => 'Bearer',
            //     'expires_in' => 60 * 24 * 7 // 1 week in minutes
            // ]);

        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 401);
        }
    }

}
