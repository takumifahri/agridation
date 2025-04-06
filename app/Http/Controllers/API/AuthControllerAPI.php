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
                'asal_instansi' => 'nullable|string|max:255',
                'role' => 'required|string|in:peserta,panitia,juri',
                'isAgree' => 'required|boolean',
                'password' => ['required', 'confirmed', RulesPassword::defaults()],
            ]);

            // Format phone number to wa.me/.62 if provided
            $formattedPhoneNumber = null;
            if (!empty($validate['phone_number'])) {
                $formattedPhoneNumber = preg_replace('/^(0|\+62|021)/', '62', ltrim($validate['phone_number'], '0'));
                $formattedPhoneNumber = 'https://wa.me/' . $formattedPhoneNumber;
            }
    
            $user = User::create([
                'name' => $validate['name'],
                'email' => $validate['email'],
                'phone_number' => $formattedPhoneNumber,
                'asal_instansi' => $validate['asal_instansi'] ?? null,
                'role' => $validate['role'],
                'isAgree' => $validate['isAgree'],
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
                    'asal_instansi' => '',
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
