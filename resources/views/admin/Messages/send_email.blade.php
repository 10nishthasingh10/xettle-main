@extends('layouts.admin.app')
@section('title',ucfirst($page_title))
@section('content')
@section('style')
<link href="{{url('public/css/buttons.dataTables.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
<style type="text/css">
    .expandtable {
        width: 100% !important;
        margin-bottom: 1rem;
    }

    .expandtable,
    tbody,
    tr,
    td {
        margin-bottom: 1rem;
    }

    @media screen and (min-width: 767px) {
        #datatable_length {
            margin-top: 0;
        }
    }

    .content-box {
        padding: 8px !important;
    }

    .element-box {
        padding: 1.5rem 0.8rem !important;
    }
</style>
<div class="content-w">
    <div class="content-box custom-content-box">
        <div class="element-wrapper">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="form-header">
                        {{$page_title}}
                    </h5>
                </div>
                
            </div>
            </div>
        </div>
    </div>
    @section('scripts')
    @endsection
@endsection