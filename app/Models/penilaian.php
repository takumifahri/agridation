<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class penilaian extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    protected $table = 'penilaian';

    protected $fillable = [
        'team_id',
        'submission_id',
        'juri_id',
        'nilai',
        'attachment_file',
        'saran_comment',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    public function juri()
    {
        return $this->belongsTo(User::class, 'juri_id');
    }
}
