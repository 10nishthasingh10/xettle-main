<head>
    <title>@yield('title')</title>
    <meta charset="utf-8">
    <meta content="ie=edge" http-equiv="x-ua-compatible">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
    <link href="{{url('bower_components/select2/dist/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{url('bower_components/bootstrap-daterangepicker/daterangepicker.css')}}" rel="stylesheet">
    <link href="{{url('bower_components/dropzone/dist/dropzone.css')}}" rel="stylesheet">
    <link href="{{url('bower_components/fullcalendar/dist/fullcalendar.min.css')}}" rel="stylesheet">
    <link href="{{url('bower_components/perfect-scrollbar/css/perfect-scrollbar.min.css')}}" rel="stylesheet">
    <link href="{{url('bower_components/slick-carousel/slick/slick.css')}}" rel="stylesheet">
    <link href="{{url('css/main.css?version=4.5.0')}}" rel="stylesheet">
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.0.18/sweetalert2.min.css" integrity="sha512-riZwnB8ebhwOVAUlYoILfran/fH0deyunXyJZ+yJGDyU0Y8gsDGtPHn1eh276aNADKgFERecHecJgkzcE9J3Lg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style type="text/css">
    .requiredstar{
      color:red;
    }
    .help-block{
      color:red;
    }
    td.details-control {
			background: url("{{custom_secure_url('')}}/public/images/details_open.png") no-repeat center center;
			cursor: pointer;
		}
		tr.shown td.details-control {
			background: url("{{custom_secure_url('')}}/public/images/details_close.png") no-repeat center center;
		}
    </style>
    <style>
  .content-w{
    min-height: 950px !important;
  }
  </style>
    @yield('style')
  </head>