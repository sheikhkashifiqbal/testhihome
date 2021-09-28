<?php
namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

use App\Models\Feedback;

class FeedbackController extends Controller
{

  public function index()
  {

    $data = [
        'title' => trans('feedback.admin.page_title'),
        'sub_title' => '',
        'icon' => 'fa fa-indent'
    ];
    //dd($data);
    $data['listTh'] = $this->returnListTh();
    $sort_order = request('sort_order') ?? 'id__desc';
    //
    $keyword = request('keyword') ?? '';
    $type = request('type') ?? '';
    //
    $arrSort = $this->returnSortArray();
    $feedbacks  = Feedback::withOrderBy($sort_order);

    if ($keyword) {
        $feedbacks = $feedbacks->withSearchKeyword($keyword);
    }

    if ($type) {
        $feedbacks = $feedbacks->where('type', $type);
    }

    $dataTmp = $feedbacks->paginate(20);
    $data['dataTr'] = $this->returnTableTr($dataTmp);
    $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links('admin.component.pagination');
    $data['result_items'] = trans('offers.admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'item_total' => $dataTmp->total()]);

    $data['menu_left'] = $this->returnLeftMenu();

    $optionSort = $this->returnSortArrayOptions($sort_order);

    $data['menu_sort'] = $this->returnMenuSort($optionSort);

    $data['script_sort'] = $this->returnScriptSort();
    //
    $data['menu_search'] = $this->returnMenuSearch($keyword, $type);
    //
    $data['url_delete_item'] = '';

    return view('admin.screen.offers.list')
        ->with($data);
  }//index

  private function returnListTh()
  {
    return [
        'id' => trans('feedback.admin.table_header.id'),
        'image' => trans('feedback.admin.table_header.image'),
        'customer_name' => trans('feedback.admin.table_header.customer_name'),
        'customer_email' => trans('feedback.admin.table_header.customer_email'),
        'customer_phone' => trans('feedback.admin.table_header.customer_phone'),
        'type' => trans('feedback.admin.table_header.type'),
        'body' => trans('feedback.admin.table_header.body'),
        'created_at' => trans('feedback.admin.table_header.created_at'),
        'action' => trans('offers.admin.table_header.action'),
    ];
  }//returnListTh

  private function returnTableTr($dataTmp)
  {
    $dataTr = [];
    foreach ($dataTmp as $key => $row) {
        $dataTr[] = [
            'id' => $row['id'],
            'image' => sc_image_render($row['image'], '50px', '50px'),
            'customer_name' => $row['customer_name'],
            'customer_email' => $row['customer_email'],
            'customer_phone' => $row['customer_phone'],
            'type' => ucwords($row['type']),
            'body' => Str::substr($row['body'], 0, 50) . " ...",
            'created_at' => date('d-m-Y h:i a', strtotime($row['created_at'])),
            'action' => '<a href="' . route('admin_feedbacks.details', ['id' => $row['id']]) . '"><span title="' . trans('feedback.admin.feedback_info') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-eye"></i></span></a>',
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
                  <a class="btn btn-flat btn-primary grid-refresh" title="Refresh"><i class="fa fa-refresh"></i><span class="hidden-xs"> ' . trans('offers.admin.refresh') . '</span></a> &nbsp;
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

  private function returnScriptSort()
  {
    return "$('#button_sort').click(function(event) {
          var url = '" . route('admin_feedbacks.index') . "?sort_order='+$('#order_sort option:selected').val();
          $.pjax({url: url, container: '#pjax-container'})
        });";
  }//returnScriptSort

  private function returnMenuSearch($keyword, $reqeust_type)
  {
    $types_array = returnFeedbackTypes();
    $types_options = '';
    foreach ($types_array as $type) {
        $types_options .= '<option ' . (($reqeust_type == $type) ? "selected" : "") . ' value="' . $type . '">' . ucwords($type) . '</option>';
    }

    return '<form action="' . route('admin_feedbacks.index') . '" id="button_search">
               <div onclick="$(this).submit();" class="btn-group pull-right">
                       <a class="btn btn-flat btn-primary" title="Refresh">
                          <i class="fa  fa-search"></i><span class="hidden-xs"> ' . trans('admin.search') . '</span>
                       </a>
               </div>

               <div class="btn-group pull-right">
                     <div class="form-group">
                       <input type="text" name="keyword" class="form-control" placeholder="' . trans('feedback.admin.search_place') . '" value="' . $keyword . '">
                     </div>
               </div>
               <div class="btn-group pull-right"  style="margin-right: 10px">
                    <div class="form-group">
                       <select class="form-control" name="type">
                         <option value="">' . trans('feedback.admin.all_types') . '</option>
                         ' . $types_options . '
                        </select>
                    </div>
                </div>
            </form>';
  }//returnMenuSearch

  public function details($id)
  {
    $feedback  = Feedback::findorFail($id);
    return view('admin.screen.feedback_details')->with(
          [
              "title" => trans('feedback.admin.feedback_info'),
              "sub_title" => '',
              'icon' => 'fa fa-file-text-o',
              "feedback" => $feedback,
          ]
      );
    }//details

}
