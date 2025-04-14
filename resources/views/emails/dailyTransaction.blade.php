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
          Transaction Details
        </h1>
        
        <div style="color: #636363; font-size: 14px; margin-bottom: 30px">
          Dear User,<br />
          You have enrolled for a daily transaction summary. Feel free to reach us if you find any discrepancies.
        </div>
        <div style="background-color: #F4F4F4; margin: 20px 0px 40px;">
          <div style="padding: 20px; text-transform: uppercase; color: #8D929D; font-size: 11px; font-weight: bold; letter-spacing: 1px; text-align: center;">
            transactions Summary : <b>{{$data['date']}}</b>
          </div>
          <table style="border-collapse: collapse; width: 100%;">
            <tr>
              <tr>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;" colspan="2">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;" >
                  
                </div>
                <div style="font-weight: bold; text-align:center;">
                  Total Volume
                </div>
                <div style="font-weight: bold; color: #24b314; text-align:center;font-size: 24px;">
                  {{$data['order']['totalCount'] + $data['stackUpi']['totalCount'] + $data['smartCollect']['totalCount'] + $data['userVan']['totalCount'] + $data['aeps']['totalCount'] + $data['loadMoney']['totalCount'] + $data['autoSettlement']['totalCount'] + $data['recharges']['totalCount'] + $data['dmt']['totalCount']}} | ₹{{number_format($data['order']['totalAmount'] + $data['stackUpi']['totalAmount'] + $data['smartCollect']['totalAmount'] + $data['userVan']['totalAmount'] + $data['aeps']['totalAmount'] + $data['loadMoney']['totalAmount'] + $data['autoSettlement']['totalAmount'] + $data['recharges']['totalAmount'] + $data['dmt']['totalAmount'],2)}}
                </div>
              </td>
              <!-- <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; border-right: none;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  Amount
                </div>
                
              </td> -->
            </tr>
            </tr>
            <tr>
              <tr>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;" colspan="2">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold; text-align:center;">
                  Total Fee & Tax
                </div>
                <div style="font-weight: bold; color: #24b314; text-align:center;font-size: 24px;">
                  {{$data['order']['totalCount'] + $data['StackUpiFeeTax']['totalCount'] + $data['smartCollectFeeTax']['totalCount'] + $data['userVan']['totalCount'] + $data['autoSettlement']['totalCount']}} | ₹{{number_format($data['order']['totalFee'] + $data['StackUpiFeeTax']['totalFee'] + $data['smartCollectFeeTax']['totalFee'] + $data['userVan']['totalFee'] + $data['autoSettlement']['totalFee'],2)}}
                </div>
              </td>
              
            </tr>
            </tr>
            <tr>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  AEPS Amount
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                  {{$data['aeps']['totalCount']}} | ₹{{NumberFormat::init()->change($data['aeps']['totalAmount'],2)}}

                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['aeps']['totalAmount'],2)}}</span>
              </td>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  AEPS Commission
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   {{$data['aeps']['totalCount']}} | ₹{{NumberFormat::init()->change($data['aeps']['totalCommission'],2)}}
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['aeps']['totalCommission'],2)}}</span>
              </td>
              
            </tr>
            <tr>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  Recharge Amount
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                  {{$data['recharges']['totalCount']}} | ₹{{NumberFormat::init()->change($data['recharges']['totalAmount'],2)}}

                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['recharges']['totalAmount'],2)}}</span>
              </td>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  Recharge Commission
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   {{$data['recharges']['totalCount']}} | ₹{{NumberFormat::init()->change($data['recharges']['totalCommission'],2)}}
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['recharges']['totalCommission'],2)}}</span>
              </td>
              
            </tr>
            <tr>
              <td style="padding: 20px 40px; color: #111; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                 Payout Amount
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                  {{$data['order']['totalCount']}} | ₹{{NumberFormat::init()->change($data['order']['totalAmount'],2)}}
                  
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['order']['totalAmount'],2)}}</span>
              </td>
              <td style="padding: 20px 10px 20px 40px; color: #111; border-left: 1px solid #e7e7e7; ">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                 Payout Fee & Tax
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   {{$data['order']['totalCount']}} | ₹{{NumberFormat::init()->change($data['order']['totalFee'],2)}}
                   
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['order']['totalFee'],2)}}</span>
              </td>
              
            </tr>
            <tr>
              <td style="padding: 20px 40px; color: #111; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                 Autosettlement Amount
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                  {{$data['autoSettlement']['totalCount']}} | ₹{{NumberFormat::init()->change($data['autoSettlement']['totalAmount'],2)}}
                  
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['autoSettlement']['totalAmount'],2)}}</span>
              </td>
              <td style="padding: 20px 10px 20px 40px; color: #111; border-left: 1px solid #e7e7e7; ">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                 Autosettlement Fee & Tax
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   {{$data['autoSettlement']['totalCount']}} | ₹{{NumberFormat::init()->change($data['autoSettlement']['totalFee'],2)}}
                   
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['autoSettlement']['totalFee'],2)}}</span>
              </td>
              
            </tr>
            <tr>
              <td style="padding: 20px 40px; color: #111; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                 DMT Amount
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                  {{$data['dmt']['totalCount']}} | ₹{{NumberFormat::init()->change($data['dmt']['totalAmount'],2)}}
                  
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['dmt']['totalAmount'],2)}}</span>
              </td>
              <td style="padding: 20px 10px 20px 40px; color: #111; border-left: 1px solid #e7e7e7; ">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                 DMT Fee & Tax
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   {{$data['dmt']['totalCount']}} | ₹{{NumberFormat::init()->change($data['dmt']['totalFee'],2)}}
                   
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['dmt']['totalFee'],2)}}</span>
              </td>
              
            </tr>
            <tr>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  Load Money Amount
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                  {{$data['loadMoney']['totalCount']}} | ₹{{NumberFormat::init()->change($data['loadMoney']['totalAmount'],2)}}
                  
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['loadMoney']['totalAmount'],2)}}</span>
              </td>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  Load Money Fee & Tax
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   0 | ₹0
                </div>
              </td>
              
            </tr>
            <tr>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  UPI Stack Amount
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   {{$data['stackUpi']['totalCount']}} | ₹{{NumberFormat::init()->change($data['stackUpi']['totalAmount'],2)}}
                   
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['stackUpi']['totalAmount'],2)}}</span>
              </td>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  UPI Stack Fee & Tax
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   {{$data['StackUpiFeeTax']['totalCount']}} | ₹{{NumberFormat::init()->change($data['StackUpiFeeTax']['totalFee'],2)}}
                   
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['StackUpiFeeTax']['totalFee'],2)}}</span>
              </td>
              
            </tr>
            <tr>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  Smart Collect Amount
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   {{$data['smartCollect']['totalCount']}} | ₹{{NumberFormat::init()->change($data['smartCollect']['totalAmount'],2)}}
                   
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['smartCollect']['totalAmount'],2)}}</span>
              </td>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  Smart Collect Fee & Tax
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   {{$data['smartCollectFeeTax']['totalCount']}} | ₹{{NumberFormat::init()->change($data['smartCollectFeeTax']['totalFee'],2)}}
                   
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['smartCollectFeeTax']['totalFee'],2)}}</span>
              </td>
              
            </tr>
            <tr>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  Partner Van Amount
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                  {{$data['userVan']['totalCount']}} | ₹{{NumberFormat::init()->change($data['userVan']['totalAmount'],2)}}
                  
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['userVan']['totalAmount'],2)}}</span>
              </td>
              <td style="padding: 20px 40px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                  
                </div>
                <div style="font-weight: bold;">
                  Partner Van Fee & Tax
                </div>
                <div style="font-weight: bold; color: #047bf8;">
                   {{$data['userVan']['totalCount']}} | ₹{{NumberFormat::init()->change($data['userVan']['totalFee'],2)}}
                   
                </div>
                <span style="font-size: 10px;padding-left: 5%;">₹{{number_format($data['userVan']['totalFee'],2)}}</span>
              </td>
              
            </tr>
            <?php $totalAmount=0;
              //print_r($data);?>
            
            
            
            
            
            
          </table>
          
        </div>
        <h4 style="margin-bottom: 10px;">
          Need Help?
        </h4>
        <div style="color: #A5A5A5; font-size: 12px;">
          <p>
            We are just an email away, reach us at techsupport@mahagram.in</a>
          </p>
        </div>

      </div>
    </div>
  </body>
</html>
