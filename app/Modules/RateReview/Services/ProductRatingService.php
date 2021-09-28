<?php
namespace App\Modules\RateReview\Services;

use App\Modules\RateReview\Models\ProductRate;

class ProductRatingService
{

  public static function getProductsAverageRating()
  {
    $ratings = ProductRate::selectRaw('*, AVG(rate) average_rating')
                          ->groupBy('product_id')
                          ->orderBy('average_rating', 'desc');
    return $ratings;
  }//getStoreAverageRating

  public static function getProductAverageRating($product_id)
  {
    $ratings = ProductRate::ratingByProduct($product_id);
    return [
      'average_rating'     => round($ratings->avg('rate'), 1),
      'rating_count'       => $ratings->count()
    ];
  }//getStoreAverageRating


}
