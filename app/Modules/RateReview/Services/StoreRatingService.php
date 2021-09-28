<?php
namespace App\Modules\RateReview\Services;

use App\Modules\RateReview\Models\StoreRate;

class StoreRatingService
{

  public static function getStoreAverageRating($store_id)
  {
    $ratings = StoreRate::ratingByStore($store_id);
    return [
      'average_rating'     => round($ratings->avg('rate'), 1),
      'rating_count'       => $ratings->count()
    ];
  }//getStoreAverageRating

  public static function checkIfUserRatedStore($user_id, $store_id)
  {
    return StoreRate::where([
                          ['user_id', $user_id],
                          ['store_id', $store_id]
                        ])->count();
  }//checkIfUserRatedStore

  public static function getStoreReviews($store_id, $sort_order)
  {
     return StoreRate::withOrderBy($sort_order)->where('store_id', $store_id);
  }//getStoreReviews

  public static function getStoreReviewById($id)
  {
     return StoreRate::find($id);
  }//getStoreReviewById

  public static function getStatusesArray()
  {
     return [
       0 => strtoupper(trans('rating.status.0')),
       1 => strtoupper(trans('rating.status.1')),
       2 => strtoupper(trans('rating.status.2'))
     ];
  }//getStatusesArray

}
