<script src="{{url('bower_components/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{url('bower_components/popper.js')}}/dist/umd/popper.min.js"></script>
    <script src="{{url('bower_components/moment/moment.js')}}"></script>
    <script src="{{url('bower_components/chart.js')}}/dist/Chart.min.js"></script>
    <script src="{{url('bower_components/select2/dist/js/select2.full.min.js')}}"></script>
    <script src="{{url('bower_components/jquery-bar-rating/dist/jquery.barrating.min.js')}}"></script>
    <script src="{{url('bower_components/ckeditor/ckeditor.js')}}"></script>
    <script src="{{url('bower_components/bootstrap-validator/dist/validator.min.js')}}"></script>
    <script src="{{url('bower_components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
    <script src="{{url('bower_components/ion.rangeSlider/js/ion.rangeSlider.min.js')}}"></script>
    <script src="{{url('bower_components/dropzone/dist/dropzone.js')}}"></script>
   
    <script src="{{url('bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>

    <script src="{{url('bower_components/fullcalendar/dist/fullcalendar.min.js')}}"></script>
    <script src="{{url('bower_components/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js')}}"></script>
    <script src="{{url('bower_components/tether/dist/js/tether.min.js')}}"></script>
    <script src="{{url('bower_components/slick-carousel/slick/slick.min.js')}}"></script>
    <script src="{{url('bower_components/bootstrap/js/dist/util.js')}}"></script>
    <script src="{{url('bower_components/bootstrap/js/dist/alert.js')}}"></script>
    <script src="{{url('bower_components/bootstrap/js/dist/button.js')}}"></script>
    <script src="{{url('bower_components/bootstrap/js/dist/carousel.js')}}"></script>
    <script src="{{url('bower_components/bootstrap/js/dist/collapse.js')}}"></script>
    <script src="{{url('bower_components/bootstrap/js/dist/dropdown.js')}}"></script>
    <script src="{{url('bower_components/bootstrap/js/dist/modal.js')}}"></script>
    <script src="{{url('bower_components/bootstrap/js/dist/tab.js')}}"></script>
    <script src="{{url('bower_components/bootstrap/js/dist/tooltip.js')}}"></script>
    <script src="{{url('bower_components/bootstrap/js/dist/popover.js')}}"></script>
    	
    <script src="{{url('js/demo_customizer.js?version=4.5.0')}}"></script>
    <script src="{{url('js/main.js?version=4.5.0')}}"></script>
    <script src="{{asset('js/toast/demos/js/jquery.toast.js')}}"></script>
		<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<script src="{{asset('js/script.js')}}"></script>
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
      
      ga('create', 'UA-XXXXXXXX-9', 'auto');
      ga('send', 'pageview');
    </script>
    
    @yield('scripts')
