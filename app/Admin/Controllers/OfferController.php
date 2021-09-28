<?php
namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ShopLanguage;
use App\Models\Offer;
use App\Models\OfferDescription;
use Validator;
use Carbon\Carbon;

class OfferController extends Controller
{

  public $templates, $languages;

  public function __construct()
  {
      foreach (timezone_identifiers_list() as $key => $value) {
          $timezones[$value] = $value;
      }
      $this->templates = []; //$templates;
      $this->languages = ShopLanguage::getListActive();
  }

  public function index()
  {
    $status = request('active', 1);
    $data = [
        'title' => trans('offers.admin.page_title'),
        'sub_title' => '',
        'icon' => 'fa fa-indent'
    ];

    $data['listTh'] = $this->returnListTh();
    $sort_order = request('sort_order') ?? 'id__desc';

    $keyword = request('keyword') ?? '';

    $arrSort = $this->returnSortArray();
    $offers = Offer::withOrderBy($sort_order);

    if ($keyword) {
        $offers = $offers->withSearchKeywords($keyword);
    }

    $dataTmp = $offers->paginate(20);
    $data['dataTr'] = $this->returnTableTr($dataTmp);
    $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links('admin.component.pagination');
    $data['result_items'] = trans('offers.admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'item_total' => $dataTmp->total()]);

    $data['menu_left'] = $this->returnLeftMenu();

    $data['menu_right'] = $this->returnRightMenu();

    $optionSort = $this->returnSortArrayOptions($sort_order);

    $data['menu_sort'] = $this->returnMenuSort($optionSort);

    $data['script_sort'] = $this->returnScriptSort();

    $data['menu_search'] = $this->returnMenuSearch($keyword);

    $data['url_delete_item'] = route('admin_offers.delete');

    return view('admin.screen.offers.list')
        ->with($data);
  }//index

  private function returnListTh()
  {
    return [
        'id' => trans('offers.admin.table_header.id'),
        'code' => trans('offers.admin.table_header.code'),
        'value' => trans('offers.admin.table_header.value'),
        'start_date' => trans('offers.admin.table_header.start_date'),
        'end_date' => trans('offers.admin.table_header.end_date'),
        'status' => trans('offers.admin.table_header.status'),
        'action' => trans('offers.admin.table_header.action'),
    ];
  }//returnListTh

  private function returnTableTr($dataTmp)
  {
    $dataTr = [];
    foreach ($dataTmp as $key => $row) {
        $dataTr[] = [
            'id' => $row['id'],
            'code' => $row['code'],
            'value' => $row['value'],
            'start_date' => date('d-m-Y', strtotime($row['start_date'])),
            'end_date' => date('d-m-Y', strtotime($row['end_date'])),
            'status' => $row['status'] ? '<span class="label label-success">ON</span>' : '<span class="label label-danger">OFF</span>',
            'action' => '
                <a href="' . route('admin_offers.edit', ['id' => $row['id']]) . '"><span title="' . trans('store.admin.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;
                <span onclick="deleteItem(' . $row['id'] . ');"  title="' . trans('store.admin.delete') . '" class="btn btn-flat btn-danger"><i class="fa fa-trash"></i></span>
              ',
        ];
    }
    return $dataTr;
  }//returnTableTr

  private function returnSortArray()
  {
    return [
        'id__desc' => trans('offers.admin.sort_order.id_desc'),
        'id__asc' => trans('offers.admin.sort_order.id_asc')
    ];
  }//returnSortArray

  private function returnSortArrayOptions($sort_order)
  {
    $arrSort = $this->returnSortArray();
    $optionSort = '';
    foreach ($arrSort as $key => $status) {
        $optionSort .= '<option  ' . (($sort_order == $key) ? "selected" : "") . ' value="' . $key . '">' . $status . '</option>';
    }
    return $optionSort;
  }//returnSortArrayOptions

  private function returnLeftMenu()
  {
    return '<div class="pull-left">
                  <a class="btn   btn-flat btn-primary grid-refresh" title="Refresh"><i class="fa fa-refresh"></i><span class="hidden-xs"> ' . trans('offers.admin.refresh') . '</span></a> &nbsp;
                  </div>';
  }//returnLeftMenu

  private function returnRightMenu()
  {
    return '<div class="btn-group pull-right" style="margin-right: 10px">
                       <a href="' . route('admin_offers.create') . '" class="btn  btn-success  btn-flat" title="New" id="button_create_new">
                       <i class="fa fa-plus"></i><span class="hidden-xs">' . trans('offers.admin.add_new') . '</span>
                       </a>
                    </div>';
  }//returnRightMenu

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
  }//returnMenuSort

  private function returnScriptSort()
  {
    return "$('#button_sort').click(function(event) {
          var url = '" . route('admin_offers.index') . "?sort_order='+$('#order_sort option:selected').val();
          $.pjax({url: url, container: '#pjax-container'})
        });";
  }//returnScriptSort

  private function returnMenuSearch($keyword)
  {
    return '<form action="' . route('admin_offers.index') . '" id="button_search">
               <div onclick="$(this).submit();" class="btn-group pull-right">
                       <a class="btn btn-flat btn-primary" title="Refresh">
                          <i class="fa  fa-search"></i><span class="hidden-xs"> ' . trans('admin.search') . '</span>
                       </a>
               </div>
               <div class="btn-group pull-right">
                     <div class="form-group">
                       <input type="text" name="keyword" class="form-control" placeholder="' . trans('offers.admin.search_place') . '" value="' . $keyword . '">
                     </div>
               </div>
            </form>';
  }//returnMenuSearch

  /*
  * CREATE OFFER FORM
  */
  public function create()
  {
    $data = [
        'title' => trans('offers.admin.add_offer'),
        'subTitle' => '',
        'title_description' => trans('offers.admin.create_offer'),
        'icon' => 'fa fa-plus',
        'store' => [],
        'languages' => $this->languages,
        'url_action' => route('admin_offers.create'),
        'templates' => []
    ];

    return view('admin.screen.offers.create')
        ->with($data);
  }

  /*
  * STORE OFFER IN DB
  */
  public function store()
  {
    $data = request()->except('_method', '_token');

    $validator = Validator::make($data, [
        'descriptions.*.description' => 'required|string|max:300',
        'descriptions.*.title' => 'required|string|max:300',
        'code' => 'required|unique:offers',
        'value' => 'required',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
    ], [
        'descriptions.*.description.required' => trans('validation.required', ['attribute' => trans('offers.admin.validation.description')]),
        'descriptions.*.title.required' => trans('validation.required', ['attribute' => trans('offers.admin.validation.title')]),
    ]);
    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput($data);
    }

    $offerData = request()->except('_method', '_token', 'descriptions');
    $offerData['start_date'] = Carbon::parse($offerData['start_date']);
    $offerData['end_date'] = Carbon::parse($offerData['end_date']);
    $offerData['status'] = empty($data['status']) ? 0 : 1;


    $offer = Offer::create($offerData);

    $offerDes = request()->only('descriptions');
    $dataDes = [];
    $languages = ShopLanguage::getListActive();
    foreach ($languages as $code => $value) {
        $dataDes[] = [
            'offer_id' => $offer->id,
            'lang' => $code,
            'description' => $data['descriptions'][$code]['description'],
            'title' => $data['descriptions'][$code]['title'],
        ];
    }
    OfferDescription::insert($dataDes);

    return redirect()->route('admin_offers.index')->with('success', trans('store.admin.create_success'));
  }


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
          $offer = offer::find($id);
          $offer->delete();
          return response()->json(['error' => 0, 'msg' => 'Order is successfully deleted.']);
      }
  }

  /*
  *  SHOW EDIT FORM
  */
  public function edit($id)
  {
      $offer = Offer::find($id);

      if ($offer === null) {
          return 'no data';
      }

      $descriptions = $offer->descriptions->keyBy('lang')->toArray();

      $data = [
          'title' => trans('product.admin.edit'),
          'sub_title' => '',
          'title_description' => '',
          'icon' => 'fa fa-pencil-square-o',
          'languages' => $this->languages,
          'offer' => $offer,
          'descriptions' => $descriptions,

      ];
      return view('admin.screen.offers.edit')
          ->with($data);
  }

  /*
  * UPDATE the offer
  */
  public function postEdit($id)
  {

    $data = request()->except('_method', '_token');

    $validator = Validator::make($data, [
        'descriptions.*.description' => 'required|string|max:300',
        'descriptions.*.title' => 'required|string|max:300',
        'code' => 'required|unique:offers,code,'.$id.',id',
        'value' => 'required',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
    ], [
        'descriptions.*.description.required' => trans('validation.required', ['attribute' => trans('offers.admin.validation.description')]),
        'descriptions.*.title.required' => trans('validation.required', ['attribute' => trans('offers.admin.validation.title')]),
    ]);
    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput($data);
    }

    $offer = Offer::find($id);

    $offerData = request()->except('_method', '_token', 'descriptions');
    $offerData['start_date'] = Carbon::parse($offerData['start_date']);
    $offerData['end_date'] = Carbon::parse($offerData['end_date']);
    $offerData['status'] = empty($data['status']) ? 0 : 1;

    $offer->update($offerData);

    $offer->descriptions()->delete();
    $offerDes = request()->only('descriptions');
    $dataDes = [];
    $languages = ShopLanguage::getListActive();
    foreach ($languages as $code => $value) {
        $dataDes[] = [
            'offer_id' => $offer->id,
            'lang' => $code,
            'description' => $data['descriptions'][$code]['description'],
            'title' => $data['descriptions'][$code]['title'],
        ];
    }
    OfferDescription::insert($dataDes);


    return redirect()->route('admin_offers.index')->with('success', trans('product.admin.edit_success'));
  }

}
