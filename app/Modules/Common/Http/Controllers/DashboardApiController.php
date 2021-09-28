<?php

namespace App\Modules\Common\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Modules\Products\Models\ShopProduct;
use App\Modules\Products\Transformers\ProductSearchTransformer;
use App\Modules\Products\Transformers\ProductTransformer;
use App\Modules\Sellers\Models\Store;
use App\Modules\Sellers\Transformers\SellerTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends MyBaseApiController
{
    public function whats_new(Request $request) {
        try {
            $latest_stores = Store::approvedAndVisible()
                                  ->with('description')
                                  ->withCount(
                                      [
                                          'rates',
                                          'rates as sum_rates' => function ($q) {
                                              $q->select(DB::raw('sum(rate)'));
                                          }
                                      ]
                                  )->orderBy('id', 'desc')->take(5)->get();

            $latest_stores_data = SellerTransformer::collection($latest_stores);

            $latest_products = ShopProduct::with(
                [
                    'productDescription' => function ($q) {
                        $q->addSelect('product_id', 'name', 'description', 'content', 'ingredients', 'allergies', 'gluten_free', 'weight');
                    }
                ]
            )->latest()->take(5)->get();

            $latest_products_data = ProductTransformer::collection($latest_products);

            return $this->successResponseWithData(
                [
                    "latest_stores"   => $latest_stores_data,
                    "latest_products" => $latest_products_data,
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

    public function listProducts(Request $request) {
        try {
            $this->validateApiRequest(
                ['category_id'],
                [
                    //                    'category_id'=>'required_without:',
                ]
            );

            $products = ShopProduct::byCategoryId($request->category_id)
                                    ->where('out_of_stock', 0)
                                    ->with(['productDescription', 'store'])
                                    ->whereHas('store', function($q) {
                                            $q->where('status', 1)->where('approval', 1)->where('accept_orders', 1);
                                     });

            $products = $products->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = ProductTransformer::collection($products);
            return $this->successResponseWithDataPaginated($data);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function listPromotionProducts(Request $request) {
        try {
            $products = ShopProduct::where('status', 1)
                                   ->whereHas('validPromotions')
                                   ->whereHas('store', function($q) {
                                           $q->where('status', 1)->where('approval', 1)->where('accept_orders', 1);
                                    })
                                   ->with(['validPromotions', 'productDescription']);

            $products = $products->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = ProductTransformer::collection($products);
            return $this->successResponseWithDataPaginated($data);
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
