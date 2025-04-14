@extends('emails.layouts.default')

@section('content')

<div style="max-width: 600px; margin: 0px auto; background-color: #fff; box-shadow: 0px 20px 50px rgba(0,0,0,0.05);">
    <div style="padding: 20px 20px; border-top: 1px solid rgba(0,0,0,0.05);">
        <h1 style="margin-top: 0px;">
            Dear {{isset($data['name'])?$data['name']:$name}}
        </h1>
        <div style="color: #636363; font-size: 14px;">
            {!! isset($data['message'])?$data['message']:$message !!}
        </div>
    </div>
</div>


@endsection