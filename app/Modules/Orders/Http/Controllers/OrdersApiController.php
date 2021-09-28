<?php

namespace App\Modules\Orders\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Modules\Auth\Models\ShopUser;
use App\Modules\Orders\Models\ShopOrder;
use App\Modules\Orders\Transformers\OrderTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Modules\Products\Services\ProductService;
use App\Modules\Orders\Services\OrderService;
use App\Modules\Orders\Services\PaymentService;
use Carbon\Carbon;

class OrdersApiController extends MyBaseApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request) {
        try {
            $orders = ShopOrder::canRateOrder()
                               ->where('user_id', Auth::user()->id)
                               ->orderBy('created_at', 'desc')
                               ->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

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

    public function create(Request $request){

      try {
          DB::beginTransaction();
          $this->validateApiRequest(
              ['customer_name', 'transaction_type', 'delivery_date', 'delivery_slot', 'products', 'store_id', 'payment_status'],
              [
                  'address_id'       => 'required_without:address_text',
                  'address_text'     => 'required_without:address_id',
                  'transaction_type' => 'in:cod,card,apple_pay,google_pay',
                  'store_id'         => 'required|exists:admin_store,id',
                  'payment_status'         => 'required|exists:shop_payment_status,id',
                  'products.*.product_id' => 'required|exists:shop_product,id',
                  'products.*.quantity' => 'required',
              ]
          );

          $request_products = $request->get('products');

          $i = 0;
          foreach($request_products as $item){
            $productItems[$i]['price'] =$item['price'];
            $productItems[$i]['quantity'] =$item['quantity'];
            $productItems[$i]['product_id'] =$item['product_id'];
            $product = ProductService::getTransformProductById($item['product_id']);
            unset($product['single_description']);
            unset($product['descriptions']);
            $productItems[$i]['product_details'] = $product;
            $i++;
          }

          $store_id =  $request->get('store_id');

          $cart_calculations = $this->CartCalculations($request_products);

          $customer_name     = explode(' ', $request->customer_name);

          $PaymentGatewayResponse = true;
          if ($PaymentGatewayResponse) {

              $order_data        = [
                  'number'            => OrderService::generateOrderNumber(),
                  'user_id'           => Auth::user()->id,
                  'first_name'        => $customer_name[0],
                  'last_name'         => $customer_name[1] ?? " ",
                  'address_id'        => $request->address_id,
                  'address1'         => $request->address_text,
                  'address1_lat'     => $request->address_lat,
                  'address1_long'    => $request->address_long,
                  'address2'         => $request->address_landmark,
                  'phone'            => $request->mobile,
                  'email'            => $request->email,
                  'status'           => 1,
                  'payment_status'   => 3,
                  'shipping_status'  => 1,
                  'currency'         => 'AED',
                  'exchange_rate'    => '1.00',
                  'exchange_rate'    => '1.00',
                  'items'            => json_encode($productItems),
                  'subtotal'         => $request->total ?? $cart_calculations['total_price'],
                  'shipping'         => $request->delivery_charges ?? 0,
                  'discount'         => $request->discount ?? 0,
                  'tax'              => 0,
                  'total'            => $request->total ?? $cart_calculations['total_price'],
                  'transaction'      => 0,
                  'transaction_type' => $request->transaction_type,
                  'payment_status'   => $request->payment_status,
                  'store_id'         => $store_id,
                  'delivery_date'    => $request->delivery_date,
                  'delivery_slot'    => $request->delivery_slot,
                  'special_request'  => $request->special_request,
                  'offer_code'       => $request->offer_code ?? 0,
              ];
              //dd($order_data);
              $estimated_time               = 45; //to do dynamic from store
              $order_data['estimated_time'] = Carbon::now()->addMinutes($estimated_time);
              $order                        = ShopOrder::create($order_data);

              foreach ($productItems as $item) {
                $order->details()->create([
                    'store_id'    => $store_id,
                    'product_id'  => $item['product_id'],
                    'name'        => $item['product_details']['name'],
                    'price'       => $item['price'],
                    'qty'         => $item['quantity'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'sku'         => 'N/A',
                    'currency'    => 'AED',
                ]);

              }

              if($request->transaction_type == 'cod'){
                $this->sendOrderNotification($store_id, $order);
              }

              $paymentDetails = false;

              if($request->transaction_type == 'card'){
                $paymentDetails = PaymentService::getPaymentUrl($order);
                if(!$paymentDetails){
                  throw new \Exception(trans('common.Something Went Wrong'));
                  DB::rollBack();
                }
              }

              $data['order'] = new OrderTransformer($order);
              $data['paymentDetails'] = $paymentDetails;
              //$data->test = "something";
              DB::commit();

              return $this->successResponseWithData($data, 'Your order has been completed successfully, please check the order status for the updates!');
          }
      } catch (ModelNotFoundException $e) {
          DB::rollBack();
          return $this->errorResponse('user cart not found');
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

    public function update(Request $request){

      try{
        $this->validateApiRequest(
            ['order_id', 'transaction_id', 'payment_status'],
            [
                'order_id'         => 'required|exists:shop_order,id',
                'payment_status'   => 'required|exists:shop_payment_status,id',
            ]
        );

        $order = ShopOrder::find($request->order_id);
        $order->transaction = $request->transaction_id;
        $order->payment_status = $request->payment_status;
        $order->save();

        $this->sendOrderNotification($order->store_id, $order);

        $data = new OrderTransformer($order);
        return $this->successResponseWithData($data, 'Your order has been completed successfully, please check the order status for the updates!');

      }catch (\Exception $e) {
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

            $this->validateApiRequest(
                ['order_id'],
                [
                    'order_id'         => 'required|exists:shop_order,id'
                ]
            );

            $order = ShopOrder::find($request->order_id);
            $order->details()->delete();

            $order->delete();
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

    private function sendOrderNotification($store_id, $order){
      if ($store_id) {
          $seller_user = ShopUser::where('role', 'seller')
              ->where('store_id', $store_id)
              ->first();

          if ($seller_user && !empty($seller_user->device_udid)) {
              send_notification_to_seller(
                  'you have new order please accept it',
                  $seller_user->device_udid,
                  null,
                  ['order_id' => $order->id]
              );
          }
      }


      if (Auth::user()->device_udid) {
          send_notification_to_customer(
              "Your order #" . $order->id . " has been placed. Awaiting confirmation from restaurant",
              Auth::user()->device_udid,
              null,
              ['order_id' => $order->id]
          );
      }
    }

    private function CartCalculations($reqeust_products)
    {
        $total_quantity = 0;
        $total_price    = 0;
        foreach ($reqeust_products as $product) {
            $total_price += $product['quantity'] * $product['price'];
            $total_quantity += $product['quantity'];
        }

        return ([
            'total_quantity' => $total_quantity,
            'total_price'    => $total_price,
        ]);
    }
}
