@extends('emails.layouts.default')

@section('content')

<div style="padding: 50px 30px 0; border-top: 1px solid rgba(0,0,0,0.05);">
    <h1 style="margin-top: 0px;">
        AEPS Fund Credited Successfully
    </h1>
    <div style="color: #636363; font-size: 14px; margin-bottom: 30px">
        Hi {{$data->name}}, you got fresh funds in your <strong>XETTLE</strong> account.
    </div>
    <div style="background-color: #F4F4F4; margin: 20px 0px 20px;position: relative; overflow: auto;">
        <div style="padding: 20px; text-transform: uppercase; color: #8D929D; font-size: 11px; font-weight: bold; letter-spacing: 1px; text-align: center;">
            Summary of your payment
        </div>
        <table style="border-collapse: collapse; width: 100%;">
            <tr>
                <td colspan="2" style="padding: 20px 20px 20px 20px; color: #111; border: 1px solid #e7e7e7; border-left: none; border-right: none;">
                    <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                        Account Number
                    </div>
                    <div style="font-weight: bold;">
                        {{$data->acc_number}}
                    </div>
                </td>
            </tr>
            <tr>
                <td style="padding: 20px 20px 20px 20px; color: #111; width: 50%; ">
                    <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                        Transaction Number
                    </div>
                    <div style="font-weight: bold;">
                        {{$data->ref_number}}
                    </div>
                </td>
            </tr>
            <tr>
                <td style="padding: 20px 20px 20px 20px; color: #111; border: 1px solid #e7e7e7; border-left: none; width: 50%;">
                    <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                        Transfer Date
                    </div>
                    <div style="font-weight: bold;">
                        {{date("d M, Y", strtotime($data->transfer_date))}}
                        <br>
                        {{date("h:i A", strtotime($data->transfer_date))}}
                    </div>
                </td>
                <td colspan="2" style="padding: 20px 20px 20px 20px; color: #111; border: 1px solid #e7e7e7; border-left: none; border-right: none;">
                    <div style="text-transform: uppercase; letter-spacing: 1px; color: #B8B8B8; font-size: 10px; font-weight: bold; margin-bottom: 3px;">
                        Amount Credited
                    </div>
                    <div style="font-weight: bold; color: #04742f;">
                        &#8377; {{$data->amount}}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

@endsection