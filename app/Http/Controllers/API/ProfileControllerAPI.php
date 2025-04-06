<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function me(Request $request)
    {
        // Ambil user yang sedang login dari request
        $user = Auth::user();
            
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

    private function normalizePhoneNumber($phone)
    {
        // Hapus semua karakter kecuali angka
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 0 -> ganti jadi 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // Kalau sudah +62 atau 62 di depan, biarkan
        // Kalau angka lokal seperti 021, kita bisa skip atau handle beda

        return 'https://wa.me/' . $phone;
    }
    /**
     * Show the form for creating a new resource.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        
        try {
                        
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
                'asal_instansi' => 'sometimes|string|max:255',
                'phone_number' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            ]);

            $updateData = $validated; // simple and clean

            if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
                if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                    Storage::disk('public')->delete($user->profile_photo);
                }
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $updateData['profile_photo'] = $path;
            }

            $user->update([
                'name' => $updateData['name'] ?? $user->name,
                'asal_instansi' => $updateData['asal_instansi'] ?? $user->asal_instansi,
                'phone_number' => $updateData['phone_number'] = $this->normalizePhoneNumber($updateData['phone_number']) ?? $user->phone_number,
                'email' => $updateData['email'] ?? $user->email,
                'profile_photo' => $updateData['profile_photo'] ?? $user->profile_photo,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => $user,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function resetPassword(Request $request)
    {
        //
        $user = $request->user();
        if (!$user) {
            return response()->json([
            'status' => 'error',
            'message' => 'User not found'
            ], 404);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['status' => __($status)])
            : response()->json(['email' => __($status)], 400);
    }

    
    /**
     * Update the specified resource in storage.
     */
    public function updatePassword (Request $request)
    {
        //
        $validate = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        
        $status = Password::reset(
            $validate,
            function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
            }
        );
        
        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password berhasil direset!'])
            : response()->json(['message' => __($status)], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteAccount(Request $request)
    {
         $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Soft delete the user
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Account deleted successfully'
        ]);
    }
}
