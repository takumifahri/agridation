<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Team_list as Team;
use App\Models\master_lomba as MasterLombaList;
use App\Models\transactions as Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class transactions extends Model
{
    //
    use HasApiTokens, Notifiable, SoftDeletes, HasFactory;
    protected $fillable = [
        'team_id',
        'type_payment',
        'nominal',
        'bukti_file',
        'update_at',
        'created_at',
        'deleted_at',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
