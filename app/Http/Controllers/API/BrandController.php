<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ShopBrand as Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{


    public function index()
    {

        $all_brands=Brand::active()->sort()->get();

        $i=0;
        foreach($all_brands as $b){

            $branches[$i]['id']=$b->id;
            $branches[$i]['name']=$b->name;
            $branches[$i]['image']=($b->image)?env('APP_URL').$b->image:'';


            $i++;
        }

        return new JsonResponse([
            'status'=>1,
            'message' => 'done',
            'data' => $branches,
        ],200 );
    }



}
