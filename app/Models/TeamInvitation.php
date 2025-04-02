<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamInvitation extends Model
{
    //
    use HasFactory, HasApiTokens, Notifiable, SoftDeletes;  

    protected $fillable = [
        'team_id',
        'sender_id',
        'receiver_id',
        'status', // 'pending', 'accept', 'reject'
    ];

    public function team()
    {
        return $this->belongsTo(Team_list::class, 'team_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
