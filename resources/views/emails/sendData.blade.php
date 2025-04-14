
<!DOCTYPE html>
<html>
<head>
    <title>Excel Export Data</title>
</head>
<body style="background-color: #a0a2c1; padding: 20px; font-size: 14px; line-height: 1.43; font-family: 'Helvetica Neue', 'Segoe UI', Helvetica, Arial, sans-serif;">
    <div style="max-width: 660px; margin: 0px auto; background-color: #fff; box-shadow: 0px 20px 20px 0px #767474;">

        <table style="width: 100%;">
            <tr>
                <td style="background-color: #fff;">
                    <img alt="" src="{{secure_url('')}}/public/img/xettle_logo.png" style="width: 70px; padding: 20px">
                </td>
                <td style="padding-left: 50px; text-align: right; padding-right: 20px;">
                    <a href="{{secure_url('')}}/login" style="color: #261D1D; text-decoration: underline; font-size: 14px; letter-spacing: 1px;">
                        Login
                    </a>
                </td>
            </tr>
        </table>

        <div style="padding: 50px 30px 0; border-top: 1px solid rgba(0,0,0,0.05);">
            <h2 style="margin-top: 0px;">
            {{$table}} Data
            </h2>
            <div style="color: #636363; font-size: 14px; margin-bottom: 30px">
			{{$data}}
            </div>
            <div style="background-color: #F4F4F4; margin: 20px 0px 40px;position: relative; overflow: auto;">
                <table style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td colspan="2" style="padding: 20px 20px 20px 20px; color: #111; border: 1px solid #e7e7e7; border-left: none; border-right: none;">
                            <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                                Date
                            </div>
                            <div style="font-weight: bold;">
                                {{$date}}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
		 
        </div>
		<div style=" padding-left: 30px; padding-right: 30px;">
			<p>	If you have not made this request, please report to the xettle security team: help@xettle.io</p>
		</div>
        <div style="background-color: #F5F5F5; padding: 20px 0; text-align: center;">
            <div>
                <div style="color: #A5A5A5; font-size: 10px;">
                    Copyright {{date('Y')}}. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>

</html>