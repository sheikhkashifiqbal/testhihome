<?php
namespace App\Modules\Orders\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\json_decode;
use Illuminate\Support\Facades\Log;
use App\Modules\Common\Models\ShopUserAddress;

class PaymentService{

  private static $_url = 'https://secure.telr.com/gateway/order.json';


  public static function getPaymentUrl($order){

    $client = new Client();
    $request = self::generateRequestion($order);

    $client = new Client([
    'headers' => [ 'Content-Type' => 'application/json' ]
    ]);
    //dd(json_encode($request));
    $response = $client->post(self::$_url,
        ['body' => json_encode($request)]
    );

    // Validate if response is equal 200
    if (200 != $response->getStatusCode()) {
        Log::channel('payment')->info('Response code from telr = ' . $response->getStatusCode());
        return false;
    }

    $result = json_decode($response->getBody()->getContents());

    // Validate if response has error messages

    if (isset($result->error)) {
        Log::channel('payment')->info('error message for order number = '.$order->number.": \n" . $result->error->message);
        return false;
    }

    return $result->order;
  }

  private static function generateRequestion($order){

    $address = ShopUserAddress::find($order->address_id);
    $city   = 'Dubai';

    if($address){
      $city = $address->emirate ?: $city;
    }

    return [
      'method' => 'create',
      'store' => env('TELR_STORE'),
      'authkey' => env('TELR_AUTH'),
      'order' => [
        "cartid"  => $order->number,
        "test"    => env('TELR_TEST'),
        "amount"  => $order->total +  $order->shipping,
        "currency" => "AED",
        "description" => "Order for customer " .$order->first_name,
        "trantype" => "ecom"
      ],
      'customer' => [
        'ref'   => $order->user_id,
        'email' => $order->email,
        'phone' => $order->phone,
        'address' => [
          'line1' => $order->address1,
          'city' => $city,
          'country' => "AE",
        ],
        'name' => [
          'forenames' => $order->first_name,
          'surname' => $order->last_name,
        ],
      ],
      'return' => [
        "authorised" => "https://hihome.app/#apps-craft-home",
         "declined" => "https://hihome.app/#apps-craft-home",
         "cancelled" => "https://hihome.app/#apps-craft-home"
      ]
    ];
  }//generateRequestion

}
