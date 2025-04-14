@extends('emails.layouts.default')

@section('content')

<div style="padding: 20px 30px 0; border-top: 1px solid rgba(0,0,0,0.05);">

    <div style="text-align: center;">
        <h1 style="margin-top: 0;color: #6c6c6c;">
            Welcome to XETTLE
        </h1>
        <div style="color: #636363; font-size: 14px; margin-bottom: 30px">
            Hi {{$data->name}}, We are delighted to have you with us
        </div>
    </div>

    <div style="border-radius: 10px; background-color: #F4F4F4; margin: 20px 0px 20px; padding: 20px 0; position: relative; overflow: auto;">

        <div style="margin: 10px 0 ;text-align: center; width: 100%; height: 196px;">
            <img src="{{custom_secure_url('')}}/public/img/welcome.png" style="height: 100%;" alt="welcome logo" />
        </div>

        <div style="text-align: center; padding: 15px 20px 0;color: #6c6c6c;">
            XETTLE products empower fintech businesses. We are here to provide sustainable solutions for
            lifting up your business.
            <div>Let's start now!</div>
        </div>

        <div style="color: #6c6c6c; text-align:justify; padding: 0 15px;">
            <ul>
                <li>Validate your email.</li>
                <li>Login your XETTLE account.</li>
                <li>Setup your profile.</li>
                <li>If have queries, get in touch with account manager.</li>
            </ul>
        </div>

    </div>
</div>

@endsection