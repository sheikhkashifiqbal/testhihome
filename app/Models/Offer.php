<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OfferDescription;
use Carbon\Carbon;

class Offer extends Model
{
    protected $fillable = ['image', 'code', 'type', 'apply_to', 'value', 'start_date', 'end_date', 'status'];

    // this is a recommended way to declare event handlers
    public static function boot() {
        parent::boot();

        static::deleting(function($offers) { // before delete() method call this
             $offers->descriptions()->delete();
        });
    }

    public function descriptions()
    {
        return $this->hasMany(OfferDescription::class,'offer_id', 'id');
    }//descriptions

    public function scopeWithOrderBy($query, $sort_order){
        $field = explode('__', $sort_order)[0];
        $sort_field = explode('__', $sort_order)[1];
        return $query->orderBy($field, $sort_field);
    }//scopeWithOrderBy

    public function scopeWithStatus($query, $status){
        return $query->where('offers.status', $status);
    }//scopeWithStatus

    public function scopeWithSearchKeywords($query, $keyword){
        return $query->whereRaw('(offers.code like "%' . $keyword . '%" )');
    }//scopeWithSearchKeywords

}
