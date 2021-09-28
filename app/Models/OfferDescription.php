<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferDescription extends Model
{
    public $timestamps = false;
    protected $fillable = ['lang', 'title', 'description'];
}
