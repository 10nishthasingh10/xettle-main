@extends('emails.layouts.default')

@section('content')

<div style="padding: 50px 30px; border-top: 1px solid rgba(0,0,0,0.05);">
    <h1 style="margin-top: 0px;">
        Hi, Admin </br></br>
    </h1>


    <div style="background-color: #F4F4F4; margin: 20px 0px 40px;">
        <div style="padding: 7px; text-transform: uppercase; color: #8D929D; font-size: 11px; font-weight: bold; letter-spacing: 1px; text-align: center;">
            User Account Details
        </div>
        <table style="border-collapse: collapse; width: 100%;">
            <tr>
                <td style="padding: 7px 10px; color: #111; width: 70%;">
                    Name : <b>{{$data->name}}</b>
                </td>
            </tr>
            <tr>
                <td style="padding: 7px 10px; color: #111; width: 70%;">
                    Business Name : <b>{{isset($data->businessName) ? $data->businessName : "NA"}}</b>
                </td>
            </tr>
            <tr>
                <td style="padding: 7px 10px; color: #111; width: 70%;">
                    E-mail : <b>{{$data->userEmail}}</b>
                </td>
            </tr>
            <tr>
                <td style="padding: 7px 10px; color: #111; width: 70%;">
                    Account Status : <b> {{$data->message}} </b>
                </td>
            </tr>
            <tr>
                <td style="padding: 7px 10px; color: #111; width: 70%;">
                    Reason : <b> {{$data->reason}} </b>
                </td>
            </tr>
            <tr>
                <td style="padding: 7px 10px; color: #111; width: 70%;">
                    Primary : <b> ₹ {{$data->primaryAccountBalance}} </b>
                </td>
            </tr>
            <tr>
                <td style="padding: 7px 10px; color: #111; width: 70%;">
                    Payout : <b>₹ {{$data->payoutAccountBalance}}</b>
                </td>
            </tr>
        </table>
    </div>
</div>

@endsection