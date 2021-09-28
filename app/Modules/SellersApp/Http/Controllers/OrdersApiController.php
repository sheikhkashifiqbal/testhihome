<?php

namespace App\Modules\SellersApp\Http\Controllers;

use App\Core\MyBaseApiController;
use Carbon\Carbon;
use App\Modules\Auth\Models\ShopUser;
use App\Modules\Orders\Models\ShopOrder;
use App\Modules\Orders\Models\ShopOrderStatus;
use App\Modules\Orders\Transformers\OrderStatusTransformer;
use App\Modules\Orders\Transformers\OrderTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OneSignal;

class OrdersApiController extends MyBaseApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        try {
            $this->validateApiRequest(
                [],
                [
                    'status' => 'in:all,1,2,3,4,5,6'
                ]
            );
            $orders = ShopOrder::excludeUnpaidByCard()->whereHas(
                'details',
                function ($q) {
                    $q->where('store_id', Auth::user()->store_id);
                }
            )
                ->with(
                    [
                        'details' => function ($q) {
                            $q->where('store_id', Auth::user()->store_id);
                        }
                    ]
                )
                ->orderBy('created_at', 'desc');

            if ($request->has('status') && $request->status != 'all') {
                $orders = $orders->where('status', $request->status);
            }

            $orders = $orders->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = OrderTransformer::collection($orders);

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

    public function dashboarOrders(Request $request)
    {
        try {

            $orders = ShopOrder::excludeUnpaidByCard()->whereHas(
                'details',
                function ($q) {
                    $q->where('store_id', Auth::user()->store_id);
                }
            )
                ->with(
                    [
                        'details' => function ($q) {
                            $q->where('store_id', Auth::user()->store_id);
                        }
                    ]
                )
                ->orderBy('status', 'asc')
                ->orderBy('created_at', 'desc');

            $orders = $orders->whereNotIn('status', ['5','6']);

            $orders = $orders->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = OrderTransformer::collection($orders);

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

    public function getOrderDetails(Request $request)
    {
        try {
            $this->validateApiRequest(['order_id']);
            $order = ShopOrder::where('id', $request->order_id)
                ->whereHas(
                    'details',
                    function ($q) {
                        $q->where('store_id', Auth::user()->store_id);
                    }
                )
                ->with(
                    [
                        'details' => function ($q) {
                            $q->where('store_id', Auth::user()->store_id);
                        }
                    ]
                )
                ->firstOrFail();
            $data  = new OrderTransformer($order);
            return $this->successResponseWithData($data);
        } catch (ModelNotFoundException $e) {
            if (app()->environment('local')) {
                $message = 'Order Not Found';
            } else {
                $message = trans('common.Something Went Wrong');
            }
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

    public function change_status(Request $request)
    {
        try {
            $this->validateApiRequest(
                ['order_id', 'status'],
                [
                    'order_id'             => 'exists:shop_order,id',
                    'status'               => 'integer|between:1,6',
                    'cancellation_comment' => 'required_if:status,6',
                ],
                ['order_id']
            );

            $order = ShopOrder::where('id', $request->order_id)
                ->whereHas(
                    'details',
                    function ($q) {
                        $q->where('store_id', Auth::user()->store_id);
                    }
                )
                ->firstOrFail();

            $order->status = $request->status;
            if (isset($request->cancellation_comment) && $request->cancellation_comment != '') {
                $order->comment = $request->cancellation_comment;
            }

            if(isset($request->pickup_time) && $request->pickup_time){
              $order->pickup_time = Carbon::parse($request->pickup_time);
            }

            $order->save();

            $order_status = ShopOrderStatus::find($request->status);

            $customer_user = ShopUser::find($order->user_id);

            if (!empty($customer_user->device_udid)) {
                $cancellation_comment = '';
                if (isset($request->cancellation_comment) && $request->cancellation_comment != '') {
                    $cancellation_comment = ', ' . $request->cancellation_comment;
                }
                send_notification_to_customer(
                    $order_status->text . $cancellation_comment,
                    $customer_user->device_udid,
                    null,
                    ['order_id' => $order->id]
                );
            }

            $data = new OrderTransformer($order);

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

    public function listOrdersStatus(Request $request)
    {


        try {
            $orders_status = ShopOrderStatus::get();
            $data          = OrderStatusTransformer::collection($orders_status);
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
}
