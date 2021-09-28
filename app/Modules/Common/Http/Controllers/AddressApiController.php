<?php


namespace App\Modules\Common\Http\Controllers;


use App\Core\MyBaseApiController;
use App\Modules\Common\Models\ShopUserAddress;
use App\Modules\Common\Transformers\AddressTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressApiController extends MyBaseApiController
{
    public function index(Request $request) {
        try {
            $addresses = ShopUserAddress::where('user_id', Auth::user()->id)->get();
            $data      = AddressTransformer::collection($addresses);
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

    public function store(Request $request) {
        try {
            $this->validateApiRequest(
                ['address', 'lat', 'long', 'name', 'mobile', 'email']
            );

            if($request->get('is_default') == 1){
              Auth::user()->addresses()->update(['is_default' => 0]);
            }

            $address = Auth::user()->addresses()->create(
                $request->only(
                    [
                        'address',
                        'lat',
                        'long',
                        'tag',
                        'landmark',
                        'name',
                        'mobile',
                        'email',
                        'address_line_1',
                        'address_line_2',
                        'area',
                        'emirate',
                        'is_default'
                    ]
                )
            );
            $data    = new AddressTransformer($address->fresh());
            return $this->successResponseWithData($data, 'Address Added Successfully');
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function update(Request $request) {
        try {
            $this->validateApiRequest(
                ['id', 'address', 'lat', 'long', 'name', 'mobile', 'email'],
                [
                    'id' => 'exists:shop_user_address,id'
                ]
            );
            if($request->get('is_default') == 1){
              Auth::user()->addresses()->update(['is_default' => 0]);
            }
            $address = ShopUserAddress::findOrFail($request->id);

            $address->update(
                $request->only(
                    [
                        'address',
                        'lat',
                        'long',
                        'tag',
                        'landmark',
                        'name',
                        'mobile',
                        'email',
                        'address_line_1',
                        'address_line_2',
                        'area',
                        'emirate',
                        'is_default'
                    ]
                )
            );
            $data = new AddressTransformer($address->fresh());
            return $this->successResponseWithData($data, 'Address Updated Successfully');
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }

    public function remove(Request $request) {
        try {
            $this->validateApiRequest(
                ['id'],
                [
                    'id' => 'exists:shop_user_address,id'
                ]
            );
            $address = ShopUserAddress::where('user_id', Auth::user()->id)
                                      ->findOrFail($request->id);
            $address->delete();
            return $this->successEmptyResponse('Address Removed Successfully');
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
