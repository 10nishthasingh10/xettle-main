@extends('layouts.loginapp')
@section('title', __('Unauthorized'))
@section('content')
<!--begin::Main-->
<div class="big-error-w">
    <h1>
        401
    </h1>
    <h5>
        {{ __('Unauthorized User') }}
    </h5>
    <h4>
        Oops, Something went missing...
    </h4>
    <form>
        <div class="input-group">
            <div class="input-group-btn">
                <a href="{{isset($url)?$url:url('')}}"><button class="mr-2 mb-2 btn btn-primary mt-2" type="button" style="background: #24b314;border: none;">Go To Home</button></a>
            </div>
        </div>
    </form>
</div>
@endsection