<?php

namespace App\Modules\Products\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Models\ShopProductLike;
use App\Modules\Products\Models\ProductRate;
use App\Modules\Products\Models\ShopProduct;
use App\Modules\Products\Transformers\ProductSearchTransformer;
use App\Modules\Products\Transformers\ProductTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductsApiController extends MyBaseApiController
{
    public function topRated(Request $request) {
        try {
            $top_rated_products = ShopProduct::where([
                                            ['status','=',1],
                                            ['out_of_stock','=',0],
                                            ])
                                            ->withCount('orderDetails')
                                             ->withCount(
                                                 [
                                                     'rates as average_rating' => function ($q) {
                                                         $q->select(DB::raw('AVG(rate)'));
                                                     }
                                                 ]
                                             )
                                             ->with(['productDescription', 'store'])
                                             ->whereHas('store', function($q) {
                                                     $q->where('status', 1)->where('approval', 1)->where('accept_orders', 1);
                                              });

            $top_rated_products = $top_rated_products->orderBy('average_rating', 'desc')
                                                     ->limit(10)->get();

            $data = ProductTransformer::collection($top_rated_products);
            return $this->successResponseWithData($data);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }

    }

    public function topReview(Request $request) {
        try {
            $top_rated_products = ShopProduct::where([
                                              ['status','=',1],
                                              ['out_of_stock','=',0],
                                            ])
                                            ->withCount('likes')
                                             ->whereHas('likes')
                                             ->withCount(
                                                 [
                                                     'rates',
                                                     'rates as sum_rates' => function ($q) {
                                                         $q->select(DB::raw('sum(rate)'));
                                                     }
                                                 ]
                                             )
                                             ->with(['productDescription', 'store'])
                                             ->whereHas('store', function($q) {
                                                     $q->where('status', 1)->where('approval', 1)->where('accept_orders', 1);
                                              });

            $top_rated_products = $top_rated_products->orderBy('likes_count', 'desc')
                                                     ->limit(10)->get();

            $data = ProductTransformer::collection($top_rated_products);
            return $this->successResponseWithData($data);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }

    }

    public function favProducts(Request $request) {
        try {
            $fav_products = ShopProduct::whereHas('user_likes')
                                       ->withCount(
                                           [
                                               'rates',
                                               'rates as sum_rates' => function ($q) {
                                                   $q->select(DB::raw('sum(rate)'));
                                               }
                                           ]
                                       )
                                       ->with(
                                           [
                                               'productDescription' => function ($q) {
                                                   $q->addSelect('product_id', 'name', 'description', 'content', 'ingredients', 'allergies', 'gluten_free', 'weight');
                                               },
                                               'store'
                                           ]
                                       )->whereHas('store', function($q) {
                                               $q->where('status', 1)->where('approval', 1)->where('accept_orders', 1);
                                        });


            //                        if ($request->has('with_seller') && $request->with_seller == true) {
            //                            $fav_products = $fav_products->with(
            //                                [
            //                                    'brand'
            //                                ]
            //                            );
            //                        }

            $fav_products = $fav_products->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = ProductTransformer::collection($fav_products);
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

    public function favProductsActions(Request $request) {
        try {
            $this->validateApiRequest(
                ['product_id', 'action'],
                [
                    'product_id' => 'exists:shop_product,id',
                    'action'     => 'in:like,dislike'
                ],
                ['product_id']
            );

            $data = [
                'product_id' => $request->product_id,
                'user_id'    => Auth::user()->id
            ];

            if ($request->action == 'like') {
                ShopProductLike::firstOrCreate($data);
            } else {
                ShopProductLike::where($data)->delete();
            }

            return $this->successEmptyResponse();
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

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

    public function searchProducts(Request $request) {
        try {
            $this->validateApiRequest(
                ['keyword']
            );


            $products = ShopProduct::
            where([
              ['status','=',1],
              ['out_of_stock','=',0],
            ])
            ->whereHas(
                'productDescription',
                function ($q) use ($request) {
                    $q->where('name', 'like', "%$request->keyword%")
                       ->where('status',1);
                    $q->addSelect('status');
                }
            )->with(
                [
                    'productDescription' => function ($q) {
                        $q->addSelect('product_id', 'name', 'description', 'content', 'ingredients', 'allergies', 'gluten_free', 'weight');
                    },
                    'store'
                ]
            )->whereHas('store', function($q) {
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

    public function rateProduct(Request $request) {
        try {
            $this->validateApiRequest(
                ['product_id', 'rate'],
                [
                    'product_id' => 'exists:shop_product,id',
                    'rate'       => 'in:1,2,3,4,5'
                ],
                ['product_id']
            );

            $product_rate = ProductRate::where(
                [
                    'product_id' => $request->product_id,
                    'user_id'    => Auth::user()->id,
                ]
            )->first();

            if ($product_rate) {
                $product_rate->rate = $request->rate;
                $product_rate->save();
            } else {
                $data = [
                    'product_id' => $request->product_id,
                    'user_id'    => Auth::user()->id,
                    'rate'       => $request->rate,
                ];
                ProductRate::create($data);
            }
            return $this->successEmptyResponse();
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
