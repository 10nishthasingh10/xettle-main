@extends('layouts.admin.app')
@section('title', $site_title)

@section('style')
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

    .content-box {
        padding: 10px !important;
    }

    .element-box {
        padding: 1.5rem 1rem !important;
    }

    .select2-container {
        width: 100% !important;
    }

    .removeOldRow,
    .remove_new_row {
        padding: 5px !important;
        color: #073253 !important;
    }

    .removeOldRow i,
    .remove_new_row i {
        font-size: 24px !important;
    }


    #addUpdateRulesModalTable tbody td {
        padding: 0 !important;
    }

    #addUpdateRulesModalTable tbody .form-control {
        border-color: transparent;
        border-width: 1px;
    }

    #addUpdateRulesModalTable tbody .form-control:focus {
        border-color: #a7a7a7;
        border-width: 1px;
    }

    #addUpdateRulesModalTable .modal-body {
        max-height: 600px !important;
        overflow-x: auto !important;
    }

    button.btn:disabled {
        cursor: none !important;
        background-color: #adb5bd;
        border-color: #adb5bd;
    }

    .removeNewRulesRow {
        color: #073253 !important;
    }

    .removeNewRulesRow i {
        font-size: 20px !important;
    }

    .table td.fit,
    .table th.fit {
        white-space: nowrap;
        width: 1%;
    }

    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button,
    select {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }

    .w-90px {
        width: 90px !important;
    }

    .disabled .slider {
        cursor: no-drop;
    }
</style>
@endsection

@if($role === 'super-admin')

@section('content')

<div class="content-w">
    <div class="content-box">
        <div class="element-wrapper">

            <div class="element-box">

                <div class="element-actions">
                    <button type="button" class="btn btn-primary" id="addNewSchemeAndRulesBtn">Add New Scheme</button>
                </div>

                <h5 class="element-header">List of Created Schemes</h5>

                <div class="element-content">
                    <div class="row">

                        <div class="col-md-12">

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-hover" id="datatable_schemes_info">
                                    <thead>
                                        <tr>
                                            <th>S.N.</th>
                                            <th>Scheme Name</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th class="fit">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="element-actions">
                                <button class="btn btn-primary" data-target="#assignSchemeModal" data-toggle="modal">Assign Scheme to User</button>
                            </div>

                            <h5 class="element-header">Scheme and User Relations</h5>

                            <form id="searchFormUserSchemeRelation">

                                <fieldset class="form-group">

                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">User:</label>
                                                <select class="form-control select2" name="user_id" id="userSchemeRelationId">
                                                    <option value="">-- Select user --</option>
                                                    @foreach($users as $val)
                                                    <option value="{{$val->id}}">{{$val->name}} - {{$val->email}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Scheme Name:</label>
                                                <select class="form-control select2" name="scheme_id_relation" id="scheme_id_relation">
                                                    <option value="">-- Select --</option>
                                                    @foreach($schemes as $val)
                                                    <option value="{{$val->id}}">{{$val->scheme_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="w-100">&nbsp;</label>
                                                        <button type="submit" class="btn btn-primary w-90px">
                                                            <b><i class="icon-search4"></i></b> Search
                                                        </button>
                                                        <button type="button" class="btn btn-warning btn-labeled legitRipple w-90px" id="resetFormUserSchemeRelation">
                                                            <b><i class="icon-rotate-ccw3"></i></b> Reset
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </fieldset>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-hover" id="datatable_scheme_user_relation">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>User Name</th>
                                            <th>Email</th>
                                            <th>Scheme Name</th>
                                            <th class="fit">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- assign schemes to user -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="assignSchemeModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Assign Scheme to User
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form role="assign-scheme-form" action="{{url('admin/custom-billing/assign-scheme')}}" method="POST">
                    @csrf
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Select User <span class="requiredstar">*</span></label>
                                    <select class="form-control select2" id="user_token" name="user_token">
                                        <option value="">--Select--</option>
                                        @foreach($users as $row)
                                        <option value="{{encrypt($row->id)}}">{{$row->name}} - {{$row->email}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Select Scheme <span class="requiredstar">*</span></label>
                                    <select class="form-control select2" name="scheme_id" id="scheme_id">
                                        <option value="">--Select--</option>
                                        @foreach($schemes as $row)
                                        <option value="{{encrypt($row->id)}}">{{$row->scheme_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        <button class="btn btn-primary" type="submit" data-request="ajax-submit" data-callbackfn="assignSchemeCallback" data-target='[role="assign-scheme-form"]'>Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Add and Update Products -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addUpdateRulesModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="xttl-chart-loader" id="addUpdateProductOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <form role="add-update-product-form" action="#" method="POST">
                    <div class="modal-header">
                        <h6 class="modal-title w-100" id="exampleModalLabel">
                            Add & Update Scheme Rules
                        </h6>
                        <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                    </div>


                    <div class="modal-body">
                        @csrf

                        <div class="row">
                            <div class="col-md-12">Scheme Name: </div>
                            <div class="col-md-6" id="addUpdateRulesName"></div>
                        </div>

                        <div class="table-responsive">

                            <table class="table table-bordered" id="addUpdateRulesModalTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Service</th>
                                        <th scope="col">Product</th>
                                        <th class="fit" scope="col">&nbsp; Fee Type &nbsp;</th>
                                        <th scope="col" class="fit">&nbsp; &nbsp; Status &nbsp; &nbsp;</th>
                                        <th scope="col" class="fit">Start Value</th>
                                        <th scope="col" class="fit">End Value</th>
                                        <th scope="col" class="fit">&nbsp; &nbsp; Fee &nbsp; &nbsp;</th>
                                        <th scope="col" class="fit">Min Fee</th>
                                        <th scope="col" class="fit">Max Fee</th>
                                        <th scope="col" class="fit">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <button type="button" class="btn border add_new_row" id="addNewRulesRow"><i class="os-icon os-icon-plus"></i> Add More Rules</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary" id="btn_addUpdateRulesInput" disabled type="submit" data-request="ajax-submit" data-target='[role="add-update-product-form"]' data-callbackfn="addUpdateRulesCallback">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- View Products -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="viewRulesModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="xttl-chart-loader" id="viewProductOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="modal-header">
                    <h6 class="modal-title w-100" id="exampleModalLabel">
                        Scheme Name: <b id="viewRulesName"></b>
                    </h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>


                <div class="modal-body">
                    @csrf

                    <div class="table-responsive">

                        <table class="table table-bordered" id="viewRulesModalTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" scope="col">Service</th>
                                    <th class="text-center" scope="col">Product</th>
                                    <th class="text-center" scope="col">Fee Type</th>
                                    <th class="text-center" scope="col" class="fit">Status</th>
                                    <th class="text-center" scope="col" class="fit">Start Value</th>
                                    <th class="text-center" scope="col" class="fit">End Value</th>
                                    <th class="text-center" scope="col" class="fit">Fee</th>
                                    <th class="text-center" scope="col" class="fit">Min Fee</th>
                                    <th class="text-center" scope="col" class="fit">Max Fee</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>

                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')

<script src="{{asset('js/handlebars.js')}}"></script>
<script src="{{asset('js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('js/pdfmake.min.js')}}"></script>
<script src="{{asset('js/jszip.min.js')}}"></script>
<script src="{{asset('js/vfs_fonts.js')}}"></script>
<script src="{{asset('js/buttons.html5.min.js')}}"></script>
<script src="{{asset('js/buttons.print.min.js')}}"></script>
<script>
    $(document).ready(function() {

        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });
    });

    var globalServices = JSON.parse('<?php echo str_replace(["'"], '', json_encode($services)); ?>');
    var globalProducts = JSON.parse('<?php echo str_replace(["'"], '', json_encode($products)); ?>');
</script>
<script src="{{asset('admin-js/custom-billing.js?v=1.0.0')}}"></script>

@endsection

@else

@section('content')

<div class="content-w">
    <div class="content-box">
        <div class="element-wrapper">

            <div class="element-box">

                <h5 class="element-header">List of Created Schemes</h5>

                <div class="element-content">
                    <div class="row">

                        <div class="col-md-12">

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-hover" id="datatable_schemes_info">
                                    <thead>
                                        <tr>
                                            <th>S.N.</th>
                                            <th>Scheme Name</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th class="fit">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="col-md-12">

                            <h5 class="element-header">Scheme and User Relations</h5>

                            <form id="searchFormUserSchemeRelation">

                                <fieldset class="form-group">

                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">User:</label>
                                                <select class="form-control select2" name="user_id" id="userSchemeRelationId">
                                                    <option value="">-- Select user --</option>
                                                    @foreach($users as $val)
                                                    <option value="{{$val->id}}">{{$val->name}} - {{$val->email}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">Scheme Name:</label>
                                                <select class="form-control select2" name="scheme_id_relation" id="scheme_id_relation">
                                                    <option value="">-- Select --</option>
                                                    @foreach($schemes as $val)
                                                    <option value="{{$val->id}}">{{$val->scheme_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="w-100">&nbsp;</label>
                                                        <button type="submit" class="btn btn-primary w-90px">
                                                            <b><i class="icon-search4"></i></b> Search
                                                        </button>
                                                        <button type="button" class="btn btn-warning btn-labeled legitRipple w-90px" id="resetFormUserSchemeRelation">
                                                            <b><i class="icon-rotate-ccw3"></i></b> Reset
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </fieldset>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-hover" id="datatable_scheme_user_relation">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>User Name</th>
                                            <th>Email</th>
                                            <th>Scheme Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- View Products -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="viewRulesModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="xttl-chart-loader" id="viewProductOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="modal-header">
                    <h6 class="modal-title w-100" id="exampleModalLabel">
                        Scheme Name: <b id="viewRulesName"></b>
                    </h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>


                <div class="modal-body">
                    @csrf

                    <div class="table-responsive">

                        <table class="table table-bordered" id="viewRulesModalTable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" scope="col">Service</th>
                                    <th class="text-center" scope="col">Product</th>
                                    <th class="text-center" scope="col">Fee Type</th>
                                    <th class="text-center" scope="col" class="fit">Status</th>
                                    <th class="text-center" scope="col" class="fit">Start Value</th>
                                    <th class="text-center" scope="col" class="fit">End Value</th>
                                    <th class="text-center" scope="col" class="fit">Fee</th>
                                    <th class="text-center" scope="col" class="fit">Min Fee</th>
                                    <th class="text-center" scope="col" class="fit">Max Fee</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>

                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" type="button" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')

<script src="{{asset('js/handlebars.js')}}"></script>
<script src="{{asset('js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('js/pdfmake.min.js')}}"></script>
<script src="{{asset('js/jszip.min.js')}}"></script>
<script src="{{asset('js/vfs_fonts.js')}}"></script>
<script src="{{asset('js/buttons.html5.min.js')}}"></script>
<script src="{{asset('js/buttons.print.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });
    });
</script>
<script src="{{asset('admin-js/custom-billing-support.js?v=1.0.0')}}"></script>

@endsection

@endif