<?php
namespace App\Admin\Services;

use Carbon\Carbon;
use App\Models\ShopOrder;
use Illuminate\Support\Facades\Log;

class OrderService
{
  public static $_notify_day = 3;


  public static function clearOrdersData()
  {
    try {
      $orders  = ShopOrder::where('transaction_type', CARD)
                            ->where('payment_status', '!=', 3)
                            ->where('created_at', '<',
                                  Carbon::now()->subHours(1)->toDateTimeString()
                            )->delete();
      Log::channel('ordercron')->info('no. of orders deleted = ' . $orders);
    }catch (\Exception $e) {
      Log::channel('ordercron')->info('error occured in order cron job'.PHP_EOL.$e->getMessage());
    }



  }//clearOrdersData

}
