@extends('admin.layout')

@section('main')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h2 class="box-title">{{ $title_description??'' }}</h2>

                <div class="box-tools">
                    <div class="btn-group pull-right" style="margin-right: 5px">
                        <a href="{{ route('admin_offers.index') }}" class="btn  btn-flat btn-default" title="List"><i
                                class="fa fa-list"></i><span class="hidden-xs"> {{trans('admin.back_list')}}</span></a>
                    </div>
                </div>
            </div>
            <!-- /.card-header -->

            <!-- form start -->
            <form action="{{ $url_action }}" method="post" accept-charset="UTF-8" class="form-horizontal" id="form-main"  enctype="multipart/form-data">

                <div class="box-body">
                    <div class="fields-group">

                        @foreach ($languages as $code => $language)


                        <div class="form-group">
                            <label class="col-sm-2  control-label"></label>
                            <div class="col-sm-8">
                                <b>{{ $language->title }}</b>
                                {!! sc_image_render($language->icon,'20px','20px') !!}
                            </div>
                        </div>

                        <div
                            class="form-group {{ $errors->has('descriptions.'.$code.'.title') ? ' has-error' : '' }}">
                            <label for="{{ $code }}__title"
                                   class="col-sm-2  control-label">{{ trans('news.title') }} <span class="seo" title="SEO"><i class="fa fa-coffee" aria-hidden="true"></i></span></label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                     <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" dir="{{($code == 'ar') ? 'rtl' : ''}}"  id="{{ $code }}__title" name="descriptions[{{ $code }}][title]"
                                           value="{{ old('descriptions.'.$code.'.title') }}"
                                           class="form-control {{ $code.'__title' }}" placeholder="" />
                                </div>
                                @if ($errors->has('descriptions.'.$code.'.title'))
                                 <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('descriptions.'.$code.'.title') }}
                                </span>
                                @else
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ trans('admin.max_c',['max'=>200]) }}
                                </span>
                                @endif
                            </div>
                        </div>

                            <div
                                class="form-group {{ $errors->has('descriptions.'.$code.'.description') ? ' has-error' : '' }}">
                                <label for="{{ $code }}__description"
                                       class="col-sm-2 control-label">{{ trans('news.description') }} <span class="seo" title="SEO"><i class="fa fa-coffee" aria-hidden="true"></i></span></label>
                                <div class="col-sm-8">
                                    <textarea  id="{{ $code }}__description" dir="{{($code == 'ar') ? 'rtl' : ''}}"
                                               name="descriptions[{{ $code }}][description]"
                                               class="form-control {{ $code.'__description' }}" placeholder="" >{{ old('descriptions.'.$code.'.description') }}</textarea>
                                    @if ($errors->has('descriptions.'.$code.'.description'))
                                    <span class="help-block">
                                        <i class="fa fa-info-circle"></i> {{ $errors->first('descriptions.'.$code.'.description') }}
                                    </span>
                                    @else
                                    <span class="help-block">
                                        <i class="fa fa-info-circle"></i> {{ trans('admin.max_c',['max'=>300]) }}
                                    </span>
                                    @endif
                                </div>
                            </div>



                        @endforeach


                        <div class="form-group {{ $errors->has('code') ? ' has-error' : '' }}">
                            <label for="code" class="col-sm-2 control-label">{{ trans('offers.admin.form.code') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="text" id="code" name="code" value="{{ old('code') }}" class="form-control" placeholder="" />
                                </div>
                                @if ($errors->has('code'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('code') }}
                                </span>
                                @endif
                            </div>
                        </div>


                        <div class="form-group {{ $errors->has('value') ? ' has-error' : '' }}">
                            <label for="value" class="col-sm-2 control-label">{{ trans('offers.admin.form.value') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="number" id="value" name="value" value="{{ old('value') }}" class="form-control" placeholder="" />
                                </div>
                                @if ($errors->has('value'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('value') }}
                                </span>
                                @endif
                            </div>
                        </div>


                        <div class="form-group {{ $errors->has('start_date') ? ' has-error' : '' }}">
                            <label for="start_date" class="col-sm-2 control-label">{{ trans('offers.admin.form.start_date') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="text" id="start_date" name="start_date" value="{{ old('start_date') }}" class="form-control date_time" placeholder="" />
                                </div>
                                @if ($errors->has('start_date'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('start_date') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('end_date') ? ' has-error' : '' }}">
                            <label for="end_date" class="col-sm-2 control-label">{{ trans('offers.admin.form.end_date') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="text" id="end_date" name="end_date" value="{{ old('end_date') }}" class="form-control date_time" placeholder="" />
                                </div>
                                @if ($errors->has('end_date'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('end_date') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group ">
                            <label for="status" class="col-sm-2 control-label">{{ trans('offers.admin.form.status') }}</label>
                            <div class="col-sm-8">
                                <input class="input" type="checkbox" name="status"  {{ old('status',(empty($store['status'])?0:1))?'checked':''}}>
                            </div>
                        </div>

                        <!-- /.card-body -->

                        <div class="box-footer">
                            @csrf
                            <div class="col-md-2">
                            </div>

                            <div class="col-md-8">
                                <div class="btn-group pull-right">
                                    <button type="submit" class="btn btn-primary">{{ trans('admin.submit') }}</button>
                                </div>

                                <div class="btn-group pull-left">
                                    <button type="reset" class="btn btn-warning">{{ trans('admin.reset') }}</button>
                                </div>
                            </div>
                        </div>

                        <!-- /.card-footer -->
                        </form>

                    </div>
                </div>
        </div>


        @endsection

  @push('styles')
{{-- switch --}}
<link rel="stylesheet" href="{{ asset('admin/plugin/bootstrap-switch.min.css')}}">


@endpush


@push('scripts')
{{-- switch --}}
<script src="{{ asset('admin/plugin/bootstrap-switch.min.js')}}"></script>

<script type="text/javascript">
    $("[name='top'],[name='status']").bootstrapSwitch();

    $("#start_date").datepicker({
       format: 'dd-mm-yyyy',
       autoclose: true,
   }).on('changeDate', function (selected) {
       var curentDate = new Date(selected.date.valueOf());
       var minDate = new Date(curentDate.setDate(curentDate.getDate() + 1));
       $('#end_date').datepicker('setStartDate', minDate);
       $('#end_date').datepicker('setDate', minDate);
   });

   $("#end_date").datepicker({
       format: 'dd-mm-yyyy',
       autoclose: true,
   });
</script>

 @endpush
