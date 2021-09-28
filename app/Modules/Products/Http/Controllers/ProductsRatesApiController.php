<?php

namespace App\Modules\Products\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Models\ShopProductLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductsRatesApiController extends MyBaseApiController
{

    public function productIsFav(Request $request) {
        try {
            $this->validateApiRequest(
                ['product_id'],
                [
                    'product_id' => 'exists:shop_product,id',
                ],
                ['product_id']
            );

            $data = [
                'product_id' => $request->product_id,
                'user_id'    => Auth::user()->id
            ];

            $is_fav = ShopProductLike::where($data)->first();

            return $this->successResponseWithData(
                [
                    'is_fav' => $is_fav ? true : false
                ]
            );
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

}
