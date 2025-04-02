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

class Team_list extends Model
{
    //
    use HasApiTokens, Notifiable, SoftDeletes, HasFactory;
    protected $fillable = [
        'name_team',
        'lomba_id',
        'anggota',
        'status',
        'nama_pembimbing',
        'no_pembimbing',
        'peringkat',
        'update_at',
        'created_at',
        'deleted_at',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function lomba()
    {
        return $this->belongsTo(MasterLombaList::class, 'lomba_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'team_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'team_id');
    }
}
