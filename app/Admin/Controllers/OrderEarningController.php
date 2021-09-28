<?php
namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ShopOrder;
use App\Models\ShopOrderTotal;
use App\Models\ShopProduct;
use App\Models\ShopAttributeGroup;
use App\Models\ShopOrderStatus;
use App\Models\ShopShippingStatus;
use DB;
use App\Models\AdminStore;

class OrderEarningController extends Controller
{

  //earnings
  public function earnings()
  {

      $data = [
          'title' => trans('order.admin.list'),
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

      $listTh = [
          // 'check_row' => '',
          'id' => trans('order.admin.id'),
          'logo' => trans('store.logo'),
          'name' => trans('store.name'),
          'from' => trans('order.customer_name'),
          'address' => trans('order.shipping_address'),
          'total' => trans('order.admin.total'),
          'seller_earning' => trans('order.seller_earning'),
          'hihome_earning' => trans('order.hihome_earning'),
          'created_at' => trans('order.admin.created_at'),
          'action' => trans('order.admin.action'),
      ];
      $sort_order = request('sort_order') ?? 'id_desc';
      $keyword = request('keyword') ?? '';
      $store_id = (int) request('store_id') ?? '';

      $order_date_from = request('order_date_from') ?? date('Y-m-d');
      $order_date_to = request('order_date_to') ?? date('Y-m-d');
      $arrSort = [
          'created_at__desc' => trans('order.admin.sort_order.date_desc'),
          'created_at__asc' => trans('order.admin.sort_order.date_asc'),
          'id__desc' => trans('order.admin.sort_order.id_desc'),
          'id__asc' => trans('order.admin.sort_order.id_asc'),
          /* 'email__desc' => trans('order.admin.sort_order.email_desc'),
          'email__asc' => trans('order.admin.sort_order.email_asc'), */

      ];
      $obj = new ShopOrder;
      if ($store_id) {
          $obj = $obj->where('store_id', $store_id);
      }

      //$obj = $obj->where('status', (int) 1);

      if ($order_date_from) {
          $obj = $obj->whereBetween(DB::raw('DATE(created_at)'), array($order_date_from, $order_date_to));
      }
      if ($keyword) {
          $obj = $obj->whereRaw('(id = ' . (int) $keyword . ' OR email like "%' . $keyword . '%" )');
      }

      if ($sort_order && array_key_exists($sort_order, $arrSort)) {
          $field = explode('__', $sort_order)[0];
          $sort_field = explode('__', $sort_order)[1];
          $obj = $obj->orderBy($field, $sort_field);
      } else {
          $obj = $obj->orderBy('id', 'desc');
      }
      $dataTmp = $obj->paginate(20);

      $dataTr = [];
      $total_earnings = 0;
      $cur = '';
      foreach ($dataTmp as $key => $row) {
          $hihome_earning = calculateHiHomeCommision($row['total']);
          $seller_earning = calculateSellerEarning($row['total']);
          $dataTr[] = [
              'id' => $row['id'],
              'logo' => sc_image_render($row->store['logo'], '50px', '50px'),
              'name' => "<p>".$row->store->description['title']. "</p><p> #".$row['number']."</p>",
              'from' => $row['first_name'] . ' ' . $row['last_name'],
              'address' => $row['address1'],
              'total' => sc_currency_render_symbol($row['total'] ?? 0, $row['currency']),
              'seller_earning' =>sc_currency_render_symbol( $seller_earning?? 0, $row['currency']),
              'hihome_earning' =>sc_currency_render_symbol( $hihome_earning?? 0, $row['currency']),
              'created_at' => $row['created_at'],
              'action' => '<a href="' . route('admin_order.detail_earning', ['id' => $row['id']]) . '"><span title="' . trans('order.admin.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;',
          ];
          $total_earnings += $row['total'];
          $cur = $row['currency'];
      }

      $data['listTh'] = $listTh;
      $data['dataTr'] = $dataTr;
      $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links('admin.component.pagination');
      $data['result_items'] = trans('order.admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'item_total' => $dataTmp->total()]);
      //menu_left
      $data['menu_left'] = '<div class="pull-left">
                            <a class="btn   btn-flat btn-primary grid-refresh" title="Refresh"><i class="fa fa-refresh"></i><span class="hidden-xs"> ' . trans('admin.refresh') . '</span></a> &nbsp;</div>
                            ';

      $data['menu_right'] = '<div class="btn-group pull-right" style="margin-right: 10px">
                                <div class="bg-red info-box-content">

                                  <span class="info-box-number">Total Sale: ' . $total_earnings . ' ' . $cur . '</span>
                                  <span class="info-box-number">Seller Earning: ' . calculateSellerEarning($total_earnings) . ' ' . $cur . '</span>
                                  <span class="info-box-number">HIHOME Earning: ' . calculateHiHomeCommision($total_earnings) . ' ' . $cur . '</span>

                                </div>
                            </div>';

      $optionSort = '';
      foreach ($arrSort as $key => $status) {
          $optionSort .= '<option  ' . (($sort_order == $key) ? "selected" : "") . ' value="' . $key . '">' . $status . '</option>';
      }

      $data['menu_sort'] = '
                     <div class="btn-group pull-left">
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

      $data['script_sort'] = "$('#button_sort').click(function(event) {
        var url = '" . route('admin_order.earnings') . "?sort_order='+$('#order_sort option:selected').val()+'&'+$('#button_search').serialize();
        $.pjax({url: url, container: '#pjax-container'})
      });";


      $optionStatus = '';
      $stores = AdminStore::getStores();

      foreach ($stores as $key => $store) {
          $optionStatus .= '<option  ' . (($store_id == $key) ? "selected" : "") . ' value="' . $key . '">' . $store . '</option>';
      }

      $data['menu_search'] = '
              <form action="' . route('admin_order.earnings') . '" id="button_search">
                 <div onclick="$(this).submit();location.reload();" class="btn-group pull-right">
                         <a class="btn btn-flat btn-primary" title="Refresh">
                            <i class="fa  fa-search"></i><span class="hidden-xs"> ' . trans('admin.search') . '</span>
                         </a>
                 </div>
                 <div class="btn-group pull-right">
                       <div class="form-group">
                         <input type="text" name="keyword" class="form-control" placeholder="' . trans('order.admin.search_place') . '" value="' . $keyword . '">
                       </div>
                 </div>

                 <div class="btn-group pull-right"  style="margin-right: 10px">
                      <div class="form-group">
                      <input type="text" id="order_date_to" name="order_date_to" value="' . $order_date_to . '" class="form-control date_time" style="width: 100px;" placeholder="" />
                      </div>
                  </div>

                 <div class="btn-group pull-right"  style="margin-right: 10px">
                      <div class="form-group">
                      <input type="text" id="order_date_from" name="order_date_from" value="' . $order_date_from . '" class="form-control date_time" style="width: 100px;" placeholder="" />
                      </div>
                  </div>

                 <div class="btn-group pull-right"  style="margin-right: 10px">
                      <div class="form-group">
                         <select class="form-control" name="store_id">
                           <option value="">' . trans('order.admin.search_order.all_seller') . '</option>
                           ' . $optionStatus . '
                          </select>
                      </div>
                  </div>


              </form>';


      $data['url_delete_item'] = route('admin_order.delete');

      return view('admin.screen.list')
          ->with($data);
  }//earnings

  public function earnings_details($id)
  {

      $order = ShopOrder::find($id);
      if ($order === null) {
          return 'no data';
      }
      $order_item = json_decode($order->items, true);
      //dd($order_item);
      $products = ShopProduct::getArrayProductName();
      $paymentMethodTmp = sc_get_extension('payment', $onlyActive = false);
      foreach ($paymentMethodTmp as $key => $value) {
          $paymentMethod[$key] = sc_language_render($value->detail);
      }
      $shippingMethodTmp = sc_get_extension('shipping', $onlyActive = false);
      foreach ($shippingMethodTmp as $key => $value) {
          $shippingMethod[$key] = sc_language_render($value->detail);
      }
      return view('admin.screen.earnings_details')->with(
          [
              "title" => trans('order.order_detail'),
              "sub_title" => '',
              'icon' => 'fa fa-file-text-o',
              "order" => $order,
              "products" => $products,
              "statusOrder" => ShopOrderStatus::getListStatus(),
              "statusPayment" => '',
              "statusShipping" => ShopShippingStatus::getListStatus(),
              "statusOrderMap" => '',
              "statusShippingMap" => '',
              'dataTotal' => ShopOrderTotal::getTotal($id),
              'attributesGroup' => ShopAttributeGroup::pluck('name', 'id')->all(),
              'paymentMethod' => $paymentMethod,
              'shippingMethod' => $shippingMethod,
              'countryMap' => '',
          ]
      );
  }//earnings_details

}
