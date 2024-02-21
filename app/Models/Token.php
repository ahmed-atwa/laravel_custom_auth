<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $guarded  = [];


    /**
     * each token belongs to a user in one-to-many relation
    */
    public function user(){
        return $this->belongsTo(User::class);
    }
}
