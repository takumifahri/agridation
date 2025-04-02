<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Team_list;
class master_lomba extends Model
{
    //
   use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
   protected $fillable = [
        'nama_lomba',
        'deskripsi',
        'link_gdrive',
        'isAccepted',
        'total_peminat_tahun_lalu',
        'total_peminat_tahun_sekarang',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function teams()
    {
        return $this->hasMany(Team_list::class, 'lomba_id');
    } 
}
