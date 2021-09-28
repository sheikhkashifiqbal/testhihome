<?php

namespace App\Modules\Offers\Models;

use App\Core\MyBaseApiModel;
use App\Modules\Offers\Models\OfferDescription;
use App\Modules\Offers\Models\OfferTypes;
use App\Modules\Offers\Models\OfferSeller;
use App\Modules\Offers\Models\OfferProduct;
use Carbon\Carbon;

class Offer extends MyBaseApiModel
{
    protected $fillable = ['image', 'code', 'type', 'apply_to', 'value', 'start_date', 'end_date', 'status'];

    // this is a recommended way to declare event handlers
    public static function boot() {
        parent::boot();

        static::deleting(function($offers) { // before delete() method call this
             $offers->descriptions()->delete();
             $offers->sellers()->delete();
             $offers->products()->delete();
             // do the rest of the cleanup...
        });
    }

    public function singleDescription()
    {
        return $this->hasOne(OfferDescription::class,'offer_id', 'id');
    }//singleDescription

    public function descriptions()
    {
        return $this->hasMany(OfferDescription::class,'offer_id', 'id');
    }//descriptions

    public function sellers()
    {
        return $this->hasMany(OfferSeller::class,'offer_id', 'id');
    }//sellers

    public function products()
    {
        return $this->hasMany(OfferProduct::class,'offer_id', 'id');
    }//products

    public function singType()
    {
        return $this->hasOne(OfferTypes::class,'keyword','type');
    }//singType

    public function scopeValidOffer($query)
    {
      return $query->where('status', 1)
                   ->where(function ($q) {
                      $q->where('start_date', '<=', Carbon::now());
                      $q->where('end_date', '>=', Carbon::now());
                    });
    }//scopeValidOffer

    public function scopeOfferValidForSeller($query, $seller_id)
    {
      return $query->whereHas('sellers', function($q) use($seller_id) {
                                $q->where('seller_id', $seller_id);
                              });
    }//scopeOfferValidForSeller

    public function scopeDescriptionByHeaderLang($query)
    {
      $lang = request()->header('lang') ?: 'en';

      return $query->with(['singleDescription' => function ($q) use ($lang) {
                              $q->where('lang', $lang);
                          }]);
    }//scopeDescriptionByHeaderLang

    public function scopeWithProductsBySeller($query, $seller_id)
    {
      return $query->with(['products' => function($q) use($seller_id) {
                                $q->where('seller_id', $seller_id);
                              }]);
    }//scopeWithProductsBySeller

    public function scopeOnlyForAllOffers($query)
    {
      return $query->where('apply_to', config('offers.contants.OFFER_APPLY_TO.ALL'));
    }//scopeonlyForAllOffers

    

}
