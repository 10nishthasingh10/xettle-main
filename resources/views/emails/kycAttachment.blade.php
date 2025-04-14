@extends('emails.layouts.default')

@section('content')

<div style="padding: 50px 30px 0; border-top: 1px solid rgba(0,0,0,0.05);">
    <h2 style="margin-top: 0px;">
        AEPS KYC Attachments
    </h2>
    <div style="color: #636363; font-size: 14px; margin-bottom: 30px">
        Hi Admin
    </div>
    <div style="margin: 20px 0px 40px;position: relative; overflow: auto;">
        <table style="border-collapse: collapse; width: 100%;">
            <tr>
                <td style="padding: 20px 20px 20px 20px; color: #111; border: 1px solid #e7e7e7; border-left: none; border-right: none;">
                    <div style=" letter-spacing: 1px; color: #B8B8B8;  margin-bottom: 3px;">
                        {!! isset($data->message) ? $data->message : 'NA' !!}
                    </div>

                </td>
            </tr>
            <tr>
                <td>
                    <ul>
                        <li> Mobile number - <b> {{$data->mobile}} </b></li>
                        <li> Email ID - <b> {{$data->email_id}} </b></li>
                        <li> MID - <b> {{$data->mid}} </b></li>
                        <li> First Name - <b> {{$data->first_name}} </b></li>
                        <li> Middle Name - <b> {{$data->middle_name}} </b></li>
                        <li> Last Name - <b> {{$data->last_name}} </b></li>
                        <li> MC id - <b> {{$data->merchant_code}} </b></li>
                        <li> Company Name - <b> {{$data->business_name}} </b></li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>

</div>

@endsection