<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XETTLE Email</title>
</head>

<?php echo $__env->yieldContent('style'); ?>

<body style="background-color: #a0a2c1; padding: 20px; font-size: 14px; line-height: 1.43; font-family: 'Helvetica Neue', 'Segoe UI', Helvetica, Arial, sans-serif;">

    <div style="max-width: 660px; margin: 0px auto; background-color: #fff; box-shadow: 0px 20px 20px 0px #767474;">

        <!-- Header -->
        <table style="width: 100%;">
            <tr>
                <td style="background-color: #fff;">
                    <img alt="Logo" src="<?php echo e(secure_url('')); ?>/public/img/xettle_logo_new.png" style="width: 70px; padding: 20px">
                </td>
                <td style="padding-left: 50px; text-align: right; padding-right: 20px;">
                    <a href="<?php echo e(env('APP_USERAPP_URL')); ?>/login" style="color: #261D1D; text-decoration: underline; font-size: 14px; letter-spacing: 1px;">
                        Login
                    </a>
                </td>
            </tr>
        </table>


        <!-- Content -->
        <?php echo $__env->yieldContent('content'); ?>


        <!-- Footer -->
        <div style="padding: 5px 30px 0;">
            <div style="color: #636363; font-size: 14px; margin-bottom: 10px; text-align: center;">
                <div style="font-size: 12px;">
                    Login your Xettle Account to Manage. For additional support please contact us at
                    <a style="text-decoration: none;" href="mailto:help@xettle.io">help@xettle.io</a>
                </div>
            </div>
        </div>

        <div style="background-color: #F5F5F5; padding: 20px 0; text-align: center;">
            <div>
                <div style="color: #A5A5A5; font-size: 10px;">
                    Copyright <?php echo e(date('Y')); ?>. All rights reserved.
                    <br>
                    Do not reply, this is an automatically generated email.
                </div>
            </div>
        </div>

    </div>

</body>

</html><?php /**PATH /home/pgpaysecureco/public_html/resources/views/emails/layouts/default.blade.php ENDPATH**/ ?>