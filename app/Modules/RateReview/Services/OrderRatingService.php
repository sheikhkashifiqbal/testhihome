<?php
namespace App\Modules\RateReview\Services;

use App\Modules\RateReview\Models\OrderReview;
use App\Modules\RateReview\Models\ProductRate;
use App\Modules\RateReview\Transformers\OrderReviewTransformer;
use App\Modules\RateReview\Transformers\ProductRatingTransformer;

class OrderRatingService
{

  public static function getOrderRatingAndReview($order_id)
  {
    $order_review = OrderReview::where('order_id',$order_id)->first();
    if($order_review){
      $trans_view = new OrderReviewTransformer($order_review);
      $products_rating = ProductRate::where('order_id',$order_id)->get();
      $trans_rating = ProductRatingTransformer::collection($products_rating);
      
      return array_merge($trans_view->toArray(), ['products' => $trans_rating]);
    }
    return new \stdClass;

  }//getOrderRatingAndReview


}
