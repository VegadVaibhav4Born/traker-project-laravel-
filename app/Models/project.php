<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class project extends Model
{
    use HasFactory;
    protected $table = "project";
    protected $primaryKey = "id";
    
    
     public function getMemberIdsAttribute($value)
    {
        return unserialize($value);
    }

    public function setMemberIdsAttribute($value)
    {
        $this->attributes['member_id'] = serialize($value);
    }
}
