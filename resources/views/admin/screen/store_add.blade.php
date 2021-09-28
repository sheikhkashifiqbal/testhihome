@extends('admin.layout')

@section('main')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h2 class="box-title">{{ $title_description??'' }}</h2>

                <div class="box-tools">
                    <div class="btn-group pull-right" style="margin-right: 5px">
                        <a href="{{ route('admin_page.index') }}" class="btn  btn-flat btn-default" title="List"><i
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
                                        <input type="text" id="{{ $code }}__title" name="descriptions[{{ $code }}][title]"
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
                                class="form-group {{ $errors->has('descriptions.'.$code.'.keyword') ? ' has-error' : '' }}">
                                <label for="{{ $code }}__keyword"
                                       class="col-sm-2 control-label">{{ trans('news.keyword') }} <span class="seo" title="SEO"><i class="fa fa-coffee" aria-hidden="true"></i></span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                       <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                        <input type="text" id="{{ $code }}__keyword"
                                               name="descriptions[{{ $code }}][keyword]"
                                               value="{{ old('descriptions.'.$code.'.keyword') }}"
                                               class="form-control {{ $code.'__keyword' }}" placeholder="" />
                                    </div>
                                    @if ($errors->has('descriptions.'.$code.'.keyword'))
                                    <span class="help-block">
                                        <i class="fa fa-info-circle"></i> {{ $errors->first('descriptions.'.$code.'.keyword') }}
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
                                    <textarea  id="{{ $code }}__description"
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


                        <div class="form-group {{ $errors->has('logo') ? ' has-error' : '' }}">
                            <label for="logo" class="col-sm-2 control-label">{{ trans('store.logo') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="text" id="logo" name="logo" value="{{ old('logo') }}" class="form-control logo" placeholder=""  />
                                    <div class="input-group-btn">
                                        <a data-input="logo" data-preview="preview_image" data-type="logo" class="btn btn-primary lfm">
                                            <i class="fa fa-image"></i> {{trans('product.admin.choose_image')}}
                                        </a>
                                    </div>
                                </div>
                                @if ($errors->has('logo'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('logo') }}
                                </span>
                                @endif
                                <div id="preview_image" class="img_holder">
                                    @if (old('logo',$store['logo']??''))
                                    <img src="{{ asset(old('logo',$store['logo']??'')) }}">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('phone') ? ' has-error' : '' }}">
                            <label for="phone" class="col-sm-2 control-label">{{ trans('store.phone') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                  <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="" />
                                </div>
                                @if ($errors->has('phone'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('phone') }}
                                </span>
                                @endif
                            </div>
                        </div>


                        <div class="form-group {{ $errors->has('long_phone') ? ' has-error' : '' }}">
                            <label for="long_phone" class="col-sm-2 control-label">{{ trans('store.long_phone') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                   <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" id="long_phone" name="long_phone" value="{{ old('long_phone') }}" class="form-control" placeholder="" />
                                </div>
                                @if ($errors->has('long_phone'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('long_phone') }}
                                </span>
                                @endif
                            </div>
                        </div>


                        <div class="form-group {{ $errors->has('time_active') ? ' has-error' : '' }}">
                            <label for="time_active" class="col-sm-2 control-label">{{ trans('store.time_active') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                   <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" id="time_active" name="time_active" value="{{ old('time_active') }}" class="form-control" placeholder="" />
                                </div>
                                @if ($errors->has('time_active'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('time_active') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('address') ? ' has-error' : '' }}">
                            <label for="address" class="col-sm-2 control-label">{{ trans('store.address') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                   <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" id="address" name="address" value="{{ old('address') }}" class="form-control" placeholder="" />
                                </div>
                                @if ($errors->has('address'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('address') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('office') ? ' has-error' : '' }}">
                            <label for="office" class="col-sm-2 control-label">{{ trans('store.office') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                   <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" id="office" name="office" value="{{ old('office') }}" class="form-control" placeholder="" />
                                </div>
                                @if ($errors->has('office'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('office') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('warehouse') ? ' has-error' : '' }}">
                            <label for="warehouse" class="col-sm-2 control-label">{{ trans('store.warehouse') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" id="warehouse" name="warehouse" value="{{ old('warehouse') }}" class="form-control" placeholder="" />
                                </div>
                                @if ($errors->has('warehouse'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('warehouse') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-sm-2 control-label">{{ trans('store.email') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="" />
                                </div>
                                @if ($errors->has('email'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('email') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('domain') ? ' has-error' : '' }}">
                            <label for="domain" class="col-sm-2 control-label">{{ trans('store.domain') }}</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                                    <input type="text" id="domain" name="domain" value="{{ old('domain') }}" class="form-control" placeholder="{{ trans('store.domain_help') }}" />
                                </div>
                                @if ($errors->has('domain'))
                                <span class="help-block">
                                    <i class="fa fa-info-circle"></i> {{ $errors->first('domain') }}
                                </span>
                                @endif
                            </div>
                        </div>


                        <div class="form-group {{ $errors->has('currency') ? ' has-error' : '' }}">
                            <label class="col-sm-2 control-label">{{ trans('store.currency') }}</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="currency">
                                    @foreach ($currencies as $key => $name)
                                    <option {{ (old('currency') ==  $key)?'selected':'' }} value="{{ $key }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('currency'))
                                <span class="help-block">
                                    {{ $errors->first('currency') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('timezone') ? ' has-error' : '' }}">
                            <label class="col-sm-2 control-label">{{ trans('store.timezone') }}</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="timezone">
                                    @foreach ($timezones as $key => $name)
                                    <option {{ (old('timezone') ==  $key)?'selected':'' }} value="{{ $key }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('timezone'))
                                <span class="help-block">
                                    {{ $errors->first('timezone') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('language') ? ' has-error' : '' }}">
                            <label class="col-sm-2 control-label">{{ trans('store.language') }}</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="language">
                                    @foreach ($languages as $key => $language)
                                    <option {{ (old('language') ==  $language['code'])?'selected':'' }} value="{{ $language['code'] }}">{{ $language['name'] }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('language'))
                                <span class="help-block">
                                    {{ $errors->first('language') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('template') ? ' has-error' : '' }}">
                            <label class="col-sm-2 control-label">{{ trans('store.template') }}</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="template">
                                    @foreach ($templates as $key => $name)
                                    <option {{ (old('template') ==  $key)?'selected':'' }} value="{{ $key }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('template'))
                                <span class="help-block">
                                    {{ $errors->first('template') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group ">
                            <label for="status" class="col-sm-2 control-label">{{ trans('store.status') }}</label>
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
</script>


        <script type="text/javascript">

            $(document).ready(function () {
                $('.select2').select2()
            });

        </script>

        @endpush
