<!DOCTYPE html>
<html>
 <style>img {display:block}</style>
  <body style="background-color: #222533; padding: 20px; font-family: font-size: 14px; line-height: 1.43; font-family: &quot;Helvetica Neue&quot;, &quot;Segoe UI&quot;, Helvetica, Arial, sans-serif;">
    <!-- <div style="max-width: 600px; margin: 10px auto 20px; font-size: 12px; color: #A5A5A5; text-align: center;">
      If you are unable to see this message, <a href="#" style="color: #A5A5A5; text-decoration: underline;">click here to view in browser</a>
    </div> -->
    <div style="max-width: 600px; margin: 0px auto; background-color: #fff; box-shadow: 0px 20px 50px rgba(0,0,0,0.05);">
      <table style="width: 100%;">
        <tr>
          <td style="background-color: #fff;line-height: 1px;">
            <img style="display: block;height: 60px;width: 102px; padding: 20px;" alt="Xettle" title="Xettle" src="{{url('public/img/xettle_logo.png')}}" >
          </td>
          <!-- <td style="padding-left: 50px; text-align: right; padding-right: 20px;">
            <a href="#" style="color: #261D1D; text-decoration: underline; font-size: 14px; letter-spacing: 1px;">Sign In</a><a href="#" style="color: #7C2121; text-decoration: underline; font-size: 14px; margin-left: 20px; letter-spacing: 1px;">Forgot Password</a>
          </td> -->
        </tr>
      </table><div style="padding: 60px 70px; border-top: 1px solid rgba(0,0,0,0.05);">
        <h1 style="margin-top: 0px;">
          UPI Transaction Details
        </h1>
        
        <div style="color: #636363; font-size: 14px; margin-bottom: 30px">
          Hi {{$data->name}} Hope you're doing well. You're enrolled in automatic transaction details, so we'll be showing your daily transactions.
        </div>
        <div style="background-color: #F4F4F4; margin: 20px 0px 40px;">
          <div style="padding: 20px; text-transform: uppercase; color: #8D929D; font-size: 11px; font-weight: bold; letter-spacing: 1px; text-align: center;">
            Summary of your transactions:
          </div>
          <table style="border-collapse: collapse; width: 100%;">
            <tr>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  User Name
                </div>
              </td>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  TXN Note
                </div>
              </td>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; border-right: none;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold; color: #D62525;">
                  Amount
                </div>
              </td>
            </tr>
            <?php $totalAmount=0;?>
            @foreach($upiCallbacks as $val)
            
            <tr>
              <td style="padding: 20px 40px; color: #111; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                 {{$val->payer_acc_name}}
                </div>
              </td>
              <td style="padding: 20px 40px; color: #111; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                 {{$val->txn_note}}
                </div>
              </td>
              <td style="padding: 20px 10px 20px 40px; color: #111; border-left: 1px solid #e7e7e7; ">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  {{$val->amount}}
                </div>
              </td>
            </tr>
            <?php $totalAmount = $totalAmount + $val->amount;?>
            @endforeach
            <tr>
              <td style="padding: 20px 40px; color: #111; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                 Total Amount
                </div>
              </td>
              <td style="padding: 20px 10px 20px 40px; color: #111; border-left: 1px solid #e7e7e7; ">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  {{$totalAmount}}
                </div>
              </td>
            </tr>
          </table>
          <div style="color: #B8B8B8; font-size: 12px; padding: 30px; border-top: 1px solid #e7e7e7;">
            Did you know that you can manage your payments online? Make an extra payment, pay off your loan, or change your due date right from your account. Simply log in to your Account Summary and click Manage Payments or <a href="mailto:help@xettle.io">contact us</a> for additional support.
          </div>
        </div>
        <h4 style="margin-bottom: 10px;">
          Need Help?
        </h4>
        <div style="color: #A5A5A5; font-size: 12px;">
          <p>
            If you have any questions you can simply reply to this email or find our contact information below. Also contact us at <a href="mailto:help@xettle.io" style="text-decoration: underline; color: #4B72FA;">help@xettle.io</a>
          </p>
        </div>
      </div><div style="background-color: #F5F5F5; padding: 40px; text-align: center;">
        <div style="margin-bottom: 20px;">
          <a href="#" style="display: inline-block; margin: 0px 10px;"><img alt="" src="{{url('img/social-icons/twitter.png')}}" style="height: 28px;width: 28px;"></a><a href="#" style="display: inline-block; margin: 0px 10px;"><img alt="" src="{{url('img/social-icons/facebook.png')}}" style="height: 28px;width: 28px;"></a><a href="#" style="display: inline-block; margin: 0px 10px;"><img alt="" src="{{url('img/social-icons/linkedin.png')}}" style="height: 28px;height: 28px;width: 28px;"></a><a href="#" style="display: inline-block; margin: 0px 10px;"><img alt="" src="{{url('img/social-icons/instagram.png')}}" style="height: 28px;width: 28px;"></a>
        </div>
        <div style="margin-bottom: 20px;">
          <!-- <a href="#" style="text-decoration: underline; font-size: 14px; letter-spacing: 1px; margin: 0px 15px; color: #261D1D;">Contact Us</a><a href="#" style="text-decoration: underline; font-size: 14px; letter-spacing: 1px; margin: 0px 15px; color: #261D1D;">Privacy Policy</a><a href="#" style="text-decoration: underline; font-size: 14px; letter-spacing: 1px; margin: 0px 15px; color: #261D1D;">Unsubscribe</a> -->
        </div>
        <div style="color: #A5A5A5; font-size: 12px; margin-bottom: 20px; padding: 0px 50px;">
          You are receiving this email because you signed up for Xettle.
        </div>
        <!-- <div style="margin-bottom: 20px;">
          <a href="#" style="display: inline-block; margin: 0px 10px;"><img alt="" src="{{url('img/market-google-play.png')}}" style="width: 133px;height: 33px;"></a><a href="#" style="display: inline-block; margin: 0px 10px;"><img alt="" src="{{url('img/market-ios.png')}}" style="width: 99px;height: 33px;"></a>
        </div> -->
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.05);">
          <div style="color: #A5A5A5; font-size: 10px; margin-bottom: 5px;">
            1073 Madison Ave, suite 649, New York, NY 10001
          </div>
          <div style="color: #A5A5A5; font-size: 10px;">
            Copyright 2021 Xettle.io. All rights reserved.
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
