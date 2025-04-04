<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\master_lomba;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompetitionControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //

        $search = $request->query('search');
        if ($search) {
            $competitions = master_lomba::where('nama_lomba', 'LIKE', "%{$search}%")
                ->orWhere('deskripsi', 'LIKE', "%{$search}%")
                ->get();
        } else {
            $competitions = master_lomba::all();
        }
        
        try{
            $competitions = master_lomba::all();
            if($competitions->isEmpty()){
                return response()->json([
                    'message' => 'Data Tidak Ditemukan',
                ], 404);
            } else{
                return response()->json([
                    'message' => 'Success',
                    'data' => $competitions,
                ], 200);
            }
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        try{
            $user = User::findOrFail(Auth::id());
            if($user->isPanitia() || $user->isJuri()){
                $validate = $request->validate([
                    'nama_lomba' => 'required|string|max:255',
                    'deskripsi' => 'nullable|string',
                    'link_gdrive' => 'nullable|url',
                    'isAccepted' => 'nullable|boolean',
                    'total_peminat_tahun_lalu' => 'nullable|integer|min:0',
                    'total_peminat_tahun_sekarang' => 'nullable|integer|min:0',
                ]);
    
                $add_data = master_lomba::create([
                    'nama_lomba' => $validate['nama_lomba'],
                    'deskripsi' => $validate['deskripsi'],
                    'link_gdrive' => $validate['link_gdrive'],
                    'isAccepted' => $validate['isAccepted'] ?? true,
                    'total_peminat_tahun_lalu' => $validate['total_peminat_tahun_lalu'] ?? 0,
                    'total_peminat_tahun_sekarang' => $validate['total_peminat_tahun_sekarang'] ?? 0,
                ]);
    
                return response()->json([
                    'message' => 'Success',
                    'data' => $add_data,
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Maaf Anda Tidak Memiliki Akses',
                ], 401);
            }
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Failed',
                'error' => $e->getMessage(),
            ], 400);
        }
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail(Auth::id());
            if ($user->isPanitia() || $user->isJuri()) {
                $validate = $request->validate([
                    'nama_lomba' => 'sometimes|string|max:255',
                    'deskripsi' => 'sometimes|string',
                    'link_gdrive' => 'sometimes|url',
                    'isAccepted' => 'sometimes|boolean',
                    'total_peminat_tahun_lalu' => 'sometimes|integer|min:0',
                    'total_peminat_tahun_sekarang' => 'sometimes|integer|min:0',
                ]);

                $update_data = master_lomba::findOrFail($id);

                $update_data->update([
                    'nama_lomba' => $validate['nama_lomba'],
                    'deskripsi' => $validate['deskripsi'] ?? $update_data->deskripsi,
                    'link_gdrive' => $validate['link_gdrive'] ?? $update_data->link_gdrive,
                    'isAccepted' => $validate['isAccepted'] ?? $update_data->isAccepted,
                    'total_peminat_tahun_lalu' => $validate['total_peminat_tahun_lalu'] ?? $update_data->total_peminat_tahun_lalu,
                    'total_peminat_tahun_sekarang' => $validate['total_peminat_tahun_sekarang'] ?? $update_data->total_peminat_tahun_sekarang,
                ]);

                return response()->json([
                    'message' => 'Success',
                    'data' => $update_data,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Maaf Anda Tidak Memiliki Akses',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        try {
            $competition = master_lomba::findOrFail($id);
            return response()->json([
                'message' => 'Success',
                'data' => $competition,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Data Tidak Ditemukan',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail(Auth::id());
            if ($user->isPanitia() || $user->isJuri()) {
                $competition = master_lomba::findOrFail($id);
                $competition->delete();

                return response()->json([
                    'message' => 'Success, data has been soft deleted',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Maaf Anda Tidak Memiliki Akses',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
