<?php

namespace App\Modules\SellersApp\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Models\ShopProductImage;
use App\Modules\Products\Models\ShopProduct;
use App\Modules\Products\Transformers\ProductImageTransformer;
use App\Modules\Products\Transformers\ProductTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use App\Models\AdminStoreShopCategory;
use Illuminate\Support\Facades\Storage;

use App\Modules\SellersApp\Http\Requests\CreateProductRequest;

class ProductsApiController extends MyBaseApiController {

    public function index(Request $request) {

        try {
            $this->validateApiRequest(
                    [], [
                'status' => 'in:all,0,1'
                    ]
            );
            $products = ShopProduct::where('store_id', Auth::user()->store_id)
                        ->whereHas('productDescription', function ($q) use ($request) {
                                    $q->where('name', 'like', "%$request->keyword%");
                                }
                        )
                        ->with(
                                [
                                    'productDescription' => function ($q) {
                                        $q->addSelect('product_id', 'name', 'description', 'ingredients', 'allergies', 'gluten_free', 'weight');
                                    },
                                    'images'
                                ]
                        );

            if ($request->has('status') && $request->status != 'all') {
                $products = $products->where('status', $request->status);
            }
            $products = $products->orderBy('id', 'desc');
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

    public function change_status(Request $request) {
        try {
            $this->validateApiRequest(
                    ['product_id', 'status'], [
                'product_id' => 'exists:shop_product,id',
                'status' => 'in:0,1'
                    ], ['product_id']
            );

            $product = ShopProduct::where('id', $request->product_id)
                    ->where('store_id', Auth::user()->store_id)
                    ->with(
                            [
                                'productDescription' => function ($q) {
                                    $q->addSelect('product_id', 'name', 'description', 'ingredients', 'allergies', 'gluten_free', 'weight');
                                }
                            ]
                    )
                    ->first();

            $product->status = $request->status;
            $product->save();

            $data = new ProductTransformer($product->fresh());

            return $this->successResponseWithData($data, trans('product.Product Status Changed Successfully'));
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function change_stock(Request $request) {
        try {
            $this->validateApiRequest(
                ['product_id', 'out_of_stock'], [
                'product_id' => 'exists:shop_product,id',
                'out_of_stock' => 'in:0,1'
                    ], ['product_id']
            );

            $product = ShopProduct::where('id', $request->product_id)
                    ->where('store_id', Auth::user()->store_id)
                    ->with(
                            [
                                'productDescription' => function ($q) {
                                    $q->addSelect('product_id', 'name', 'description', 'ingredients', 'allergies', 'gluten_free', 'weight');
                                }
                            ]
                    )
                    ->first();

            $product->out_of_stock = $request->out_of_stock;
            $product->save();
            $data = new ProductTransformer($product);

            return $this->successResponseWithData($data, trans('product.Product Status Changed Successfully'));
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function create(CreateProductRequest $request) {
        try {
            DB::beginTransaction();

            if ($request->hasFile('main_image')) {
                $image = $request->file('main_image');
                $extension = $image->extension();
                $file_name = env('AWS_PRODUCT_FOLDER_PATH').'/product_' . time() . '.' . $extension;
                $path = $request->file('main_image')->storeAs('', $file_name,'s3');
                $request->merge(['image' => $file_name]);
            }

            $brand_id = Auth::user()->brand_id;
            $store_id = Auth::user()->store_id;
            $request->merge(
                    [
                        //'sku' => $brand_id . '_' . $store_id . str_limit(str_slug($request->name['en'], '_'), 35) . '_' . time(),
                        'brand_id' => $brand_id,
                        'store_id' => $store_id,
                        'status' => 1
                    ]
            );

            if($request->is_feature == 1){
              $updateProducts = ShopProduct::where('store_id', $request->store_id)
                                  ->update(['is_feature' => 0]);
            }

            $product_data = $request->only(['brand_id', 'store_id', 'sku', 'price', 'image', 'is_feature', 'prepration_time', 'serve_count']);

            if($request->discount_percentage){
              $product_data['discount_percentage']  = $request->discount_percentage;
              $product_data['discount_start_date']  = Carbon::parse($request->discount_start_date)->format('Y-m-d');
              $product_data['discount_expiry_date'] = Carbon::parse($request->discount_expiry_date)->format('Y-m-d');
            }

            $product = ShopProduct::create($product_data);

            foreach ($request->details as $lang => $values) {
                $values['lang'] = $lang;
                $product->descriptions()->create($values);
            }

            $product->categories()->sync($request->category_id);
            $store_cateory_id=AdminStoreShopCategory::where('store_id',$store_id)->where('category_id',$request->category_id)->first();
            //dd($store_cateory_id);
            if(!$store_cateory_id){
                AdminStoreShopCategory::insert(['store_id'=>$store_id,'category_id'=>$request->category_id]);
            }
            $product = $product->fresh();
            $product->load('productDescription');

            $data = new ProductTransformer($product);
            DB::commit();
            return $this->successResponseWithData($data, trans('product.admin.create_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function update(CreateProductRequest $request) {
        try {
            DB::beginTransaction();

            $product = ShopProduct::find($request->product_id);

            $old_main_image = '';
            $request->merge(['status' => 0]);

            if ($request->hasFile('main_image')) {
                $image = $request->file('main_image');
                $extension = $image->extension();
                $file_name = env('AWS_PRODUCT_FOLDER_PATH').'/product_' . time() . '.' . $extension;
                $path = $request->file('main_image')->storeAs('', $file_name,'s3');
                $request->merge(['image' => $file_name]);
                $old_main_image = $product->image;
            }

            $product_data = $request->only(['sku', 'price', 'image', 'status', 'is_feature', 'prepration_time', 'serve_count']);

            if($request->discount_percentage > 0){
              $product_data['discount_percentage'] = $request->discount_percentage;
              $product_data['discount_start_date']  = Carbon::parse($request->discount_start_date)->format('Y-m-d');
              $product_data['discount_expiry_date'] = Carbon::parse($request->discount_expiry_date)->format('Y-m-d');
            }else{
              $product_data['discount_percentage'] = $request->discount_percentage;
              $product_data['discount_start_date']  = null;
              $product_data['discount_expiry_date'] = null;
            }

            //dd($product_data);
            $product->update($product_data);

            if($request->is_feature == 1){
              $updateProducts = ShopProduct::where('store_id', $product->store_id)
                                          ->where('id', '<>', $product->id)
                                           ->update(['is_feature' => 0]);
            }

            $product->categories()->sync($request->category_id);

            foreach ($request->details as $lang => $values) {
                $values['lang'] = $lang;
                foreach ($values as $field => $value) {
                    $product_description = $product->descriptions()
                            ->where('lang', $lang)
                            ->first();
                }
                if ($product_description) {
                    $product->descriptions()->where('lang', $lang)
                            ->update($values);
                } else {
                    $product->descriptions()->create($values);
                }
            }

            if (!empty($old_main_image)) {
                unlink(public_path($old_main_image));
            }
            $store_id = Auth::user()->store_id;
            $store_cateory_id=AdminStoreShopCategory::where('store_id',$store_id)->where('category_id',$request->category_id)->first();
            //dd($store_cateory_id);
            if(!$store_cateory_id){
                AdminStoreShopCategory::insert(['store_id'=>$store_id,'category_id'=>$request->category_id]);
            }
            $cofig['to'] =  env('MAIL_FROM_ADDRESS');
            $cofig['subject'] = '【HiHOME.app】Seller Account Status';
            $prod=$request->details[$request->header('lang')]['name'];

           $url_pr=url("/backend/product/edit/").'/'.$request->product_id;

            $data = array('title' => 'product Status','content'=>'the product <a target="_blank" class="button button-primary" href="'.$url_pr.'">'.$prod.'</a> was changed from the'.Auth::user()->first_name.' '.Auth::user()->last_name);
            $A = Mail::queue(new SendMail('mail.product_inactive', $data, $cofig));
            $product = $product->fresh();
            $product->load('productDescription');
            $data = new ProductTransformer($product);
            DB::commit();
            return $this->successResponseWithData($data, trans('product.admin.edit_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function destroy(Request $request) {
        try {
            DB::beginTransaction();
            $this->validateApiRequest(
                    [
                'product_id'
                    ], [
                'product_id' => 'exists:shop_product,id'
                    ], ['product_id']
            );

            $product = ShopProduct::find($request->product_id);
            $product->descriptions()->delete();
            $product_images = $product->images();
            if (file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }
            foreach ($product_images as $image) {
                if (file_exists(public_path($image->image))) {
                    unlink(public_path($image->image));
                }
                $image->delete();
            }
            //todo delete all related tables for this product
            $product->delete();
            DB::commit();
            return $this->successEmptyResponse(trans('product.admin.delete_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function addProductImages(Request $request) {
        try {
            DB::beginTransaction();
            $this->validateApiRequest(
                    [
                'product_id',
                'images'
                    ], [
                'product_id' => 'exists:shop_product,id',
                'images' => 'array',
                    ], ['product_id']
            );

            $images_ids = [];
            foreach ($request->file('images') as $image) {
                $extension = $image->extension();
                $file_name = env('AWS_PRODUCT_FOLDER_PATH').'/product_' . $request->product_id . '_' . time() . '_' . str_slug($image->getFilename()) . '.' . $extension;

                $path = $image->storeAs('', $file_name,'s3');
                $saved_image = ShopProductImage::create(
                                [
                                    'product_id' => $request->product_id,
                                    'image' => $file_name,
                                ]
                );
                $images_ids[] = $saved_image->id;
            }

            $images = ShopProductImage::whereIn('id', $images_ids)->get();
            $data = ProductImageTransformer::collection($images);

            DB::commit();
            return $this->successResponseWithData($data, trans('product.admin.images_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function removeProductImage(Request $request) {
        try {
            DB::beginTransaction();
            $this->validateApiRequest(
                    [
                'product_id',
                'image_id'
                    ], [
                'product_id' => 'exists:shop_product,id',
                    ], ['product_id']
            );
            //todo unlink file to delete it
            $product_image = ShopProductImage::find($request->image_id);
            if($product_image){
              Storage::disk('s3')->delete($product_image->image);
              $product_image->delete();
            }
            DB::commit();
            return $this->successEmptyResponse(trans('product.admin.images_removed_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function removeProductMainImage(Request $request) {
        try {
            DB::beginTransaction();
            $this->validateApiRequest(
                    [
                'product_id',
                    ], [
                'product_id' => 'exists:shop_product,id',
                    ], ['product_id']
            );
            $product = ShopProduct::find($request->product_id);

            if (!empty($product->image)) {
              Storage::disk('s3')->delete($product->image);
            }
            $product->image = '';
            $product->save();
            DB::commit();
            return $this->successEmptyResponse(trans('product.admin.images_removed_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

}
