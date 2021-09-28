<?php

namespace App\Modules\RateReview\Http\Controllers;

use App\Core\MyBaseApiController;
use Illuminate\Http\Request;
use App\Modules\RateReview\Http\Requests\CustomerRateOrder;
use Illuminate\Support\Facades\DB;
use Auth;

use App\Modules\Orders\Services\OrderService;
use App\Modules\Sellers\Services\StoreService;
use App\Modules\Products\Services\ProductService;
use App\Modules\Notification\Services\NotificationService;

use App\Modules\RateReview\Models\OrderReview;
use App\Modules\RateReview\Models\ProductRate;
use App\Modules\RateReview\Transformers\OrderReviewTransformer;
use App\Modules\RateReview\Transformers\ProductRatingTransformer;

class OrderRatingController extends MyBaseApiController
{

    public function create(CustomerRateOrder $request)
    {

      try{
          DB::beginTransaction();

          $review_data = $request->only(['order_id', 'review']);
          $order_review = $this->storeOrderReview($review_data);
          $products_rating = $this->storeProductsRating($request);

          $trans_view = new OrderReviewTransformer($order_review);
          $trans_rating = ProductRatingTransformer::collection($products_rating);

          $response = array_merge($trans_view->toArray(), ['products' => $trans_rating]);
          $this->sendRatingNotifications($order_review->order_id);
          DB::commit();

          return $this->successResponseWithData( $response, trans('order_rating.api.success'));
      }catch (\Exception $e) {
          DB::rollBack();
          if (app()->environment('local')) {
              $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
          } else {
              $message = trans('common.Something Went Wrong');
          }
          return $this->errorResponse($message);
      }
    }

    private function storeOrderReview($review_data)
    {
        $review = OrderReview::create($review_data);
        return $review;
    }//storeOrderReview

    private function storeProductsRating($request)
    {
        $product_data = array();
        foreach($request->products as $product){
          $product['order_id'] = $request->order_id;
          $product['user_id'] = Auth::user()->id;
          $product_data[] = $product;
        }
        $rating_insert = ProductRate::insert($product_data);
        $ratings = ProductRate::where('order_id', $request->order_id)->get();
        return $ratings;
    }//storeProductsRating

    public function sendRatingNotifications($order_id, $lang = 'en')
    {
      $order_review = OrderReview::find($order_id);

      $product_rating = $this->getProductRatingData($order_id);

      $user = Auth::user();
      $order = OrderService::getOrderbyCustomer($order_id, $user->id)->first();

      $store = StoreService::getStoreDetails($order->store_id, $lang);

      $data = compact('order_review', 'product_rating', 'order', 'user', 'store');

      //store in notification table
      NotificationService::orderRatingNotification($order->store_id, $data);

      $admin_config['to'] = env('MAIL_FROM_ADDRESS');
      $admin_config['subject'] = "Customer Has Rated Order";
      sc_send_mail('mail.order_rating.admin',$data, $admin_config);

      $store_config['to'] = $store->legal_business_email;
      $store_config['subject'] = "Customer Rated The Order";
      sc_send_mail('mail.order_rating.store',$data, $store_config);
    }//sendRatingNotifications

    private function getProductRatingData($order_id)
    {
      $product_rating = array();
      $rating = ProductRate::where('order_id', $order_id)->get();
      foreach($rating as $rate){
        $product = ProductService::getProductsById($rate->product_id);
        $rating_array = [
           'product_id' => $product->id,
           'product_name' => $product->singleDescription->name,
           'rating'   => $rate->rate
         ];
         $product_rating[] = $rating_array;
      }

      return $product_rating;
    }//getProductRatingData
}
