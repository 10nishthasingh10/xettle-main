<!DOCTYPE html>
<html>
  <head>
    <title>Xettle Technologies <?php echo $__env->yieldContent('title'); ?></title>
	<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta charset="utf-8">
    <meta content="Xettle App" name="description">
    <link href="<?php echo e(url('img/favicon.ico')); ?>" rel="shortcut icon">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet" type="text/css">
    <link href="<?php echo e(url('bower_components/select2/dist/css/select2.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('bower_components/bootstrap-daterangepicker/daterangepicker.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('bower_components/dropzone/dist/dropzone.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('bower_components/fullcalendar/dist/fullcalendar.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('bower_components/perfect-scrollbar/css/perfect-scrollbar.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('bower_components/slick-carousel/slick/slick.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('css/main.css?version=4.5.0')); ?>" rel="stylesheet">
    <script src="<?php echo e(url('bower_components/jquery/dist/jquery.min.js')); ?>"></script>
    <style type="text/css">
    .help-block {
      color: red;
    }
    .deliveryreponse{
      color: green;
    }
    .deliveryreponseOtp{
      color: green;
    }
    .has-error{
      color:red;
    }
  </style>
  </head>
  <body class="auth-wrapper">
    <div class="all-wrapper menu-side with-pattern"><!--begin::Main-->
        <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
  </body>
</html><?php /**PATH C:\xampp\htdocs\xettle\resources\views/layouts/loginapp.blade.php ENDPATH**/ ?>