<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Team_list as TeamList;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'asal_sekolah',
        'team_id',
        'role',
        'google_id',
        'update_at',
        'created_at',
        'deleted_at',
        'unique_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // Role helper methods
    public function isPeserta()
    {
        return $this->role === 'peserta';
    }

    public function isPanitia()
    {
        return $this->role === 'panitia';
    }

    public function isJuri()
    {
        return $this->role === 'juri';
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function team()
    {
        return $this->belongsTo(TeamList::class, 'team_id');
    }

    public function penilaianAsJuri()
    {
        return $this->hasMany(Penilaian::class, 'juri_id');
    }

}
