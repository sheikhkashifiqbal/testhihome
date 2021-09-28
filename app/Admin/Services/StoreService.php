<?php
namespace App\Admin\Services;

use Carbon\Carbon;
use App\Models\AdminStore;
use Illuminate\Support\Facades\Log;

class StoreService
{
  public static $_notify_day = 3;


  public static function sendLicenseExpiryNotification()
  {

    try {
        $expiry_date = Carbon::now()->addDays(self::$_notify_day);

        $stores = AdminStore::with('description')->whereDate('license_end_date', '=', $expiry_date->toDateString())
                              ->get();

        foreach($stores as $store){
            $data_seller = [
              'expiry_date' => $expiry_date->format('d-m-Y')
            ];

            $config_seller = [
                'to' => $store->contact_us_email,
                'subject' => 'License Expiry Notification',
            ];
            $config_admin = [
                'to' => env('MAIL_FROM_ADDRESS'),
                'subject' => 'Seller\'s License Expiry Notification',
            ];
            $data_admin = [
              'expiry_date' => $expiry_date->format('d-m-Y'),
              'name'        => $store->description->title,
              'email'       => $store->contact_us_email,
              'phone'       => $store->legal_business_phone,
            ];

            sc_send_mail('mail.license.expiry_store', $data_seller, $config_seller, []);
            sc_send_mail('mail.license.expiry_admin', $data_admin, $config_admin, []);
          }
    }catch (\Exception $e) {
        Log::channel('licenseexpiry')->info('License expiry cron job error;'.PHP_EOL.$e->getMessage());
    }
  }//sendLicenseExpiryNotification

}
