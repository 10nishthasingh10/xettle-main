@extends('emails.layouts.default')

@section('content')

<div style="padding: 50px 30px 0; border-top: 1px solid rgba(0,0,0,0.05);">
    <h2 style="margin-top: 0px;">
        AEPS KYC
    </h2>
    <div style="color: #636363; font-size: 14px; margin-bottom: 30px">
        Hi {{isset($data->name) ? $data->name: $name}}
    </div>
    <div style="background-color: #F4F4F4; margin: 20px 0px 40px;position: relative; overflow: auto;">
        <table style="border-collapse: collapse; width: 100%;">
            <tr>
                <td colspan="2" style="padding: 20px 20px 20px 20px; color: #111; border: 1px solid #e7e7e7; border-left: none; border-right: none;">
                    <div style=" letter-spacing: 1px; color: #B8B8B8;  margin-bottom: 3px;">
                        {!! isset($data->message) ? $data->message : 'NA' !!}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

@endsection