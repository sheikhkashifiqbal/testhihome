@extends('admin.layout')

@section('main')

<div class="row">
  <div class="col-md-12">
    <div class="card-header with-border">
      <div class="float-left">
        
      </div>
      <div class="card-tools">
        <div class="menu-right">
            <a href="{{ route('admin_store.create') }}" class="btn btn-success btn-flat btn-sm" title="New" id="button_create_new">
            <i class="fa fa-plus" title="{{ trans('store.admin.add_new') }}"></i>
            </a>
        </div>
      </div>
      
    </div>
    </div>
</div>

<div class="row">
  <div class="col-md-12" id="pjax-container">

    @foreach ($stories as $store)
    <div class="card collapsed-card">
      <div class="card-header with-border">
        <h3 class="card-title"><i class="fas fa-home"></i> {{ trans('store.admin.title') }} #{{ $store->id }} 
         {{--  (<i class="fas fa-link"></i> <a target=_new href="//{{ $store->domain }}">{{ $store->domain }}</a>) --}}
        </h3>
        <div class="card-tools">
          <div class="menu-right">
            @if ($store->id != 1)
            <input class="store-status" name="{{ $store->id }}__status" data-on-text="{{ trans('admin.unlock') }}" data-off-text="{{ trans('admin.lock') }}" type="checkbox"  {{ ($store->status == '1'?'checked':'') }}>
            @endif
            <input class="store-active" name="{{ $store->id }}__active" data-on-text="{{ trans('admin.maintain_enable') }}" data-off-text="{{ trans('admin.maintain_disable') }}" type="checkbox"  {{ ($store->active == '1'?'checked':'') }}>
            @if ($store->id != 1)
            <span onclick="deleteItem({{ $store->id }});" title="Delete" class="btn btn-flat btn-danger">
              <i class="fas fa-trash-alt"></i>
            </span>
            @endif
          </div>
        </div>
      </div>
      
      <div class="card-header with-border">
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-plus"></i>
          </button>
        </div>
      </div>


    <div class="card-body">
      <div class="row">
      <div class="col-md-5">
        <table class="table table-hover table-bordered">
        <tbody>
          <tr>
            <td>{{ trans('store.logo') }}</td>
            <td>
                <div class="input-group">
                    <input type="hidden" id="{{ $store->id }}__logo" name="{{ $store->id }}__logo" value="{{ $store->logo }}" class="form-control input-sm logo" placeholder=""  />
                </div>
                <div id="{{ $store->id }}__preview_image" class="img_holder">{!! sc_image_render($store->logo,'100px', '', 'Logo') !!}</div>
                  <a data-input="{{ $store->id }}__logo" data-preview="{{ $store->id }}__preview_image" data-type="logo" class="lfm pointer">
                    <i class="fa fa-image"></i> {{trans('product.admin.choose_image')}}
                  </a>
            </td>
          </tr>
    
          <tr>
            <td><i class="fas fa-phone-alt"></i> {{ trans('store.phone') }}</td>
            <td><a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__phone" data-type="number" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.phone') }}" data-value="{{ $store->phone }}" data-original-title="" title="">{{$store->phone }}</a></td>
          </tr>
    
          <tr>
            <td><i class="fas fa-phone-square"></i> {{ trans('store.long_phone') }}</td>
            <td><a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__long_phone" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.long_phone') }}" data-value="{{ $store->long_phone }}" data-original-title="" title="">{{$store->long_phone }}</a></td>
          </tr>
    
          <tr>
            <td><i class="far fa-calendar-alt"></i> {{ trans('store.time_active') }}</td>
            <td><a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__time_active" data-type="textarea" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.time_active') }}" data-value="{{ $store->time_active }}" data-original-title="" title="">{{$store->time_active }}</a></td>
          </tr>
    
          <tr>
            <td><i class="fas fa-map-marked"></i> {{ trans('store.address') }}</td>
            <td><a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__address" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.address') }}" data-value="{{ $store->address }}" data-original-title="" title="">{{$store->address }}</a></td>
          </tr>
          <tr>
            <td><i class="fas fa-location-arrow"></i></span> {{ trans('store.office') }}</td>
            <td><a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__office" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.office') }}" data-value="{{ $store->office }}" data-original-title="" title="">{{$store->office }}</a></td>
          </tr>
          <tr>
            <td><i class="fas fa-warehouse"></i> {{ trans('store.warehouse') }}</td>
            <td><a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__warehouse" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.warehouse') }}" data-value="{{ $store->warehouse }}" data-original-title="" title="">{{$store->warehouse }}</a></td>
          </tr>
    
          <tr>
            <td><i class="fas fa-envelope"></i> {{ trans('store.email') }}</td>
            <td><a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__email" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.email') }}" data-value="{{ $store->email }}" data-original-title="" title="">{{$store->email }}</a></td>
          </tr>
    
          <tr>
            <td><i class="fab fa-chrome"></i> {{ trans('store.domain') }}</td>
            <td><a href="#" class="fied-domain editable editable-click" data-name="{{ $store->id }}__domain" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.domain') }}" data-value="{{ $store->domain }}" data-original-title="" title="">{{$store->domain }}</a></td>
          </tr>

          <tr>
            <td><i class="far fa-money-bill-alt nav-icon"></i> {{ trans('store.currency') }}</td>
            <td>
              <a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__currency" data-type="select" data-pk="" data-source="{{ json_encode($currencies) }}" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.currency') }}" data-value="{{ $store->currency }}" data-original-title="" title=""></a>
             </td>
          </tr>


          <tr>
            <td><i class="fas fa-language nav-icon"></i> {{ trans('store.language') }}</td>
            <td>
              <a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__language" data-type="select" data-pk="" data-source="{{ json_encode($languages->pluck('name','code')->toArray()) }}" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.language') }}" data-value="{{ $store->language }}" data-original-title="" title=""></a>
             </td>
          </tr>

          <tr>
            <td><i class="fas fa-clock"></i> {{ trans('store.timezone') }}</td>
            <td>
              <a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__timezone" data-type="select" data-pk="" data-source="{{ json_encode($timezones) }}" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.timezone') }}" data-value="{{ $store->timezone }}" data-original-title="" title=""></a>
             </td>
          </tr>

          <tr>
            <td><i class="nav-icon  fas fa-object-ungroup "></i>{{ trans('store.template') }}</td>
            <td>
              <a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__template" data-type="select" data-pk="" data-source="{{ json_encode($templates) }}" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.template') }}" data-value="{{ $store->template }}" data-original-title="" title=""></a>
             </td>
          </tr>

        </td>
      </tr>
    
        </tbody>
           </table>
      </div>
    @php
        $descriptions = $store->descriptions->keyBy('lang');
    @endphp
      <div class="col-md-7">
        <table class="table table-hover table-bordered">
          <tbody>
            <tr>
              <td>{{ trans('store.title') }}</td>
              <td>
                @foreach ($descriptions as  $codeLang => $infoDescription)
                @php
                    if (!in_array($codeLang, array_keys($languages->toArray()))) {
                      continue;
                    }
                @endphp
                  {{ $languages[$codeLang]->name }} <img src="{{ asset($languages[$codeLang]->icon )}}" style="width:20px">:<br>
                <i><a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__{{ 'title__'.$codeLang }}" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.title') }}" data-value="{{ $infoDescription['title'] }}" data-original-title="" title="">{{ $infoDescription['title'] }}</a></i><br>
                <br>
                @endforeach
              </td>
            </tr>

            <tr>
              <td>{{ trans('store.keyword') }}</td>
              <td>
                @foreach ($descriptions as  $codeLang => $infoDescription)
                @php
                    if (!in_array($codeLang, array_keys($languages->toArray()))) {
                      continue;
                    }
                @endphp
                  {{ $languages[$codeLang]->name }} <img src="{{ asset($languages[$codeLang]->icon )}}" style="width:20px">:<br>
                <i><a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__{{ 'keyword__'.$codeLang }}" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.keyword') }}" data-value="{{ $infoDescription['keyword'] }}" data-original-title="" title="">{{ $infoDescription['keyword'] }}</a></i><br>
                <br>
                @endforeach
              </td>
            </tr>

            <tr>
              <td>{{ trans('store.description') }}</td>
              <td>
                @foreach ($descriptions as  $codeLang => $infoDescription)
                @php
                    if (!in_array($codeLang, array_keys($languages->toArray()))) {
                      continue;
                    }
                @endphp
                  {{ $languages[$codeLang]->name }} <img src="{{ asset($languages[$codeLang]->icon )}}" style="width:20px">:<br>
                <i><a href="#" class="fied-required editable editable-click" data-name="{{ $store->id }}__{{ 'description__'.$codeLang }}" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store.update') }}" data-title="{{ trans('store.description') }}" data-value="{{ $infoDescription['description'] }}" data-original-title="" title="">{{ $infoDescription['description'] }}</a></i><br>
                <br>
                @endforeach
              </td>
            </tr>

        </tbody>
        </table>
      </div>
    
    </div>
      </div>
  </div>
  @endforeach
  </div>
</div>


@endsection


@push('styles')
<!-- Ediable -->
<link rel="stylesheet" href="{{ asset('admin/plugin/bootstrap-editable.css')}}">
<style type="text/css">
  #maintain_content img{
    max-width: 100%;
  }
</style>
@endpush

@push('scripts')
<!-- Ediable -->
<script src="{{ asset('admin/plugin/bootstrap-editable.min.js')}}"></script>

<script type="text/javascript">
  // Editable
$(document).ready(function() {

      //  $.fn.editable.defaults.mode = 'inline';
      $.fn.editable.defaults.params = function (params) {
        params._token = "{{ csrf_token() }}";
        return params;
      };

      $('.fied-required').editable({
        validate: function(value) {
            if (value == '') {
                return '{{  trans('admin.not_empty') }}';
            }
        },
        success: function(data) {
          if(data.error == 0){
            alertJs('success', '{{ trans('admin.msg_change_success') }}');
          } else {
            alertJs('error', data.msg);
          }
      }
    });

    $('.fied-domain').editable({
        validate: function(value) {
            if (value == '') {
                return '{{  trans('admin.not_empty') }}';
            }
        },
        success: function(data) {
          if(data.error == 0){
            alertJs('success', '{{ trans('admin.msg_change_success') }}');
          } else {
            alertJs('error', data.msg);
          }
      }
    });

});
</script>


  <script type="text/javascript">

    {!! $script_sort??'' !!}

  </script>

{{-- //Pjax --}}
<script src="{{ asset('admin/plugin/jquery.pjax.js')}}"></script>

<script>
  // Update store_info

//Logo
  $('.logo').change(function() {
        $.ajax({
        url: '{{ route('admin_store.update') }}',
        type: 'POST',
        dataType: 'JSON',
        data: {"name": $(this).attr('name'),"value":$(this).val(),"_token": "{{ csrf_token() }}",},
      })
      .done(function(data) {
        if(data.error == 0){
          alertJs('success', '{{ trans('admin.msg_change_success') }}');
        } else {
          alertJs('error', data.msg);
        }
      });
  });
//End logo


  function deleteItem(id){
  Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success',
      cancelButton: 'btn btn-danger'
    },
    buttonsStyling: true,
  }).fire({
    title: '{{ trans('admin.store_confirm_delete') }} #'+id,
    text: "",
    type: 'warning',
    showCancelButton: true,
    confirmButtonText: '{{ trans('admin.confirm_delete_yes') }}',
    confirmButtonColor: "#DD6B55",
    cancelButtonText: '{{ trans('admin.confirm_delete_no') }}',
    reverseButtons: true,

    preConfirm: function() {
        return new Promise(function(resolve) {
            $.ajax({
                method: 'post',
                url: '{{ $urlDeleteItem ?? '' }}',
                data: {
                  id:id,
                    _token: '{{ csrf_token() }}',
                },
                success: function (data) {
                  console.log(data);
                    if(data.error == 1){
                      alertMsg('error', data.msg, '{{ trans('admin.warning') }}');
                      $.pjax.reload('#pjax-container');
                      return;
                    }else{
                      alertMsg('success', data.msg);
                      $.pjax.reload('#pjax-container');
                      resolve(data);
                    }

                }
            });
        });
    }

  }).then((result) => {
    if (result.value) {
      alertMsg('success', '{{ trans('admin.confirm_delete_deleted_msg') }}', '{{ trans('admin.confirm_delete_deleted') }}');
    } else if (
      // Read more about handling dismissals
      result.dismiss === Swal.DismissReason.cancel
    ) {
    }
  })
}
  //End update store_info
</script>

<script type="text/javascript">
  $(".store-status, .store-active").bootstrapSwitch();
  $('.store-status, .store-active').on('switchChange.bootstrapSwitch', function (event, state) {
      var site_status;
      if (state == true) {
          site_status =  '1';
      } else {
          site_status = '0';
      }
      $('#loading').show();

      $.ajax({
        type: 'POST',
        dataType:'json',
        url: "{{ route('admin_store.update') }}",
        data: {
          "_token": "{{ csrf_token() }}",
          "name": $(this).attr('name'),
          "value": site_status
        },
        success: function (response) {
            // console.log(site_status);
          if(parseInt(response.error) ==0){
            alertMsg('success', '{{ trans('admin.msg_change_success') }}');
          }else{
            alertMsg('error', response.msg);
          }
          $('#loading').hide();
        }
      });
  }); 

  $(".domain-strict").bootstrapSwitch().on('switchChange.bootstrapSwitch', function (event, state) {
      var site_status;
      if (state == true) {
          site_status =  '1';
      } else {
          site_status = '0';
      }
      $('#loading').show();

      $.ajax({
        type: 'POST',
        dataType:'json',
        url: "",
        data: {
          "_token": "{{ csrf_token() }}",
          "name": $(this).attr('name'),
          "value": site_status
        },
        success: function (response) {
            // console.log(site_status);
          if(parseInt(response.error) ==0){
            alertMsg('success', '{{ trans('admin.msg_change_success') }}');
          }else{
            alertMsg('error', response.msg);
          }
          $('#loading').hide();
        }
      });
  }); 

</script>

@endpush
