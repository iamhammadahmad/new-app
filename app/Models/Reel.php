<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reel extends Model
{
    protected $fillable = ['title' , 'description' , 'video' , 'user_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
