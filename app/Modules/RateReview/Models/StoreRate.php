<?php

namespace App\Modules\RateReview\Models;

use App\Core\MyBaseApiModel;

class StoreRate extends MyBaseApiModel
{
    protected $fillable = ['store_id', 'user_id', 'rate', 'review', 'status'];

    public function scopeApprovedRatings($query)
    {
      return $query->where('status', config('rating.contants.STATUS.APPROVED'));
    }

    public function scopeRatingByStore($query, $store_id)
    {
      return $query->approvedRatings()->where('store_id', $store_id);
    }

    public function scopeRatingByCustomers($query, $user_id)
    {
      return $query->where('user_id', $user_id);
    }

    public function scopeWithOrderBy($query, $sort_order){
        $field = explode('__', $sort_order)[0];
        $sort_field = explode('__', $sort_order)[1];
        return $query->orderBy($field, $sort_field);
    }//scopeWithOrderBy

}
