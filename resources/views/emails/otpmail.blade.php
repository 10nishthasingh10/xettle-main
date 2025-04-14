@extends('emails.layouts.default')

@section('content')

<div style="padding: 50px 30px 0; border-top: 1px solid rgba(0,0,0,0.05);">
    <h2 style="margin-top: 0px;">
        OTP Requested
    </h2>
    <div style="color: #636363; font-size: 14px; margin-bottom: 30px">
        Hi {{$user->name}}
    </div>
    <div style="background-color: #F4F4F4; margin: 20px 0px 40px;position: relative; overflow: auto;">
        <table style="border-collapse: collapse; width: 100%;">
            <tr>
                <td colspan="2" style="padding: 20px 20px 20px 20px; color: #111; border-left: none; border-right: none;">
                    <div style="text-align: center; text-transform: uppercase; letter-spacing: 1px; color: #111; font-size: 22px; font-weight: bold; margin-bottom: 10px;">
                        OTP
                    </div>
                    <div style="font-weight: bold; text-align: center;">
                        @foreach(str_split($otp) as $num)
                        <span style="min-width: 21px; background-color: #0a7cf8; color: #FFF; display: inline-block; font-size: 32px; padding: 0px 10px; border-radius: 5px; border: 1px solid #FFF; margin-right: 2px;">
                            {{$num}}
                        </span>
                        @endforeach
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 21px 20px 7px 20px; color: #767676; font-size: 12px; margin-bottom: 10px; text-align: center;">
                    If you did not make this request then please reset your password.
                </td>
            </tr>
        </table>
    </div>
</div>

@endsection