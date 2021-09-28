<?php

namespace App\Modules\Sellers\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Modules\Products\Models\ShopProduct;
use App\Modules\Products\Models\ShopProductDescription;
use App\Modules\Products\Transformers\ProductTransformer;
use App\Modules\Sellers\Models\Store;
use App\Modules\Sellers\Models\StoreLike;
use App\Modules\Sellers\Models\StoreRate;
use App\Modules\Sellers\Transformers\SellerSearchTransformer;
use App\Modules\Sellers\Transformers\SellerTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class SellersApiController extends MyBaseApiController
{
    public function listSellers(Request $request) {
        try {
            $sellers = Store::approvedAndVisible()
                            ->with('description')
                            ->has('products')
                            ->with(
                                     [
                                         'banners',
                                         'featureProduct',
                                         'featureProduct.productDescription' => function ($q) {
                                             $q->addSelect('product_id', 'name', 'description', 'content', 'ingredients', 'allergies', 'gluten_free', 'weight');
                                         }
                                     ]
                            );

            if ($request->has('category_id') && $request->category_id) {
                $sellers->whereHas(
                    'categories',
                    function (Builder $q) use ($request) {
                        $q->where('shop_category.id', $request->category_id);
                    }
                );
            }

            if ($request->has('sub_location_id') && $request->sub_location_id) {
                $sellers->whereHas(
                    'subLocations',
                    function (Builder $q) use ($request) {
                        $q->where('sub_locations.id', $request->sub_location_id);
                    }
                );
            }

            if ($request->has('emirates_id') && $request->emirates_id) {
                $sellers->where('emirates_id', $request->emirates_id);
            }

            $sellers = $sellers->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = SellerTransformer::collection($sellers);

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

    public function menu(Request $request) {
        try {
            $this->validateApiRequest(
                ['store_id'],
                [
                    'store_id' => 'exists:admin_store,id'
                ],
                ['store_id']
            );
            //            $sort_order = request('sort_order') ?? 'sort_asc';
            //            $sort = explode('_', $sort_order);
            //            $keyword = request('keyword') ?? '';

            $seller = Store::approvedAndVisible()
                           ->where('id', $request->store_id)
                           ->with(
                               [
                                   'description',
                                   'categories',
                                   'categories.Categorydescription'         => function ($q) {
                                       $q->addSelect('category_id', 'name');
                                   },
                                   'categories.products'                    => function ($q) use ($request) {
                                       $q->where('store_id', $request->store_id);
                                       $q->where('status',1);
                                       $q->where('out_of_stock',0);
                                       if ($request->has('sort_order')) {
                                           $sort = explode('_', $request->sort_order);
                                           $q->sort($sort[0], $sort[1]);
                                       }
                                       if ($request->has('keyword')) {
                                           $product_ids = ShopProductDescription::where('name', 'like', "%{$request->keyword}%")
                                                                                ->pluck('product_id')->toArray();
                                           $q->whereIn('id', $product_ids);
                                           //                                           $q->withCount('userLikes');
                                       }
                                   },
                                   'categories.products.productDescription' => function ($q) use ($request) {
                                       $q->addSelect('product_id', 'name', 'description', 'content', 'ingredients', 'allergies', 'gluten_free', 'weight');
                                   },

                                   'categories.products.images',
                                   'featureProduct',
                                   'featureProduct.images',
                                   'featureProduct.productDescription'      => function ($q) {
                                       $q->addSelect('product_id', 'name');

                                   },
                                   'brand',
                                   'brand.subLocations',
                                   'brand.subLocations.location',

                               ]
                           )
                           ->withCount(
                               [
                                   'rates',
                                   'rates as sum_rates' => function ($q) {
                                       $q->select(DB::raw('sum(rate)'));
                                   },
                                   'likes'              => function ($q) {
                                       $q->where('user_id', Auth::user()->id);
                                   },
                               ]
                           )
                           ->first();
            $data   = new SellerTransformer($seller, ['featureProduct', 'cartItems']);
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

    public function menuItem(Request $request) {
        try {
            $this->validateApiRequest(
                ['store_id', 'product_id'],
                [
                    'store_id'   => 'exists:admin_store,id',
                    'product_id' => 'exists:shop_product,id'
                ],
                ['store_id', 'product_id']
            );
            $product = ShopProduct::where('id', $request->product_id)
                                  ->where('store_id', $request->store_id)
                                  ->with('images', 'productDescription', 'store')
                                  ->withCount(
                                      [
                                          'rates',
                                          'rates as sum_rates' => function ($q) {
                                              $q->select(DB::raw('sum(rate)'));
                                          }
                                      ]
                                  )
                                  ->firstOrFail();

            $data = new ProductTransformer($product);
            return $this->successResponseWithData($data);
        } catch (ModelNotFoundException $e) {
            $message = trans('Product Not Found');
            return $this->errorResponse($message);
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function favStores(Request $request) {
        try {
            $sellers = Store::where('status', 1)
                            ->with('description')
                            ->withCount(
                                [
                                    'rates',
                                    'rates as sum_rates' => function ($q) {
                                        $q->select(DB::raw('sum(rate)'));
                                    }
                                ]
                            );

            if ($request->has('with_feature_product') && $request->with_feature_product == 'true') {
                $sellers = $sellers->with(
                    [
                        'featureProduct',
                        'featureProduct.productDescription' => function ($q) {
                            $q->addSelect('product_id', 'name', 'description', 'content', 'ingredients', 'allergies', 'gluten_free', 'weight');
                        }
                    ]
                );
            }

            if ($request->has('must_has_feature_product') && $request->must_has_feature_product == 'true') {
                $sellers = $sellers->has('featureProduct');
            }

            if ($request->has('category_id') && $request->category_id) {
                $sellers->whereHas(
                    'categories',
                    function (Builder $q) use ($request) {
                        $q->where('shop_category.id', $request->category_id);
                    }
                );
            }

            if ($request->has('sub_location_id') && $request->sub_location_id) {
                $sellers->whereHas(
                    'subLocations',
                    function (Builder $q) use ($request) {
                        $q->where('sub_locations.id', $request->sub_location_id);
                    }
                );
            }

            $sellers->whereHas(
                'likes',
                function (Builder $q) use ($request) {
                    $q->where('store_likes.user_id', Auth::user()->id);
                }
            );

            $sellers = $sellers->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = SellerTransformer::collection($sellers);

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

    public function favStoresActions(Request $request) {
        try {
            $this->validateApiRequest(
                ['store_id', 'action'],
                [
                    'store_id' => 'exists:admin_store,id',
                    'action'   => 'in:like,dislike'
                ],
                ['store_id']
            );

            $data = [
                'store_id' => $request->store_id,
                'user_id'  => Auth::user()->id
            ];

            if ($request->action == 'like') {
                StoreLike::firstOrCreate($data);
            } else {
                StoreLike::where($data)->delete();
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

    public function searchStores(Request $request) {
        try {
            $this->validateApiRequest(
                ['keyword']
            );

            $sellers = Store::approvedAndVisible()
                            ->whereHas(
                                'description',
                                function ($q) use ($request) {
                                    $q->where('title', 'like', "%$request->keyword%");
                                }
                            )
                            ->with('description')
                            ->with('banners')
                            ->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = SellerSearchTransformer::collection($sellers);

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

    public function rateStore(Request $request) {
        try {
            $this->validateApiRequest(
                ['store_id', 'rate'],
                [
                    'store_id' => 'exists:admin_store,id',
                    'rate'     => 'in:1,2,3,4,5'
                ],
                ['store_id']
            );

            $store_rate = StoreRate::where(
                [
                    'store_id' => $request->store_id,
                    'user_id'  => Auth::user()->id,
                ]
            )->first();

            if ($store_rate) {
                $store_rate->rate = $request->rate;
                $store_rate->save();
            } else {
                $data = [
                    'store_id' => $request->store_id,
                    'user_id'  => Auth::user()->id,
                    'rate'     => $request->rate,
                ];
                StoreRate::create($data);
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
