<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AdminStore as Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{


    public function index(Request $request)
    {

        $input = $request->only(
            [
                'brand_id',
                'lat',
                'long',

            ]
        );
        $this->validate(
            $request,
            [
                'brand_id' => 'required|integer',
                /* 'lat' => 'required',
                'long' => 'required', */
            ]
        );

        $all_branches=Branch::where('brand_id',$input['brand_id'])->active()->sort()->get();
        $i=0;
        foreach($all_branches as $b){

            $branches[$i]['id']=$b->id;
            $branches[$i]['logo']=($b->logo)?env('APP_URL').$b->logo:'';
            $branches[$i]['name']=empty($b->description->title)?'':$b->description->title;
            $branches[$i]['address']=empty($b->description->address)?'':$b->description->address;
            $branches[$i]['hours']=$b->time_active;
            $branches[$i]['phone']=$b->phone;
            $branches[$i]['lat']=$b->lat;
            $branches[$i]['long']=$b->long;
            if(!empty($input['lat'])&&!empty($input['long'])){
                $branches[$i]['distance']=$this->distance($input['lat'], $input['long'], $b->lat, $b->long);
            }else{
                $branches[$i]['distance']=-1;
            }
            $branches[$i]['distance_unite']='KM';

            $branches[$i]['email']=$b->email;

            $i++;
        }
        /* sort branches accourding to distance asc PHP5 */
        // $distance = array_column($branches, 'distance');
        // array_multisort($distance, SORT_DESC, $branches);

        /* sort branches accourding to distance asc PHP7 */
        if(!empty($branches)){
            usort($branches, function ($item1, $item2) {
                return $item1['distance'] <=> $item2['distance'];
            });
            return new JsonResponse([
                'status'=>1,
                'message' => 'done',
                'data' => $branches,
            ],200 );
        }

        return new JsonResponse([
            'status'=>1,
            'message' => 'done',
            'data' => [],
        ],200 );
    }
    public function pickup(Request $request)
    {

        $input = $request->only(
            [
                'lat',
                'long',

            ]
        );
        $this->validate(
            $request,
            [
                'lat'  => 'required',
                'long' => 'required',
            ]
        );

        $all_branches=Branch::active()->sort('brand_id')->get();
        $i=0;
        $i2=0;
        $previous_brand=null;
        $branches=[];
        $brand=[];
        foreach($all_branches as $b){
            $distance=$this->distance($input['lat'], $input['long'], $b->lat, $b->long,'K',1);
            if($distance>10) continue;

            $distance= number_format($distance,2);


            if($b->brand->id!=$previous_brand){
                $brand[$i2]['id']=$b->brand->id;
                $brand[$i2]['name']=$b->brand->name;
                $brand[$i2]['image']=($b->brand->image)?env('APP_URL').$b->brand->image:'';
                $previous_brand=$b->brand->id;
                $i2++;
                $branches=[];
                $i=0;
            }
            $branches[$i]['id']=$b->id;
            $branches[$i]['logo']=($b->logo)?env('APP_URL').$b->logo:'';
            $branches[$i]['name']=empty($b->description->title)?'':$b->description->title;
            $branches[$i]['address']=empty($b->description->address)?'':$b->description->address;
            $branches[$i]['hours']=$b->time_active;
            $branches[$i]['phone']=$b->phone;
            $branches[$i]['lat']=$b->lat;
            $branches[$i]['long']=$b->long;
            $branches[$i]['email']=$b->email;
            $branches[$i]['distance']=$this->distance($input['lat'], $input['long'], $b->lat, $b->long);

            $branches[$i]['distance_unite']='KM';

            $brand[$i2-1]['braches']=$branches;
            $i++;
        }
        $brand_new=[];
        $branches_new=[];
        //sort branches according to distance asc
        foreach($brand as $b) {

            usort(
                $b['braches'],
                function ($item1, $item2) {
                    return $item1['distance'] <=> $item2['distance'];
                }
            );
            $brand_new[] = $b;

        }


        return new JsonResponse([
            'status'=>1,
            'message' => 'done',
            'data' => $brand_new,
        ],200 );
    }
    public function distance($lat1, $lon1, $lat2, $lon2, $unit = 'K',$number=0) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                $distance=$miles * 1.609344;
            }
            else if ($unit == "N") {
                $distance = $miles * 0.8684;

            }
            else {
                $distance=$miles;
            }
            //$distance=round(floatval($distance),1);
            if($number)
                return (double) $distance;
            else
                return number_format($distance,2);
            //return $distance;
            //return (float)number_format((float)$distance, 1, '.', '');
        }
    }



}
