<?php

namespace App\Modules\RateReview\Http\Controllers;

use App\Core\MyBaseApiController;
use Illuminate\Http\Request;

use App\Modules\Products\Services\ProductService;

use App\Modules\RateReview\Models\ProductRate;
use App\Modules\RateReview\Transformers\SellerProductListTransformer;

class ProductRatingController extends MyBaseApiController
{

  public function getProductRatingList(Request $request)
  {

    try{
      $this->validateApiRequest(
          ['product_id'],
          [
              'product_id' => 'exists:shop_product,id'
          ]
      );

      $products_rating = array();
      $rating = ProductRate::where('product_id', $request->product_id);
      $ratings = $rating->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));


      $trans_rating = SellerProductListTransformer::collection($ratings);

      return $this->successResponseWithDataPaginated($trans_rating);

      }catch (\Exception $e) {
          if (app()->environment('local')) {
              $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
          } else {
              $message = trans('common.Something Went Wrong');
          }
          return $this->errorResponse($message);
      }

  }

}
