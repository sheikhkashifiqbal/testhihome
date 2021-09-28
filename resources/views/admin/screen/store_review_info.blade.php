@extends('admin.layout')

@section('main')
<style>
    #viewer_modal .modal-content { max-width: 600px; }
img#img_viewer { width: 100%; height: 100%; border-radius: 10px; }
</style>
<div class="row">

  <div class="col-md-6">
    <div class="box box-primary">

      <div class="box-body table-responsive no-padding box-primary">
          <table class="table table-hover table-bordered">
            <thead>
              <tr>
                <th>{{ trans('rating.admin.field') }}</th>
                <th>{{ trans('rating.admin.value') }}</th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td>{{ trans('rating.admin.table_header.customer_name') }}</td>
                <td>{{$customer->first_name .' '. $customer->last_name}}</td>
              </tr>
              <tr>
                <td>{{ trans('rating.admin.table_header.rating') }}</td>
                <td>{{$rating->rate }}</td>
              </tr>
              <tr>
                <td>{{ trans('rating.admin.table_header.review') }}</td>
                <td>{{$rating->review }}</td>
              </tr>
              <tr>
                <td>{{ trans('rating.admin.table_header.created_date') }}</td>
                <td>{{date('d-m-Y', strtotime($rating->created_at)) }}</td>
              </tr>
              <tr>
                <td>{{ trans('rating.admin.table_header.status') }}</td>
                <td>
                  <select class="form-control status select2" style="width: 100%;" name="status" >
                      @foreach ($statuses as $k => $v)
                          <option value="{{ $k }}" {{ (old('status', $rating->status ??'') ==$k) ? 'selected':'' }}>{{ $v }}</option>
                      @endforeach
                  </select>
                </td>
              </tr>

            </tbody>
          </table>
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
<!-- Ediable -->
<script src="{{ asset('admin/plugin/bootstrap-editable.min.js')}}"></script>

<script type="text/javascript">
  $(function () {
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });

      $('.status').change(function(){
        let value = $(this).val();
        let id = '{{$rating->id }}';

        $.ajax({
          url: '{{ route('admin_store_rating.update_status') }}',
          type: 'POST',
          dataType: 'JSON',
          data: {"id": id, "status": value,"_token": "{{ csrf_token() }}"},
        }).done(function(data) {
          let toast_type = 'success';
          if(data.error){
            toast_type = 'error';
          }

          Toast.fire({
            type: toast_type,
            title: data.msg
          })
          // if(data.stt == 1){
          //   const Toast = Swal.mixin({
          //     toast: true,
          //     position: 'top-end',
          //     showConfirmButton: false,
          //     timer: 3000
          //   });
          //
          //   Toast.fire({
          //     type: 'success',
          //     title: '{{ trans('admin.msg_change_success') }}'
          //   })
          // }
        });
      })//end status change action
  });
</script>

@endpush
