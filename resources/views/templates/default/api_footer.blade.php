<!--Footer-->

<!--Module top footer -->
  @isset ($blocksContent['footer'])
      @foreach ( $blocksContent['footer']  as $layout)
        @php
          $arrPage = explode(',', $layout->page)
        @endphp
        @if ($layout->page == '*' ||  (isset($layout_page) && in_array($layout_page, $arrPage)))
          @if ($layout->type =='html')
            {!! $layout->text !!}
          @elseif($layout->type =='view')
            @if (view()->exists('blockView.'.$layout->text))
             @include('blockView.'.$layout->text)
            @endif
          @elseif($layout->type =='module')
            {!! sc_block_render($layout->text) !!}
          @endif
        @endif
      @endforeach
  @endisset
<!--//Module top footer -->

  <footer id="footer"><!--Footer-->
    <div class="footer-widget">
      <div class="container">
        <div class="row">
          <div class="col-sm-3">
            <div class="single-widget">
              
             
            </div>
          </div>
          

        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <div class="row">
          <p class="pull-left">Copyright © {{date('Y')}} Hihome.app  Inc. All rights reserved.</p>
          <p class="pull-right"></p>
           
        </div>
      </div>
    </div>
  </footer>
<!--//Footer-->
