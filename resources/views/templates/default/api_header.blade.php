  <header id="header"><!--header-->
    <div class="header_top"><!--header_top-->
      <div class="container">
        <div class="row">
          <div class="col-sm-6">
            <div class="contactinfo">
             
            </div>
          </div>
          <div class="col-sm-6">
            <div class="btn-group pull-right">
              <div class="btn-group locale">
                @if (count($languages)>1)
                <button type="button" class="btn btn-default dropdown-toggle usa" data-toggle="dropdown"><img src="{{ asset($languages[app()->getLocale()]['icon']) }}" style="height: 25px;">
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  @foreach ($languages as $key => $language)
                    <li><a href="{{ url('locale/'.$key) }}"><img src="{{ asset($language['icon']) }}" style="height: 25px;"></a></li>
                  @endforeach
                </ul>
                @endif
              </div>
              @if (count($currencies)>1)
               <div class="btn-group locale">
                <button type="button" class="btn btn-default dropdown-toggle usa" data-toggle="dropdown">
                  HiHome.app
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  @foreach ($currencies as $key => $currency)
                    <li><a href="{{ url('currency/'.$currency->code) }}">{{ $currency->name }}</a></li>
                  @endforeach
                </ul>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div><!--/header_top-->
    <div class="header-middle"><!--header-middle-->
      <div class="container">
        <div class="row">
          <div class="col-sm-4">
            <div class="logo pull-left">
              <a href="{{ route('home') }}"><img style="width: 150px;" src="{{ asset('data/logo/hihome.jpg') }}" alt="" /></a>
            </div>
          </div>
          <div class="col-sm-8">
            
          </div>
        </div>
      </div>
    </div><!--/header-middle-->

    <div class="header-bottom"><!--header-bottom-->
      <div class="container">
        <div class="row">
          
        </div>
      </div>
    </div><!--/header-bottom-->
  </header><!--/header-->
