@extends('admin.layout')

@section('main')


<div class="box">

    <div class="row">
        <div class="col-sm-8 pl-1" >
            <h4>{{trans('feedback.admin.table_header.customer_name').': '.$feedback['customer_name']}}</h4>
            <h4>{{trans('feedback.admin.table_header.customer_email').': '.$feedback['customer_email']}}</h4>
            <h4>{{trans('feedback.admin.table_header.customer_phone').': '.$feedback['customer_phone']}}</h4>
            <h4>{{trans('feedback.admin.table_header.type').': '.ucwords($feedback['type'])}}</h4>
            <h4>{{trans('feedback.admin.table_header.body').': '.$feedback['body']}}</h4>
            <h4>{{trans('feedback.admin.table_header.created_at').': '.\Carbon\Carbon::parse($feedback['created_at'])->format('d-m-Y h:i a') }}</h4>
            <h4>{{trans('feedback.admin.table_header.image')}}: </h4>
              <br /><img style="width: 800px;" src="{{url('/').'/'.$feedback['image']}}"/>

        </div>
    </div>
</div>

@endsection


@push('styles')
<style type="text/css">
    .pl-1{
        padding-left: 50px;
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
