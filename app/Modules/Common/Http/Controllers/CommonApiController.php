<?php

namespace App\Modules\Common\Http\Controllers;

use App\Core\MyBaseApiController;
use App\Modules\Common\Models\Faq;
use App\Modules\Common\Models\Feedback;
use App\Modules\Common\Models\Location;
use App\Modules\Common\Models\ShopBanner;
use App\Modules\Common\Models\ShopCategory;
use App\Modules\Common\Models\SubLocation;
use App\Modules\Common\Transformers\BannerTransformer;
use App\Modules\Common\Transformers\CategoryTransformer;
use App\Modules\Common\Transformers\FaqTransformer;
use App\Modules\Common\Transformers\FeedbackTransformer;
use App\Modules\Common\Transformers\LocationTransformer;
use App\Modules\Common\Transformers\SubLocationTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommonApiController extends MyBaseApiController
{
    public function listBanners(Request $request) {
        try {
            $banners = ShopBanner::paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));
            $data    = BannerTransformer::collection($banners);
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

    public function listCategories(Request $request) {
        try {
            $categories = ShopCategory::with(
                [
                    'Categorydescription' => function ($q) {
                        $q->addSelect('category_id', 'name');
                    }
                ]
            )->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = CategoryTransformer::collection($categories);
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

    public function listLocations(Request $request) {
        try {
            $locations = new Location;

            if ($request->has('with_sub_locations') && $request->with_sub_locations == "true") {
                $locations = $locations->with(
                    [
                        'subLocations' => function ($q) {
                            $q->orderBy('sort')->orderBy('name');
                        }
                    ]
                );
            }

            if ($request->has('must_has_sub_locations') && $request->must_has_sub_locations == 'true') {
                $locations = $locations->has('subLocations');
            }

            $locations = $locations->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = LocationTransformer::collection($locations);
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

    public function listSubLocations(Request $request) {
        try {
            $this->validateApiRequest(['location_id']);

            $locations = SubLocation::where('location_id', $request->location_id)
                                    ->orderBy('sort')
                                    ->orderBy('name')
                                    ->paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));

            $data = SubLocationTransformer::collection($locations);
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

    public function listFaq(Request $request) {
        try {
            $faqs = Faq::paginate($request->has('per_page') ? $request->per_page : env('PAGINATE_PER_PAGE'));
            $data = FaqTransformer::collection($faqs);
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

    public function storeFeedback(Request $request) {

        try {
            $this->validateApiRequest(
                ['body', 'image', 'name', 'mobile', 'email'],
                [
                    'body' => 'required',
                    'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
                ]
            );

            $data = [
              'user_id' => Auth::user()->id,
              'body' => $request->input('body'),
              'customer_name' => $request->input('name'),
              'customer_email' => $request->input('email'),
              'customer_phone' => $request->input('mobile'),
              'type' => (Auth::user()->role == SELLER_TYPE) ? SELLER_TYPE :  CUSTOMER_TYPE,
            ];

            if ($request->hasFile('image')) {
                $imageName = Auth::user()->id . '-' . rand(11111, 99999) . '.' . request()->image->getClientOriginalExtension();
                $upload = request()->image->move(public_path('data/feedback'), $imageName);
                $data['image'] =  'data/feedback/' . $imageName;
            }

            $feedback = Feedback::create($data);
            //$feedback =  Feedback::find(44);
            $this->sendFeedbackNotificationEmail($feedback->toArray());
            return $this->successResponseWithData( new FeedbackTransformer($feedback), trans('feedback.api.success'));

        } catch (\Exception $e) {
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' file : ' . $e->getFile() . ' In Line : ' . $e->getLine();
            } else {
                $message = trans('common.Something Went Wrong');
            }
            return $this->errorResponse($message);
        }

    }//storeFeedback

    private function sendFeedbackNotificationEmail($feedback)
    {
      $fileAttach = [];
      if(isset($feedback['image'])){
        $image = sc_check_image_exist($feedback['image']);

        if($image)
        {
          $fileAttach[] = $image;
        }
      }
      $cofig['to'] = env('MAIL_FROM_ADDRESS');
      $cofig['subject'] = "【{$feedback['id']}】Received a feedback";
      sc_send_mail('mail.feedback', $feedback, $cofig, $fileAttach);
    }//sendFeedbackNotificationEmail

}
