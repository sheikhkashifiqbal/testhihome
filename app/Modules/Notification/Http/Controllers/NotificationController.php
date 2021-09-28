<?php

namespace App\Modules\Notification\Http\Controllers;

use App\Core\MyBaseApiController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Transformers\NotificationResource;
use Auth;

class NotificationController extends MyBaseApiController
{
    public function getAllNotifications(Request $request)
    {
        try {
            $notifications = Notification::getSellerNotifications(Auth::user()->store_id)->orderBy('created_at', 'desc');
            $notifications = $notifications->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));
            //$notifications$data = ProductTransformer::collection($top_rated_products);
            return $this->successResponseWithData(NotificationResource::collection($notifications));
        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }
    }//getAllNotifications

    public function readNotification(Request $request)
    {
      $this->validateApiRequest(
          ['notification_id', 'read_at'],
          ['read_at' => 'date_format:d-m-Y H:i:s a']
      );

      $notification = Notification::find($request->notification_id);
      try{
          $notification->read_at = Carbon::parse($request->read_at)->format('Y-m-d h:i:s a');
          $notification->save();
          return $this->successResponseWithData( [], trans('notifications.api.success'));
      }catch (\Exception $e) {
        if (app()->environment('local')) {
            $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
        } else {
            $message = trans('common.Something Went Wrong');
        }
        return $this->errorResponse($message);
      }
    }
}
