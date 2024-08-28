<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;
    protected $table = "activities";
    protected $primaryKey = "id";
    protected $fillable = [
        'title',
        'project_id',
        'member_id',
        'mouse_click',
        'keyboard_click',
        'screenshot',
        'software_use_name',
        
        'end_time',
        'durations',
    ];
}