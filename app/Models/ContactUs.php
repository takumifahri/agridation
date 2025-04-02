<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;
use App\Mail\ContactEmail;
class ContactUs extends Model
{
    //
    use HasFactory, HasApiTokens, Notifiable, SoftDeletes;
    public $fillable = [
        'name', 
        'email', 
        'phone',
        'subject', 
        'message'
    ];

  
    
}
