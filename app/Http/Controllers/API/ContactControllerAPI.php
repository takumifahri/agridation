<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\AutoResponse;
use App\Mail\ContactEmail;
use App\Models\ContactUs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = User::findOrFail(Auth::id());

        if ($user->isAdmin()) {
            $data = ContactUs::all();
            return response()->json([
                'message' => 'Data retrieved successfully',
                'status' => 'success',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
{
    try{
        $validate = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ],
        [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Email tidak valid',
            'subject.required' => 'Subjek harus diisi',
            'phone.required' => 'Nomor HP harus diisi',
            'message.required' => 'Pesan harus diisi',
        ]);

        // Save to database
        $contact = ContactUs::create([
            'name' => $validate['name'],
            'email' => $validate['email'],
            'phone' => $validate['phone'],
            'subject' => $validate['subject'],
            'message' => $validate['message'],
        ]);

        // Menggunakan alamat email yang telah ditentukan
        $reciever = config('contact.recipient_email', 'fahri.radiansyah@gmail.com');
        
        // Send email notification to the administrator
        Mail::to($reciever)->send(new ContactEmail($contact));
        
        // Send auto-response back to the user
        Mail::to($contact->email)->send(new AutoResponse($contact));

        return response()->json([
            'message' => 'Pesan berhasil dikirim',
            'data' => $contact,
            'status' => 'success'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
            'status' => 'error'
        ], 500);
    }   
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $user = User::findOrFail(Auth::id());

        if ($user->isAdmin()) {
            try{
                $data = ContactUs::find($id);
                if ($data !== null) {
                    return response()->json([
                        'message' => 'Data retrieved successfully',
                        'data' => $data
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Contact Us not found'
                    ], 404);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status' => 'error'
                ]);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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
