@extends('admin.layout')

@section('main')
<style>
    #viewer_modal .modal-content { max-width: 600px; }
img#img_viewer { width: 100%; height: 100%; border-radius: 10px; }
</style>
<div class="row">

  <div class="col-md-6">

    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">{{ trans('store_info.admin.config_mode') }}</h3>
      </div>

      <div class="box-body table-responsive no-padding box-primary">
       <table class="table table-hover table-bordered">
         <thead>
           <tr>
             <th>{{ trans('store_info.admin.field') }}</th>
             <th>{{ trans('store_info.admin.value') }}</th>
           </tr>
         </thead>
         <tbody>


      <tr>
        <td>{{ trans('store_info.logo') }}
               <span class="input-group-btn">
                 <a data-input="image1" data-preview="preview_image1" data-type="store" class="btn btn-sm btn-flat btn-primary lfm">
                   <i class="fa fa-picture-o"></i> {{trans('product.admin.choose_image')}}
                 </a>
               </span>

        </td>
        <td>
            <div class="input-group">
                <input type="hidden" id="image1" name="{{ $infos->id }}__logo" value="{{ $infos->logo }}" class="form-control input-sm image" placeholder=""  />
            </div>
              @if ($errors->has('image1'))
                  <span class="help-block">
                      {{ $errors->first('image1') }}
                  </span>
              @endif
            <div id="preview_image1" class="img_holder">{!! cdn_image_render($infos->logo) !!}</div>

        </td>
      </tr>

      <tr>
          <td>{{ trans('store_info.rank') }}</td>
          <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__rank" data-type="number" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.ranks') }}" data-value="{{ $infos->rank }}" data-original-title="" title="">{{$infos->rank }}</a></td>
      </tr>
        <tr>
          <td>{{ trans('store_info.reviewer_count') }}</td>
          <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__reviewer_count" data-type="number" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.reviewer_count') }}" data-value="{{ $infos->reviewer_count }}" data-original-title="" title="">{{$infos->reviewer_count }}</a></td>
      </tr>
       <tr>

          <td>{{ trans('store_info.accept_orders') }}
          </td>

          <td><input class="input" type="checkbox" name="{{ $infos->id }}__accept_orders"  {{($infos->accept_orders)?'checked':''}}>   </td>
      </tr>
       <tr>

          <td>{{ trans('store_info.status') }}
          </td>

          <td><input class="input" type="checkbox" name="{{ $infos->id }}__status"  {{($infos->status)?'checked':''}}>   </td>
      </tr>
       <tr>

          <td>{{ trans('store_info.approval') }}
          </td>

          <td>
            <select class="form-control approval select2" style="width: 100%;" name="{{ $infos->id }}__approval" >
                @foreach ($approval_status as $k => $v)
                    <option value="{{ $k }}" {{ $infos->approval == $k ? 'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
          </td>
      </tr>



@foreach ($infosDescription as $obj => $infoDescription)
  @if ($obj =='title')
  <tr>
    <td>{{ trans('store_info.'.$obj) }}</td>
    <td>
      @foreach ($infoDescription as $codeLanguage => $des)

        {{ $languages[$codeLanguage] }}:<br>
        <i><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__{{ $obj.'__'.$codeLanguage }}" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.'.$obj) }}" data-value="{{ $des }}" data-original-title="" title="">{{ $des }}</a></i><br>
        <br>
      @endforeach
    </td>
  </tr>
  @endif
@endforeach


<tr>

   <td>{{ trans('store_info.city') }}
   </td>

   <td>
     <select class="form-control approval select2" style="width: 100%;" name="{{ $infos->id }}__emirates_id" >
         @foreach ($locations as $k => $v)
             <option value="{{ $v['id'] }}" {{ $infos->emirates_id == $v['id'] ? 'selected':'' }}>{{ $v['name'] }}</option>
         @endforeach
     </select>
   </td>
</tr>

    </tbody>
       </table>
      </div>
    </div>
  </div>


  <div class="col-md-6">
    <div class="box box-primary">

        <div class="box-body table-responsive no-padding box-primary">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>{{ trans('store_info.admin.field') }}</th>
                        <th>{{ trans('store_info.admin.value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ trans('store_info.emirates_id') }}</td>
                        <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__seller_eid" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.emirates_id') }}" data-value="{{ $infos->seller_eid }}" data-original-title="" title="">{{$infos->seller_eid }}</a></td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.legal_business_email') }}</td>
                        <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__legal_business_email" data-type="email" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.legal_business_email') }}" data-value="{{ $infos->legal_business_email }}" data-original-title="" title="">{{$infos->legal_business_email }}</a></td>
                    </tr>

                    <tr>
                        <td>{{ trans('store_info.legal_business_phone') }}</td>
                        <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__legal_business_phone" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.legal_business_phone') }}" data-value="{{ $infos->legal_business_phone }}" data-original-title="" title="">{{$infos->legal_business_phone }}</a></td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.license_photo') }}
                            <span class="input-group-btn">
                                <a data-input="license_photo" data-preview="license_photo2" data-type="store" class="btn btn-sm btn-flat btn-primary lfm">
                                    <i class="fa fa-picture-o"></i> {{trans('product.admin.choose_image')}}
                                </a>
                            </span>

                        </td>
                        <td>
                            <div class="input-group">
                                <input type="hidden" id="license_photo" name="{{ $infos->id }}__license_photo" value="{{ $infos->license_photo }}" class="form-control input-sm image" placeholder=""  />
                            </div>
                            @if ($errors->has('license_photo'))
                            <span class="help-block">
                                {{ $errors->first('license_photo') }}
                            </span>
                            @endif
                            <div id="license_photo2" class="img_holder">{!! cdn_image_render($infos->license_photo) !!}</div>

                        </td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.license_number') }}</td>
                        <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__license_id" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.license_id') }}" data-value="{{ $infos->license_id }}" data-original-title="" title="">{{$infos->license_id }}</a></td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.license_start_date') }}</td>
                        <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__license_start_date" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.license_start_date') }}" data-value="{{ $infos->license_start_date }}" data-original-title="" title="">{{\Carbon\Carbon::parse($infos->license_start_date)->format('d-m-Y')}}</a></td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.license_end_date') }}</td>
                        <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__license_end_date" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.legal_business_phone') }}" data-value="{{ $infos->license_end_date }}" data-original-title="" title="">{{\Carbon\Carbon::parse($infos->license_end_date)->format('d-m-Y')}}</a></td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.contact_us_first_name') }}</td>
                        <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__contact_us_first_name" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.contact_us_first_name') }}" data-value="{{ $infos->contact_us_first_name }}" data-original-title="" title="">{{$infos->contact_us_first_name }}</a></td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.contact_us_last_name') }}</td>
                        <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__contact_us_last_name" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.contact_us_last_name') }}" data-value="{{ $infos->contact_us_last_name }}" data-original-title="" title="">{{$infos->contact_us_last_name }}</a></td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.contact_us_email') }}</td>
                        <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__contact_us_email" data-type="email" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.contact_us_email') }}" data-value="{{ $infos->contact_us_email }}" data-original-title="" title="">{{$infos->contact_us_email }}</a></td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.contact_us_phone') }}</td>
                        <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__phone" data-type="text" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.phone') }}" data-value="{{ $infos->phone }}" data-original-title="" title="">{{$infos->phone}}</a></td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.login_email') }}</td>
                        <td>{{$infos->user->email }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('store_info.login_password') }}</td>
                        <td>{{$infos->user->real_password }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
  </div>



</div>

<div class="row">
  <div class="col-md-6">

    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">{{ trans('store_info.admin.banner_images') }}</h3>
        <form method="post" id="bm_form" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="box-body table-responsive box-primary">

          @php
          $bannerImages = $infos->banners()->pluck('image')->all();

          @endphp
          @if (!empty($bannerImages))
            @foreach ($bannerImages as $key => $banner)

            <div class="group-image">
                <div class="input-group" >
                  <input type="text"
                          id="banner_image_{{ $key}}"
                          name="banner_images[]"
                          value="{!! $banner !!}" class="form-control input-sm sub_image" placeholder=""
                          />
                   <span class="input-group-btn">
                    <span>
                        <a data-input="banner_image_{{ $key }}"
                            data-preview="preview_sub_image_{{ $key  }}"
                            data-type="store" class="btn btn-sm btn-flat btn-primary lfm">
                            <i class="fa fa-picture-o"></i>
                       {{trans('product.admin.choose_image')}}
                       </a>
                     </span>
                       <span title="Remove" class="btn btn-flat btn-sm btn-danger removeImage">
                         <i class="fa fa-times"></i>
                       </span>
                   </span>
                    </div>
                <div id="preview_sub_image_{{ $key  }}" class="img_holder">
                  
                  {!! cdn_image_render($banner) !!}
                </div>
            </div>


            @endforeach
          @endif

          <button type="button" id="add_sub_image" class="btn btn-flat btn-success">
              <i class="fa fa-plus" aria-hidden="true"></i>
              {{ trans('product.admin.add_sub_image') }}
          </button>

          <div class="btn-group pull-right">
              <button type="submit" class="btn btn-primary">{{ trans('admin.submit') }}</button>
          </div>

        </form>
        </div>
      </div>
  </div>
</div>


<div class="modal" id="viewer_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <img id="img_viewer" src="" />
            </div>
        </div>
    </div>
</div>

<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{ trans('store_info.reject_msg') }}</h4>
      </div>
      <div class="modal-body">


          <td><a href="#" class="fied-required editable editable-click" data-name="{{ $infos->id }}__reject_msg" data-type="textarea" data-pk="" data-source="" data-url="{{ route('admin_store_info.update') }}" data-title="{{ trans('store_info.reject_msg') }}" data-value="{{ ($infos->reject_msg) ?: 'Write Reason' }}" data-original-title="" title="">{{$infos->reject_msg }}</a></td>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
@endsection


 @push('styles')
{{-- switch --}}
<link rel="stylesheet" href="{{ asset('admin/plugin/bootstrap-switch.min.css')}}">



<!-- Ediable -->
<link rel="stylesheet" href="{{ asset('admin/plugin/bootstrap-editable.css')}}">
<style type="text/css">
  #maintain_content img{
    max-width: 100%;
  }
</style>
@endpush

@push('scripts')
{{-- switch --}}
<script src="{{ asset('admin/plugin/bootstrap-switch.min.js')}}"></script>

<script type="text/javascript">
    $("[name='top'],[name='status'],").bootstrapSwitch();
</script>
<!-- Ediable -->
<script src="{{ asset('admin/plugin/bootstrap-editable.min.js')}}"></script>

<script type="text/javascript">
  // Editable
$(document).ready(function() {

       $.fn.editable.defaults.mode = 'inline';
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
          //  alert(data.error)
          if(data.error == 0){
             $('#myModal').modal('hide');
            const Toast = Swal.mixin({
              toast: true,
              position: 'top-end',
              showConfirmButton: false,
              timer: 3000
            });

            Toast.fire({
              type: 'success',
              title: '{{ trans('admin.msg_change_success') }}'
            })
          }
      }
    });
});
</script>


  <script type="text/javascript">

    {!! $script_sort??'' !!}

  </script>

<script type="text/javascript">
{{-- sweetalert2 --}}
var selectedRows = function () {
    var selected = [];
    $('.grid-row-checkbox:checked').each(function(){
        selected.push($(this).data('id'));
    });

    return selected;
}

</script>
<script>
  // Update store_info

//Logo
  $('[name="{{ $infos->id }}__logo"]').change(function(event) {
          $.ajax({
        url: '{{ route('admin_store_info.update') }}',
        type: 'POST',
        dataType: 'JSON',
        data: {"name": '{{ $infos->id }}__logo',"value":$(this).val(),"_token": "{{ csrf_token() }}",},
      })
      .done(function(data) {
        if(data.stt == 1){
          const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          });

          Toast.fire({
            type: 'success',
            title: '{{ trans('admin.msg_change_success') }}'
          })
        }
      });
  });
//End logo
//Logo2 Dark-mode
$('[name="{{ $infos->id }}__logo2"]').change(function(event) {
          $.ajax({
        url: '{{ route('admin_store_info.update') }}',
        type: 'POST',
        dataType: 'JSON',
        data: {"name": '{{ $infos->id }}__logo2',"value":$(this).val(),"_token": "{{ csrf_token() }}",},
      })
      .done(function(data) {
        if(data.stt == 1){
          const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          });

          Toast.fire({
            type: 'success',
            title: '{{ trans('admin.msg_change_success') }}'
          })
        }
      });
  });
//End logo2 Dark-mode
//licence photo
$('[name="{{ $infos->id }}__license_photo"]').change(function(event) {
          $.ajax({
        url: '{{ route('admin_store_info.update') }}',
        type: 'POST',
        dataType: 'JSON',
        data: {"name": '{{ $infos->id }}__license_photo',"value":$(this).val(),"_token": "{{ csrf_token() }}",},
      })
      .done(function(data) {
        if(data.stt == 1){
          const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          });

          Toast.fire({
            type: 'success',
            title: '{{ trans('admin.msg_change_success') }}'
          })
        }
      });
  });
//end liences photo
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' /* optional */
    }).on('ifChanged', function(e) {
      var isChecked = e.currentTarget.checked;
      var name = $(this).attr('name');
      isChecked = (isChecked == false)?0:1;
       let requestData = {"name": name,"value":isChecked,"_token": "{{ csrf_token() }}"};
       sendUpdateStoreRequest(requestData);
    });//checkbox change

    $(".approval").change(function(){
      var name = $(this).attr('name');
      var value = $(this).val();

      if(value == "{{ STORE_REJECTED }}"){
        $('#myModal').modal('show');
      }else{
        $('#myModal').modal('hide');
        let requestData = {"name": name,"value":value,"_token": "{{ csrf_token() }}"};
        sendUpdateStoreRequest(requestData);
      }

    })

  });//function

  $('.img_holder').click(function() {
      // change viewer's img
      $('#img_viewer').attr('src', $(this).find('img').attr('src'))
      // show modal
      $('#viewer_modal').modal('show')
  });

  function sendUpdateStoreRequest(requestData)
  {
      $.ajax({
        url: '{{ route('admin_store_info.update') }}',
        type: 'POST',
        dataType: 'JSON',
        data: requestData,
      })
      .done(function(data) {
        if(data.error == 0){
          const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          });

          Toast.fire({
            type: 'success',
            title: '{{ trans('admin.msg_change_success') }}'
          })
        }
      });
  }//sendUpdateStoreRequest
  //End update store_info

  // Add banner images
      var id_sub_image = {{ $infos->banners()->count() }};
      $('#add_sub_image').click(function(event) {
      id_sub_image += 1;
      $(this).before('<div class="group-image"><div class="input-group"><input type="text" id="banner_image_' + id_sub_image + '" name="banner_images[]" value="" class="form-control input-sm sub_image" placeholder=""  /><span class="input-group-btn"><span><a data-input="banner_image_' + id_sub_image + '" data-preview="preview_sub_image_' + id_sub_image + '" data-type="store" class="btn btn-sm btn-flat btn-primary lfm"><i class="fa fa-picture-o"></i> {{trans('product.admin.choose_image')}}</a></span><span title="Remove" class="btn btn-flat btn-sm btn-danger removeImage"><i class="fa fa-times"></i></span></span></div><div id="preview_sub_image_' + id_sub_image + '" class="img_holder"></div></div>');
      $('.removeImage').click(function(event) {
      $(this).closest('.group-image').remove();
      });
      $('.lfm').filemanager();
      });
      $('.removeImage').click(function(event) {
      $(this).closest('.group-image').remove();
      });
  //end banner images

  //upload banner Images

   $('#bm_form').on('submit', function(event){
    event.preventDefault();
    $.ajax({
     url:"{{ route('admin_store_info.update-banners', ['id' =>  $infos->id]) }}",
     method:"POST",
     data:new FormData(this),
     dataType:'JSON',
     contentType: false,
     cache: false,
     processData: false,
     success:function(data)
     {
       if(data.error == 0){
         const Toast = Swal.mixin({
           toast: true,
           position: 'top-end',
           showConfirmButton: false,
           timer: 3000
         });

         Toast.fire({
           type: 'success',
           title: 'Banner Images updated successfully'
         })
       }else{
         alert(data.msg);
       }
     }
    })
   });
  //end of upload banner images
</script>
@endpush
