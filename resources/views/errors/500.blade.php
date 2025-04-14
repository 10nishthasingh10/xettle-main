@extends('layouts.loginapp')
@section('title', "500 - Internal Server Error")
@section('content')
<!--begin::Main-->
<div class="big-error-w">
    <h1>
        500
    </h1>
    <h5>
        @if(!empty($title))
        {{$title}}
        @else
        Internal Server Error
        @endif
    </h5>
    <h4>
        @if(!empty($message))
        {{$message}}
        @else
        Oops, Something went missing...
        @endif
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