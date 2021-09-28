<?php

namespace App\Modules\RateReview\Http\Controllers;

use App\Core\MyBaseApiController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Orders\Services\OrderService;
use App\Modules\Notification\Services\NotificationService;

use Auth;

use App\Modules\RateReview\Models\StoreRate;
use App\Modules\RateReview\Transformers\StoreRateFullTransformer;
use App\Modules\RateReview\Transformers\StoreRateWithCustomerTransformer;
use App\Modules\RateReview\Transformers\StoreRateWithStoreTransformer;
use App\Modules\RateReview\Http\Requests\RateSellerRequest;

class StoreRatingController extends MyBaseApiController
{

  public function create(RateSellerRequest $request)
  {
    $user = Auth::user();
    $userOrderCount = OrderService::usersDeliveredOrdersInStoreCount($user->id, $request->get('store_id'));

    if($userOrderCount <= 0 ){
        return $this->errorResponse(trans('rating.api.no_delivery_order'));
    }

    try{
      $rating = StoreRate::create($request->all());

      $tranferRating = new StoreRateFullTransformer($rating);

      $this->sendRatingNotifications("new", $tranferRating->toArray($rating));
      return $this->successResponseWithData( $tranferRating, trans('rating.api.success'));
    }catch (\Exception $e) {
        if (app()->environment('local')) {
            $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
        } else {
            $message = trans('common.Something Went Wrong');
        }
        return $this->errorResponse($message);
    }

  }//create

  private function sendRatingNotifications($type, $rating)
  {
    $store_config['to'] = $rating['seller']['email'];
    $store_config['subject'] = "You Got A Review In HiHome Platform";

    $admin_config['to'] = env('MAIL_FROM_ADDRESS');
    $admin_config['subject'] = "New Review Posted For Seller";

    if($type === "update"){
      $admin_config['subject'] = "Customer Updated Review For Seller";
      $store_config['subject'] = "Customer Has Updated The Review" ;
    }

    //store in notification table
    NotificationService::storeRatingNotification($rating['store_id'], $rating);

    sc_send_mail('mail.rating.admin', $rating, $admin_config);
    sc_send_mail('mail.rating.store', $rating, $store_config);
  }//sendRatingNotifications

  public function getSellerReviews(Request $request)
  {
    try{
      $this->validateApiRequest(
          ['store_id'],
          [
              'store_id' => 'exists:admin_store,id'
          ]
      );

      $ratings = StoreRate::ratingByStore($request->store_id);

      $ratings = $ratings->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));


      $data = StoreRateWithCustomerTransformer::collection($ratings);
      return $this->successResponseWithDataPaginated($data);

      }catch (\Exception $e) {
          if (app()->environment('local')) {
              $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
          } else {
              $message = trans('common.Something Went Wrong');
          }
          return $this->errorResponse($message);
      }
  }//getSellerReviews

  public function getCustomerSellersReviews(Request $request)
  {
    try{

      $ratings = StoreRate::ratingByCustomers(Auth::user()->id);

      $ratings = $ratings->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));
      //dd(request()->header('lang'));

      $data = StoreRateWithStoreTransformer::collection($ratings);
      return $this->successResponseWithDataPaginated($data);

      }catch (\Exception $e) {
          if (app()->environment('local')) {
              $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
          } else {
              $message = trans('common.Something Went Wrong');
          }
          return $this->errorResponse($message);
      }
  }//getCustomerSellersReviews

  public function getSellerCustomerReviews(Request $request)
  {
    try{

      $ratings = StoreRate::ratingByStore(Auth::user()->store_id);

      $ratings = $ratings->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

      $data = StoreRateWithCustomerTransformer::collection($ratings);
      return $this->successResponseWithDataPaginated($data);

      }catch (\Exception $e) {
          if (app()->environment('local')) {
              $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
          } else {
              $message = trans('common.Something Went Wrong');
          }
          return $this->errorResponse($message);
      }
  }//getSellerCustomerReviews

}
