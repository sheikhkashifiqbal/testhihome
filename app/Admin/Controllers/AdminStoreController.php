<?php

#app/Http/Admin/Controllers/AdminStoreController.php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminStore;
use App\Models\ShopUser;
use App\Models\AdminStoreDescription;
use App\Models\ShopAttributeGroup;
use App\Models\ShopCountry;
use App\Models\ShopCurrency;
use App\Models\ShopOrder;
use App\Models\ShopOrderDetail;
use App\Models\ShopOrderStatus;
use App\Models\ShopOrderTotal;
use App\Models\ShopPaymentStatus;
use App\Models\ShopLanguage;
use App\Models\ShopShippingStatus;
use App\Models\ShopBrand;
use App\Models\StoreBannerImage;
use Validator; 
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str; 
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\ShopProduct;
use App\Models\Location;
use App\Modules\RateReview\Services\StoreRatingService;
use Illuminate\Validation\ValidationException;

class AdminStoreController extends Controller
{

    public $templates, $currencies, $languages, $timezones, $statusPayment, $statusOrder, $statusShipping, $statusOrderMap, $statusShippingMap, $currency, $country, $countryMap, $brand, $branch, $typeOrder;

    public function __construct()
    {
        foreach (timezone_identifiers_list() as $key => $value) {
            $timezones[$value] = $value;
        }
        $this->templates = []; //$templates;
        $this->currencies = ShopCurrency::getCodeActive();
        $this->languages = ShopLanguage::getListActive();
        $this->timezones = $timezones;
        $this->statusOrder = ShopOrderStatus::getListStatus();
        $this->statusOrderMap = ShopOrderStatus::mapValue();
        $this->currency = ShopCurrency::getList();
        $this->country = ShopCountry::getArray();
        $this->countryMap = ShopCountry::mapValue();
        $this->statusPayment = ShopPaymentStatus::getListStatus();
        $this->statusShipping = ShopShippingStatus::getListStatus();
        $this->statusShippingMap = ShopShippingStatus::mapValue();
        $this->brand = ShopBrand::getListBrand();
        $this->branch = AdminStore::getListBranch();
        $this->typeOrder = ['pickup' => 'Pickup', 'delivery' => 'Delivery'];
    }

    public function index()
    {
        $status = request('active', 1);

        $data = [
            'title' => trans('store.admin.list'),
            'sub_title' => '',
            'icon' => 'fa fa-indent'
        ];

        $listTh = $this->returnListTh();

        $sort_order = request('sort_order') ?? 'id__desc';

        $keyword = request('keyword') ?? '';

        $arrSort = $this->returnSortArray();
        $obj = AdminStore::withRelationships()->withOrderBy($sort_order)->withApproval($status);

        if ($keyword) {
            $obj = $obj->withSearchKeywords($keyword);
        }

        $dataTmp = $obj->paginate(20);

        $data['listTh'] = $listTh;
        $data['dataTr'] = $this->returnTableTr($dataTmp);
        $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links('admin.component.pagination');
        $data['result_items'] = trans('store.admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'item_total' => $dataTmp->total()]);

        $data['menu_left'] = $this->returnLeftMenu();

        $data['menu_right'] = $this->returnRightMenu();

        $optionSort = $this->returnSortArrayOptions($sort_order);

        $data['menu_sort'] = $this->returnMenuSort($optionSort);

        $data['script_sort'] = $this->returnScriptSort();

        $data['menu_search'] = $this->returnMenuSearch($keyword);

        $data['url_delete_item'] = route('admin_store.delete');

        return view('admin.screen.list')
            ->with($data);

    }

    private function returnListTh()
    {
      return [
          'id' => trans('store.id'),
          'image' => trans('store.image'),
          'name' => trans('store.name'),
          'expire_date' => trans('store.admin.expire_date'),
          'most_product_picture' => trans('store.admin.most_product'),
          'most_product_name' => trans('store.admin.most_product'),
          'rating' =>  trans('store.admin.average_rating'),
          'approval' => trans('store.approval'),
          'status' => trans('store.status'),
          'action' => trans('store.admin.action'),
      ];
    }

    private function returnTableTr($dataTmp)
    {
      $dataTr = [];
      foreach ($dataTmp as $key => $row) {
          $rating = StoreRatingService::getStoreAverageRating($row['id']);
          $approval   = trans('store.approval_status.'.$row['approval']);

          $approval_label = $row['approval'] === 2 ? 'label-danger' :  ($row['approval'] === 1 ? 'label-success' : 'label-info');
          $dataTr[] = [
              'id' => $row['id'],
              'image' => cdn_image_render($row->logo, '50', '50'),
              'name' => $row['store_name'],
              'expire_date' => date('Y-m-d', strtotime($row['license_end_date'])),
              'most_product_picture' => $row['image'] ? cdn_image_render($row['image'], '50', '50') : '',
              'most_product_name' =>  $row['product_name'] ? $row['product_name'] : 'N/A',
              'rating' => $rating['average_rating'].' (' . $rating['rating_count'] .' '.trans('store.admin.reviews').')',
              'approval' => '<span class="label '.$approval_label.'" style="text-transform:uppercase">'.$approval.'</span>',
              'status' => $row['status'] ? '<span class="label label-success">ON</span>' : '<span class="label label-danger">OFF</span>',
              'action' => '
                  <a href="' . route('admin_store.edit', ['id' => $row['id']]) . '"><span title="' . trans('store.admin.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;
                  <a href="' . route('admin_store.seller_details', ['id' => $row['id']]) . '"><span title="' . trans('store_info.store_info') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-eye"></i></span></a>&nbsp;
                  <a href="' . route('admin_store_rating.index', ['id' => $row['id']]) . '"><span title="' . trans('store.admin.reviews') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-star"></i></span></a>&nbsp;
                  <span onclick="deleteItem(' . $row['id'] . ');"  title="' . trans('store.admin.delete') . '" class="btn btn-flat btn-danger"><i class="fa fa-trash"></i></span>
                ',
          ];
      }
      return $dataTr;
    }

    private function returnSortArray()
    {
      return [
          'id__desc' => trans('store.admin.sort_order.id_desc'),
          'id__asc' => trans('store.admin.sort_order.id_asc'),
          'store_name__desc' => trans('store.admin.sort_order.name_desc'),
          'store_name__asc' => trans('store.admin.sort_order.name_asc'),
      ];
    }

    private function returnSortArrayOptions($sort_order)
    {
      $arrSort = $this->returnSortArray();
      $optionSort = '';
      foreach ($arrSort as $key => $status) {
          $optionSort .= '<option  ' . (($sort_order == $key) ? "selected" : "") . ' value="' . $key . '">' . $status . '</option>';
      }
      return $optionSort;
    }

    private function returnLeftMenu()
    {
      return '<div class="pull-left">
                    <a class="btn   btn-flat btn-primary grid-refresh" title="Refresh"><i class="fa fa-refresh"></i><span class="hidden-xs"> ' . trans('store.admin.refresh') . '</span></a> &nbsp;
                    </div>';
    }

    private function returnRightMenu()
    {
      return '<div class="btn-group pull-right" style="margin-right: 10px">
                         <a href="' . route('admin_store.create') . '" class="btn  btn-success  btn-flat" title="New" id="button_create_new">
                         <i class="fa fa-plus"></i><span class="hidden-xs">' . trans('store.admin.add_new') . '</span>
                         </a>
                      </div>';
    }

    private function returnMenuSort($optionSort)
    {
      return '<div class="btn-group pull-left">
                      <div class="form-group">
                         <select class="form-control" id="order_sort">
                          ' . $optionSort . '
                         </select>
                       </div>
                     </div>

                     <div class="btn-group pull-left">
                         <a class="btn btn-flat btn-primary" title="Sort" id="button_sort">
                            <i class="fa fa-sort-amount-asc"></i><span class="hidden-xs"> ' . trans('admin.sort') . '</span>
                         </a>
                     </div>';
    }

    private function returnScriptSort()
    {
      return "$('#button_sort').click(function(event) {
            var url = '" . route('admin_store.index') . "?sort_order='+$('#order_sort option:selected').val();
            $.pjax({url: url, container: '#pjax-container'})
          });";
    }

    private function returnMenuSearch($keyword)
    {
      return '<form action="' . route('admin_store.index') . '" id="button_search">
                 <div onclick="$(this).submit();" class="btn-group pull-right">
                         <a class="btn btn-flat btn-primary" title="Refresh">
                            <i class="fa  fa-search"></i><span class="hidden-xs"> ' . trans('admin.search') . '</span>
                         </a>
                 </div>
                 <div class="btn-group pull-right">
                       <div class="form-group">
                         <input type="text" name="keyword" class="form-control" placeholder="' . trans('store.admin.search_place') . '" value="' . $keyword . '">
                       </div>
                 </div>
              </form>';
    }

    /**
     * Form create new order in admin
     * @return [type] [description]
     */
    public function create()
    {
        $data = [
            'title' => trans('store.admin.add_new_title'),
            'subTitle' => '',
            'title_description' => trans('store.admin.add_new_des'),
            'icon' => 'fa fa-plus',
            'store' => [],
            'languages' => $this->languages,
            'url_action' => route('admin_store.create'),
            'templates' => $this->templates
        ];

        $data['timezones'] = $this->timezones;
        $data['currencies'] = $this->currencies;

        return view('admin.screen.store_add')
            ->with($data);
    }

    /*
     * Post create new order in admin
     * @return [type] [description]
     */

    public function postCreate()
    {
        $dataOrigin = $data = request()->all();
        $data['domain'] = Str::finish(str_replace(['http://', 'https://'], '', $data['domain']), '/');
        $validator = Validator::make($data, [
            'descriptions.*.title' => 'required|string|max:200',
            'descriptions.*.keyword' => 'nullable|string|max:200',
            'descriptions.*.description' => 'nullable|string|max:300',
            'timezone' => 'required',
            'language' => 'required',
            'currency' => 'required',
            'template' => 'required',
        ], [
            'descriptions.*.title.required' => trans('validation.required', ['attribute' => trans('store.title')]),
            'descriptions.*.keyword.required' => trans('validation.required', ['attribute' => trans('store.keyword')]),
            'descriptions.*.description.required' => trans('validation.required', ['attribute' => trans('store.description')]),
        ]);
		
		$sizes =getimagesize("http://localhost/dev-k/public/data/logo/a.jpeg");
		if ($sizes[0] != 200 OR $sizes[0] != 200){
			throw ValidationException::withMessages(['logo' => 'The image size should be 200 by 200']);		
		}
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($dataOrigin);
        }
        $dataInsert = [
            'logo' => $data['logo'],
            'phone' => $data['phone'],
            'long_phone' => $data['long_phone'],
            'email' => $data['email'],
            'time_active' => $data['time_active'],
            'address' => $data['address'],
            'office' => $data['office'],
            'timezone' => $data['timezone'],
            'language' => $data['language'],
            'currency' => $data['currency'],
            'template' => $data['template'],
            'domain' => $data['domain'],
            'status' => empty($data['status']) ? 0 : 1,
        ];
        $store = AdminStore::create($dataInsert);

        $dataDes = [];
        $languages = ShopLanguage::getListActive();
        foreach ($languages as $code => $value) {
            $dataDes[] = [
                'store_id' => $store->id,
                'lang' => $code,
                'title' => $data['descriptions'][$code]['title'],
                'keyword' => $data['descriptions'][$code]['keyword'],
                'description' => $data['descriptions'][$code]['description'],
            ];
        }
        AdminStoreDescription::insert($dataDes);

        return redirect()->route('admin_store.index')->with('success', trans('store.admin.create_success'));
    }

    /*
      Update value config
     */

    public function updateInfo()
    {
        $data = request()->all();
        //dd($data);
        $fieldName = $data['name'];
        $value = $data['value'];
        $parseName = explode('__', $fieldName);
        $storeId = $parseName[0];

        $name = $parseName[1];
        $lang = $parseName[2] ?? '';
        $msg = '';

        if (in_array($name, ['logo', 'logo2', 'license_photo'])) {
            $value = cdn_image_upload_from_storage($value, $name);
        }

        $updateData = [$name => $value];

        if (!in_array($parseName[1], ['title', 'description', 'keyword', 'maintain_content'])) {

            try {
                $seller_user = ShopUser::where('role', 'seller')
                    ->where('store_id', $storeId)
                    ->first();
                $config['subject'] = '【HiHOME.app】Seller Account Status';


                if ($name == "approval") {
                    if($value == STORE_ARPROVED){
                        $this->sendApprovalNotificationToSeller($seller_user, $config);
                    }
                   $updateData = array_merge($updateData, ['reject_msg' => '']);
                }
                if ($name == "reject_msg") {
                   $this->sendRejectionNotificationToSeller($seller_user, $config, $value);
                   $updateData = array_merge($updateData, ['approval' => STORE_REJECTED]);
                }

                AdminStore::where('id', $storeId)->update($updateData);
                $error = 0;
            } catch (\Throwable $e) {
                $error = 1;
                $msg = $e->getMessage();
            }
        } else {
            try {
                $dd = AdminStoreDescription::where('config_id', $storeId)
                    ->where('lang', $lang)
                    ->update([$name => $value]);
                $error = 0;
                if ($dd == 0) {
                    AdminStoreDescription::where('config_id', $storeId)
                        ->where('lang', $lang)
                        ->insert([$name => $value, 'config_id' => $storeId, 'lang' => $lang]);
                }
            } catch (\Throwable $e) {
                $error = 1;
                $msg = $e->getMessage();
            }
        }
        return response()->json(['error' => $error, 'msg' => $msg]);
    }

    private function sendApprovalNotificationToSeller($seller_user, $config)
    {
      $config['to'] = $email = $seller_user->email;
      if ($seller_user && !empty($seller_user->device_udid)) {
              send_notification_to_seller(
                  'Your account have been approve, use your credential to login to the App',
                  $seller_user->device_udid,
                  null,
                  ['content' => ''],
                  null,
                  null,
                  'Seller Account:'
              );
        }
        $data = array('title' => 'Seller Account Status');

        $A = Mail::queue(new SendMail('mail.success_email', $data, $config));

    }//sendApprovalNotificationToSeller

    private function sendRejectionNotificationToSeller($seller_user, $config, $content)
    {
      $config['to'] = $email = $seller_user->email;
      $data = array('title' => 'Seller Account Status', 'content' => $content);

      $A = Mail::queue(new SendMail('mail.reject_email', $data, $config));
      if ($seller_user && !empty($seller_user->device_udid)) {
          send_notification_to_seller(
              'Please do the needed to activate your account',
              $seller_user->device_udid,
              null,
              ['content' => $content],
              null,
              null,
              'Seller Account:'
          );
      }
    }//sendRejectionNotificationToSeller
    /*
      Delete list item
      Need mothod destroy to boot deleting in model
     */

    public function delete()
    {
        if (!request()->ajax()) {
            return response()->json(['error' => 1, 'msg' => 'Method not allow!']);
        } else {
            $id = request('ids');
            //dd($id);
            if (config('app.storeId') == $id) {
                return response()->json(['error' => 1, 'msg' => trans('store.cannot_delete')]);
            }
            if ($id != 1) {
                AdminStore::destroy($id);
            }
            return response()->json(['error' => 0, 'msg' => '']);
        }
    }

    public function edit($id)
    {
        $store = AdminStore::find($id);

        if ($store === null) {
            return 'no data';
        }
        $languages = ShopLanguage::getCodeActive();
        $data = [
            'title' => trans('store_info.admin.list'),
            'sub_title' => '',
            'icon' => 'fa fa-indent',
            'menu_left' => '',
            'menu_right' => '',
            'menu_sort' => '',
            'script_sort' => '',
            'menu_search' => '',
            'script_search' => '',
            'listTh' => '',
            'dataTr' => '',
            'pagination' => '',
            'result_items' => '',
            'url_delete_item' => '',
        ];

        $infosDescription = [];
        foreach ($languages as $code => $lang) {
            $langDescriptions = AdminStoreDescription::where('config_id', $store->id)->where('lang', $code)->first();

            $infosDescription['title'][$code] = $langDescriptions['title'] ?? "";
            $infosDescription['description'][$code] = $langDescriptions['description'] ?? "";
            $infosDescription['keyword'][$code] = $langDescriptions['keyword'] ?? "";
            $infosDescription['maintain_content'][$code] = $langDescriptions['maintain_content'] ?? "";
        }

        $infos = $store; //AdminStore::first();
        $data['infos'] = $infos;
        $data['infosDescription'] = $infosDescription;
        $data['languages'] = $languages;
        $data['approval_status'] = $this->approval_status();
        $data['locations'] = Location::all()->toArray();
        //dd($infos->banners()->get());
        return view('admin.screen.store_info')
            ->with($data);
    }

    /*
      Update value config
     */

    public function updateInfo2()
    {
        $stt = 0;
        $data = request()->all();
        $name = $data['name'];
        $value = $data['value'];
        $parseName = explode('__', $name);
        if (!in_array($parseName[0], ['title', 'description', 'keyword', 'maintain_content'])) {
            $update = AdminStore::first()->update([$name => $value]);
        } else {
            $update = AdminStore::first()->descriptions()->where('lang', $parseName[1])->update([$parseName[0] => $value]);
        }
        if ($update) {
            $stt = 1;
        }
        return response()->json(['stt' => $stt]);
    }

    public function seller_details($id)
    {

        $seller = AdminStore::find($id);
        if ($seller === null) {
            return 'no data';
        }
        //dd($seller->products);
        $products_item = $seller->products;
        $orders = $seller->orders()->orderBy('id', 'desc')->get();
        //dd($orders[0]->details);
        $paymentMethodTmp = sc_get_extension('payment', $onlyActive = false);
        foreach ($paymentMethodTmp as $key => $value) {
            $paymentMethod[$key] = sc_language_render($value->detail);
        }
        $shippingMethodTmp = sc_get_extension('shipping', $onlyActive = false);
        foreach ($shippingMethodTmp as $key => $value) {
            $shippingMethod[$key] = sc_language_render($value->detail);
        }

        return view('admin.screen.store_details')->with(
            [
                "title" => trans('store_info.store_info'),
                "sub_title" => '',
                'icon' => 'fa fa-file-text-o',
                "order" => $orders,
                "products" => $products_item,
                'seller' => $seller,
                "statusOrder" => $this->statusOrder,
                "statusPayment" => $this->statusPayment,
                "statusShipping" => $this->statusShipping,
                "statusOrderMap" => $this->statusOrderMap,
                "statusShippingMap" => $this->statusShippingMap,
                'attributesGroup' => ShopAttributeGroup::pluck('name', 'id')->all(),
                'paymentMethod' => $paymentMethod,
                'shippingMethod' => $shippingMethod,
                'countryMap' => $this->countryMap,
            ]
        );
    }

    public function approval_status()
    {
      return [
        0 => strtoupper(trans('store.approval_status.0')),
        1 => strtoupper(trans('store.approval_status.1')),
        2 => strtoupper(trans('store.approval_status.2'))
      ];
    }

    public function updateBanners($id)
    {

      if (!request()->ajax()) {
          return response()->json(['error' => 1, 'msg' => 'Method not allow!']);
      } else {
        $data = request()->all();

        $banner_images = $data['banner_images'] ?? [];
         if(empty($banner_images)){
           return response()->json(['error' => 1, 'msg' => 'No Image is selected']);
         }
         $store = AdminStore::find($id);

         if(!$store){
           return response()->json(['error' => 1, 'msg' => 'Store not found']);
         }

         $store->banners()->delete();
         $arrBannerImages = [];
         foreach ($banner_images as $key => $image) {
             if ($image) {
                 $image_name = cdn_image_upload_from_storage($image, 'store_featured');
                 $arrBannerImages[] = new StoreBannerImage(['image' => $image_name]);
             }
         }
         $store->banners()->saveMany($arrBannerImages);

          return response()->json(['error' => 0, 'msg' => '']);
      }
    }
}
