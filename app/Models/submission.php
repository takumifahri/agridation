<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Team_list as Team;
use App\Models\master_lomba as MasterLombaList;
class submission extends Model
{
    //

    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'team_id',
        'lomba_id',
        'attachment_file',
        'status',
        'update_at',
        'created_at',
        'deleted_at',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function lomba()
    {
        return $this->belongsTo(MasterLombaList::class, 'lomba_id');
    }

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'submission_id');
    }
}
