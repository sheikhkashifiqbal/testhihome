<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Imports\ImagesImport;
use App\Models\ShopCategory as Category;
use App\Models\ShopProduct as Product;
use App\Models\ShopProductLike as Like;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;


class ProductController extends Controller
{


    public function index(Request $request) {

        /*
        do not show extra product
        do not show products which not available at stores
        */
        $input = $request->only(
            [
                'branch_id',
                'lang',
                'sort_asc',
                'keyword'

            ]
        );


        $this->validate(
            $request,
            [
                'branch_id' => 'required|integer'
            ]
        );
        /*
         Filter by: branch_id
            Get all categories of branch_id
        */

        $all_categories = Category::whereHas(
            'stores',
            function ($q) use ($input) {
                $q->where('admin_store.id', $input['branch_id']);
            }
        )->main()->active()->sort()->get();

        $i = 0;
        foreach ($all_categories as $c) {
            $menu[$i]['category_id'] = $c->id;
            $menu[$i]['name']=empty($c->details->name)?'':$c->details->name;
            $menu[$i]['image']=($c->image)?env('APP_URL').$c->image:'';
            $menu[$i]['items']=$this->processProductObj($request,$c->products,$c);//$this->get_products_by_category_id($request,$c->id);

            $i++;
        }

        return new JsonResponse([
            'status'=>1,
            'message' => 'done',
            'data' => $menu,
        ],200 );
    }
    public function list_items(Request $request) {

        /*
        do not show extra product
        do not show products which not available at stores
        */
        $input     = $request->only(
            [
                'brand_id',
                'branch_id',
                'lang',
                'sort_asc',
                'keyword'

            ]
        );
        $branch_id = $input['branch_id'] ?? null;
        if(!$branch_id) {
            //this is delivery validation
            $this->validate(
                $request,
                [
                    'brand_id' => 'required|integer',//delivery
                ]
            );
            /*
            Filter by: brand_id
                Get all categories of brand_id
            */

            $all_categories = Category::where('brand_id', $input['brand_id'])->main()->active()->sort()->get();

        }else {
            //this is for pickup or maybe delivery with spacific branch
            $this->validate(
                $request,
                [
                    'branch_id' => 'integer'//pickup
                ]
            );

            /*
            Filter by: branch_id
                Get all categories of branch_id
            */

            $all_categories = Category::whereHas(
                'stores',
                function ($q) use ($input) {
                    $q->where('admin_store.id', $input['branch_id']);
                }
            )->main()->active()->sort()->get();
        }


        $i=0;
        $items=[];
        foreach($all_categories as $c){
            /* $menu[$i]['category_id']=$c->id;
            $menu[$i]['name']=empty($c->details->name)?'':$c->details->name;
            $menu[$i]['image']=($c->image)?env('APP_URL').$c->image:''; */
            $menu[$i]=$this->processProductObj($request,$c->products,$c);
            $items=array_merge($items,$menu[$i]);
            $i++;
        }

        return new JsonResponse([
            'status'=>1,
            'message' => 'done',
            'data' => $items,
        ],200 );
    }
    public function processProductObj(Request $request,$products,$category){

        $sort_order = request('sort_order') ?? 'sort_asc';
        $sort = explode('_', $sort_order);
        $keyword = request('keyword') ?? '';

        /* $all_categories = Product::whereHas('categories', function($q)  use ($input){
            $q->where('admin_store.id',  $input['branch_id']);
        })->main()->active()->sort()->get(); */

        /* Todo: filter by category_id
        //category_id
        $products=Product::active()->sort($sort[0],$sort[1])->get();*/

        $i=0;
        $item=[];
        foreach($products as $p){
            $item[$i]['category_id']=$category->id;
            $item[$i]['category_name']=empty($category->details->name)?'':$category->details->name;
            $item[$i]['id']=$p->id;
            $item[$i]['item_name']=empty($p->details->name)?'':$p->details->name;
            $item[$i]['item_description']=empty($p->details->description)?'':$p->details->description;
            $item[$i]['liked_by_auth_user']=$p->liked_by_auth_user;
            $item[$i]['likes']=$p->likes_count;
            $item[$i]['price']=$p->price;
            $item[$i]['sizes']=$p->sizes;
            $item[$i]['spicy']=$p->is_spicy;
            $item[$i]['new']=$p->is_new;
            $item[$i]['comments']=$p->comments;
            $item[$i]['shares']=$p->shares;

            $images['portrait']=[];
            $images['landscape']=[];
            foreach($p->images_portrait as $img){
                $images['portrait'][]=env('APP_URL').$img->image;
            }
            foreach($p->images_landscape as $img){
                $images['landscape'][]=env('APP_URL').$img->image;
            }
            /* if($p->image_portrait)
            $images[$i]['landscape'][]=env('APP_URL').$p->image;
            foreach($p->images as $img){
                $images[]=env('APP_URL').$img->image;
            }     */
            $item[$i]['images'] = $images;

            $i++;
        }
        return $item;

    }

    public function show() {

        $product = Product::findOrFail(request('id'));
        //dd($product->liked_by_auth_user);
        //Prepare Item
        $category_id = null;
        $categories  = $product->categories->toArray();
        if (count($categories)) {
            $category_id = $categories[0]['id'];
        }

        $item['id']                 =$product->id;
        $item['item_name']          =empty($product->details->name)?'':$product->details->name;
        $item['item_description']   =empty($product->details->description)?'':$product->details->description;
        $item['liked_by_auth_user'] =$product->liked_by_auth_user;
        $item['likes']              =$product->likes_count;
        $item['categoryId']         =$category_id;
        $item['price']              = $product->price;
        $item['sizes']              = $product->sizes;
        $item['spicy']              = $product->is_spicy;
        $item['new']                = $product->is_new;
        $item['comments']           = $product->comments;
        $item['shares']             = $product->shares;
        $images                     = [];
        if ($product->image) {
            $images[] = env('APP_URL') . $product->image;
        }
        foreach ($product->images as $img) {
            $images[] = env('APP_URL') . $img->image;
        }
        $item['images'] = $images;
        $item['extras'] = [];
        $i              = 0;
        foreach ($product->extras as $extra) {
            $extras[$i]['id']               = $product->id;
            $extras[$i]['item_name']        = empty($product->details->name) ? '' : $product->details->name;
            $extras[$i]['item_description'] = empty($product->details->description) ? '' : $product->details->description;
            $extras[$i]['price']            = $product->price;
            $extras[$i]['sizes']            = $product->sizes;

            $extras[$i]['id']               = $extra->id;
            $extras[$i]['item_name']        = $extra->id;
            $extras[$i]['item_description'] = $extra->id;
            $extras[$i]['price']            = $extra->price;

            $extras[$i][''] = $extra->id;
            $extras[$i][''] = $extra->id;
            $i++;
        }
        $item['extras'] = $extras;


        return new JsonResponse(
            [
                'status'  => 1,
                'message' => 'done',
                'data'    => $item,
            ], 200
        );
    }
    public function actions(Request $request) {

        $input = $request->only(
            [
                'action',
                'item_id',

            ]
        );


        $this->validate(
            $request,
            [
                'action'  => 'in:unlike,like,share',
                'item_id' => 'required|integer'
            ]
        );


        $item = Product::findOrFail(request('item_id'));
        if ($input['action'] == 'unlike') {
            $item->likes()->where('user_id', Auth::user()->id)->delete();
        }
        else if($input['action']=='like'){
            $like = new Like(['user_id' => Auth::user()->id]);

            $item->likes()->save($like);
        }
        else if($input['action']=='share')
            $item->increment('shares');

        return new JsonResponse([
            'status'=>1,
            'message' => 'done'
        ],200 );
    }
    public function increment_shares(){
        $item= Product::findOrFail(request('item_id'));
        $item->increment('shares');

        return new JsonResponse([
            'status'=>1,
            'message' => 'done',
            'data' => $item->shares,
        ],200 );
    }
    public function import()
    {
        $import = new ImagesImport($brand_id=3);
        Excel::import($import, public_path().'/imports/Switch/images.xlsx');
        $row_count = $import->getRowCount();
        $result[]  = $row_count . ' images successfully imported!';
        //Import Location
        /* $import = new LocationsImport($brand_id=3);
        Excel::import($import, public_path().'/imports/Switch/locations.xlsx');
        $row_count = $import->getRowCount();
        $result[]= $row_count . ' Locations successfully imported!';  */
        //Import Categories
        /* $import = new CategoriesImport($brand_id=3);
        Excel::import($import, public_path().'/imports/Switch/categories.xlsx');
        $row_count = $import->getRowCount();
        $result[]= $row_count . ' Categories successfully imported!'; */


        //Import Products
        /* $import = new ProductsImport($brand_id=3);
        Excel::import($import, public_path().'/imports/Switch/menu.xlsx');
        $row_count = $import->getRowCount();
        $result[]= $row_count . ' Products successfully imported!';  */
        return $result;

    }


}
