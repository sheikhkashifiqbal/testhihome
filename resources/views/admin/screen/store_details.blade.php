@extends('admin.layout')

@section('main')


<div class="box">

    <div class="row">
        <div class="col-sm-4">
            {!! cdn_image_render($seller['logo'],'300', '300') !!}
        </div>
        <div class="col-sm-3">
            <h4>{{$seller->description['title']}}</h4>
            <h4>{{trans('store.admin.legal_business_name').':'.$seller['legal_business_name']}}</h4>
            <h5>{{trans('order.shipping_address').':'.$seller['address']}}</h5>
            <h5>{{$seller['time_active']}}</h5>
            <h5>{{trans('store_info.emirates_id').' : '.$seller['seller_eid']}}</h5>
            <h5>{{trans('store_info.contact_us_email').' : '.$seller['contact_us_email']}}</h5>
            <h5>{{trans('store_info.phone').' : '.$seller['phone']}}</h5>
            <h5>{{trans('store_info.city').' : '.$seller['city']}}</h5>
            <h5>{{trans('store_info.license_start_date').' : '.\Carbon\Carbon::parse($seller['license_start_date'])->format('d-m-Y')}}</h5>
            <h5>{{trans('store_info.license_end_date').' : '.\Carbon\Carbon::parse($seller['license_end_date'])->format('d-m-Y')}}</h5>
            <h5>{{trans('store_info.license_id').' : '.$seller['license_id']}}</h5>
            <h5>{{trans('store_info.approval').' : '.strtoupper(trans('store.approval_status.'.$seller['approval']))}}</h5>

        </div>
        <div class="col-sm-4">
            <div class="bg-red info-box-content">
              <span class="info-box-number">Total Sale: {{$order->sum('total')}} AED</span>
              <span class="info-box-number">Seller Earning: {{calculateSellerEarning($order->sum('total'))}} AED</span>
              <span class="info-box-number">HIHOME Earning: {{calculateHiHomeCommision($order->sum('total'))}} AED</span>
            </div>
        </div>
    </div>
</div>

<div class="box">
    <h5 >Orders</h5>
    <div class="row" id="order-body">


        <div class="col-sm-12">
          <div class="box collapsed-box">
          <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>{{trans('order.admin.id')}}</th>
                    <th>{{trans('order.admin.number')}}</th>
                    <th>{{ trans('product.name') }}</th>
                    <th>{{ trans('order.customer_name') }}</th>
                    <th>{{ trans('order.shipping_address') }}</th>
                    <th>{{ trans('order.admin.total') }}</th>
                    <th>{{ trans('order.seller_earning') }}</th>
                    <th>{{ trans('order.hihome_earning') }}</th>
                    <th>{{ trans('order.admin.created_at') }}</th>
                  </tr>
                </thead>
                <tbody>
                    @foreach ($order as $ord)
                    <?php
                      $hihome_earning = calculateHiHomeCommision($ord->total);
                      $seller_earning = calculateSellerEarning($ord->total);
                    ?>
                          <tr>
                            <td>{{ $ord->id }}</td>
                            <td>{{ $ord->number }}</td>
                            <td>
                                @foreach ($ord->details as $item)
                                 <p>{{ $item->qty }} X {{ $item->name }}</p>
                                @endforeach
                            </td>
                            <td>{{ $ord->first_name . ' ' . $ord->last_name }}</td>
                            <td>{{ $ord->address1 }} </td>
                            <td>{{ sc_currency_render_symbol($ord->total ?? 0, $ord->currency) }} </td>
                            <td>{{ sc_currency_render_symbol( $seller_earning ?? 0, $ord->currency) }} </td>
                            <td>{{ sc_currency_render_symbol( $hihome_earning ?? 0, $ord->currency) }} </td>
                            <td>{{ \Carbon\Carbon::parse($ord->created_at)->format('d-m-Y h:i a') }} </td>
                          </tr>
                    @endforeach

                </tbody>
              </table>
            </div>
        </div>
        </div>

      </div>


    </div>
<div class="box">
       <h5>Menu</h5>
    <div class="row" id="order-body">


        <div class="col-sm-12">
          <div class="box collapsed-box">
          <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>{{ trans('product.name') }}</th>
                    <th class="">{{ trans('product.image') }}</th>
                    <th>{{ trans('product.sku') }}</th>
                    <th class="">{{ trans('product.price') }}</th>


                  </tr>
                </thead>
                   <tbody>
                       <?php $i=0;?>
                @foreach($products as $pr)
                <?php
                $i++;
                ?>
                <tr>
                    <td>{{$i}}</td>
                    <td>{{$pr->details->name}}</td>
                    <td>{!! cdn_image_render($pr->image, '50', '50') !!}</td>
                    <td>{{$pr->sku}}</td>
                    <td>{{$pr->price}}</td>

                </tr>
                @endforeach

                </tbody>
            </table>
          </div>
          </div>
        </div>
    </div>
</div>

    @php
    if($seller->balance == 0){
    $style = 'style="color:#0e9e33;font-weight:bold;"';
    }else
    if($seller->balance < 0){
    $style = 'style="color:#ff2f00;font-weight:bold;"';
    }else{
    $style = 'style="font-weight:bold;"';
    }
    @endphp







@endsection


@push('styles')
<style type="text/css">
    .history{
        max-height: 50px;
        max-width: 300px;
        overflow-y: auto;
    }
    .td-title{
        width: 35%;
        font-weight: bold;
    }
    .td-title-normal{
        width: 35%;
    }
    .product_qty{
        width: 80px;
        text-align: right;
    }
    .product_price,.product_total{
        width: 120px;
        text-align: right;
    }

</style>
<!-- Ediable -->
<link rel="stylesheet" href="{{ asset('admin/plugin/bootstrap-editable.css')}}">
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('admin/AdminLTE/bower_components/select2/dist/css/select2.min.css')}}">
@endpush

@push('scripts')
{{-- //Pjax --}}
<script src="{{ asset('admin/plugin/jquery.pjax.js')}}"></script>

<!-- Ediable -->
<script src="{{ asset('admin/plugin/bootstrap-editable.min.js')}}"></script>

<!-- Select2 -->
<script src="{{ asset('admin/AdminLTE/bower_components/select2/dist/js/select2.full.min.js')}}"></script>


@endpush
