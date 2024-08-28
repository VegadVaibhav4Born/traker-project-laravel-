<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Register extends Authenticatable
{
    use HasFactory;

    protected $table = "traking_user";
    protected $primaryKey = "id";
    protected $fillable = ['email', 'password']; // Adjust as necessary
}