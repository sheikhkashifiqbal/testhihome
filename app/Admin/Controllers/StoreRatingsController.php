<?php
namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\RateReview\Services\StoreRatingService;
use App\Models\ShopUser;

class StoreRatingsController extends Controller
{

  public function index($id)
  {

    // $status = request('active', 1);
    $data = [
        'title' => trans('rating.admin.list_page_title'),
        'sub_title' => '',
        'icon' => 'fa fa-indent',
        'url_delete_item' => '',
    ];

    $data['dataTr'] = [];

    $data['listTh'] = $this->returnListTh();
    $sort_order = request('sort_order') ?? 'id__desc';

    $keyword = request('keyword') ?? '';

    $arrSort = $this->returnSortArray();
    $reviews  = StoreRatingService::getStoreReviews($id, $sort_order);

    $dataTmp = $reviews->paginate(20);
    $data['dataTr'] = $this->returnTableTr($dataTmp);
    $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links('admin.component.pagination');
    $data['result_items'] = trans('offers.admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'item_total' => $dataTmp->total()]);

    $data['menu_left'] = $this->returnLeftMenu();


    $optionSort = $this->returnSortArrayOptions($sort_order);

    $data['menu_sort'] = $this->returnMenuSort($optionSort);

    $data['script_sort'] = $this->returnScriptSort($id);


    return view('admin.screen.list')
        ->with($data);
  }//index

  private function returnListTh()
  {
    return [
        'id'            => trans('rating.admin.table_header.id'),
        'customer_name' => trans('rating.admin.table_header.customer_name'),
        'rating'        => trans('rating.admin.table_header.rating'),
        'review'        => trans('rating.admin.table_header.review'),
        'status'        => trans('rating.admin.table_header.status'),
        'created_date'  => trans('rating.admin.table_header.created_date'),
        'action'        => trans('rating.admin.table_header.action'),
    ];
  }//returnListTh

  private function returnTableTr($dataTmp)
  {
    $dataTr = [];
    foreach ($dataTmp as $key => $row) {
        $customer = ShopUser::find($row['user_id']);
        $status   = trans('rating.status.'.$row['status']);
        $status_label = $row['status'] === 2 ? 'label-danger' :  ($row['status'] === 1 ? 'label-success' : 'label-info');
        $dataTr[] = [
            'id' => $row['id'],
            'customer_name' => $customer->first_name .' '. $customer->last_name,
            'rating' => $row['rate'],
            'review' => $row['review'],
            'status' =>  '<span class="label '.$status_label.'" style="text-transform:uppercase">'.$status.'</span>',
            'created_date' => date('d-m-Y', strtotime($row['created_at'])),
            'action' => '
                <a href="' . route('admin_store_rating.edit', ['id' => $row['id']]) . '"><span title="' . trans('store.admin.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;
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
                  <a class="btn   btn-flat btn-primary grid-refresh" title="Refresh"><i class="fa fa-refresh"></i><span class="hidden-xs"> ' . trans('rating.admin.refresh') . '</span></a> &nbsp;
                  </div>';
  }//returnLeftMenu

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

  private function returnScriptSort($id)
  {
    return "$('#button_sort').click(function(event) {
          var url = '" . route('admin_store_rating.index', $id) . "?sort_order='+$('#order_sort option:selected').val();
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

  public function edit($id)
  {
    $rating = StoreRatingService::getStoreReviewById($id);
    //dd($rating);
    if ($rating === null) {
        return 'no data';
    }
    $customer = ShopUser::find($rating->user_id);
    $data = [
        'title' => trans('rating.admin.detail_page_title'),
        'sub_title' => '',
        'icon' => 'fa fa-indent',
        'url_delete_item' => '',
    ];

    $data['rating'] = $rating;
    $data['customer'] = $customer;
    $data['statuses'] = StoreRatingService::getStatusesArray();

    return view('admin.screen.store_review_info')
        ->with($data);
  }//edit

  public function updateStatus(Request $request)
  {
    // $id = request()->id;
    // $status = request()->status;

    $review = StoreRatingService::getStoreReviewById($request->id);
    if($review){
        $review->status = $request->status;
        $review->save();
        return response()->json(['error' => false, 'msg' => 'Status succesfully update.']);
    }else{
      return response()->json(['error' => true, 'msg' => 'Not found']);
    }


  }//updateStatus


}
