<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Team_list as Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TeamInvitation as Invitation;
class TeamControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = User::findOrFail(Auth::id());
        if($user->isPanitia() || $user->isJuri()){
            try{
                $teams = Team::all();
                if($teams->isEmpty()){
                    return response()->json([
                        'message' => 'Data Tidak Ditemukan',
                    ], 404);
                } else{
                    return response()->json([
                        'message' => 'Success',
                        'data' => $teams,
                    ], 200);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed',
                    'error' => $e->getMessage(),
                ], 400);
            } 
        } else {
            return response()->json([
                'message' => 'Maaf Anda Tidak Memiliki Akses',
            ], 401);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create_team_invitation(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    
        try {
            $validate = $request->validate([
                'name_team' => 'required|string|max:255',
                'lomba_id' => 'required|exists:master_lombas,id',
                'invited_members' => 'required|array|min:1',
                'invited_members.*' => 'exists:users,unique_id',
                'nama_pembimbing' => 'nullable|string|max:255',
                'no_pembimbing' => 'nullable|string|max:20',
            ]);
    
            // Check if invited members exist
            $checkUsers = User::whereIn('unique_id', $validate['invited_members'])->get();
            if ($checkUsers->count() !== count($validate['invited_members'])) {
                return response()->json([
                    'message' => 'Some of the selected members do not exist',
                ], 400);
            }
    
            // Create team with pending status
            $team = Team::create([
                'name_team' => $validate['name_team'],
                'lomba_id' => $validate['lomba_id'],
                'anggota' => json_encode([$user->unique_id]), // Initially only includes the creator
                'status' => 'pending',
                'nama_pembimbing' => $validate['nama_pembimbing']?? null,
                'no_pembimbing' => $validate['no_pembimbing']?? null,
            ]);
    
            if (!$team->save()) {
                return response()->json([
                    'message' => 'Failed to create team',
                ], 500);
            }
    
            // Create invitations for each invited member
            foreach ($validate['invited_members'] as $memberId) {
                if ($memberId != $user->unique_id) { // Don't send invitation to the creator
                    Invitation::create([
                        'team_id' => $team->id,
                        'sender_id' => $user->unique_id,
                        'receiver_id' => $memberId,
                        'status' => 'pending',
                    ]);
                }
            }
    
            return response()->json([
                'message' => 'Team created and invitations sent successfully',
                'data' => $team,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed creating a team',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function respond_to_invitation(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    
        try {
            $validate = $request->validate([
                'invitation_id' => 'required|exists:team_invitations,id',
                'response' => 'required|in:accept,reject',
            ]);
    
            $invitation = Invitation::findOrFail($validate['invitation_id']);
    
            // Check if the invitation is for this user
            if ($invitation->receiver_id != $user->unique_id) {
                return response()->json([
                    'message' => 'This invitation is not for you',
                ], 403);
            }
    
            // Check if invitation is still pending
            if ($invitation->status != 'pending') {
                return response()->json([
                    'message' => 'This invitation has already been responded to',
                ], 400);
            }
    
            // Update invitation status
            $invitation->status = $validate['response'];
            $invitation->save();
    
            // If accepted, add user to the team
            switch ($validate['response']) {
                case 'accept':
                    $team = Team::findOrFail($invitation->team_id);

                    // Decode current members, add new member, and re-encode
                    $currentMembers = json_decode($team->anggota, true);
                    if (!in_array($user->unique_id, $currentMembers)) {
                        $currentMembers[] = $user->unique_id;
                        $team->anggota = json_encode($currentMembers);
                        $team->save();
                    }

                    return response()->json([
                        'message' => 'You have successfully joined the team',
                        'team' => $team,
                    ], 200);
                default:
                    return response()->json([
                        'message' => 'You have rejected the team invitation',
                    ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to respond to invitation',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    // Function to get all pending invitations for a user
    public function get_pending_invitations()
    {
        $user = User::findOrFail(Auth::id());
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            $pendingInvitations = Invitation::where('receiver_id', $user->unique_id)
                ->where('status', 'pending')
                ->with(['team', 'sender'])
                ->get();

            return response()->json([
                'message' => 'Pending invitations retrieved successfully',
                'data' => $pendingInvitations,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve pending invitations',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    // Function to finalize team after all invitations are handled
    public function finalize_team(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        try {
            $validate = $request->validate([
                'team_id' => 'required|exists:team_lists,id',
            ]);

            $team = Team::findOrFail($validate['team_id']);

            // Check if the current user is the team creator
            $members = json_decode($team->anggota, true);
            if ($members[0] != $user->unique_id) {
                return response()->json([
                    'message' => 'Only team creator can finalize team',
                ], 403);
            }

            // Update team status to 'pembayaran' (or any other status you need)
            $team->status = 'pembayaran';
            $team->save();

            return response()->json([
                'message' => 'Team finalized successfully',
                'data' => $team,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to finalize team',
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
