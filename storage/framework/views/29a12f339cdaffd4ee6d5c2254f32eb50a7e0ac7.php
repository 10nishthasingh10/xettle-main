<?php $__env->startSection('title', $site_title); ?>

<?php $__env->startSection('style'); ?>
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

    .removeNewProductRow,
    .removeNewProductFeeRow,
    .remove_new_row {
        /* padding: 5px !important; */
        color: #073253 !important;
    }

    .removeNewProductRow i,
    .removeNewProductFeeRow i,
    .remove_new_row i {
        font-size: 20px !important;
    }

    .table td.fit,
    .table th.fit {
        white-space: nowrap;
        width: 1%;
    }

    .add_new_row:hover {
        /* font-weight: 600; */
        box-shadow: 0px 4px 5px 0px grey;
    }

    #modal-product-table tbody td,
    #modal-product-fee-table tbody td {
        padding: 0 !important;
    }

    #modal-product-table tbody .form-control,
    #modal-product-fee-table tbody .form-control {
        border-color: transparent;
        border-width: 1px;
    }

    #modal-product-table tbody .form-control:focus,
    #modal-product-fee-table tbody .form-control:focus {
        border-color: #a7a7a7;
        border-width: 1px;
    }

    #addUpdateProductsModal .modal-body,
    #addUpdateProductFeeModal .modal-body {
        max-height: 600px !important;
        overflow-x: auto !important;
    }

    #addUpdateProductFeeModal th.fee-type {
        min-width: 160px;
    }

    #addUpdateProductFeeModal .form-control-radio {
        margin-left: 10px;
    }

    #addUpdateProductFeeModal label {
        cursor: pointer;
    }

    button.btn:disabled {
        cursor: none !important;
        background-color: #adb5bd;
        border-color: #adb5bd;
    }

    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if($role === 'super-admin'): ?>

<div class="content-w">
    <div class="content-box">
        <div class="element-wrapper">

            <div class="row">
                <div class="col-md-8">
                    <h5 class="form-header">
                        <?php echo e($page_title); ?>

                    </h5>
                </div>

                <div class="col-md-4 text-right">
                    <div class="bold-label text-primary">
                        <div class="value font-1-5">
                            <button class="btn btn-success" data-target="#addNewServiceModal" data-toggle="modal">Add New Service</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="xttl-chart-loader d-none" id="serviceListOverlay">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>

                            <h5 class="mb-4">Services List</h5>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-hover" id="dt-service-list">
                                    <thead>
                                        <tr>
                                            <th>S.N.</th>
                                            <th>Service Name</th>
                                            <th>Show at Dropdown</th>
                                            <th>Activation Allowed</th>
                                            <th>Created At</th>
                                            <th class="fit">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
                            <h5>Scheme Fee Info</h5>

                            <form id="searchForm_2">

                                <fieldset class="form-group">

                                    <div class="row">


                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="">Service Name:</label>
                                                <select class="form-control select2" name="service_id" id="serviceSchemeFeeInfo">
                                                    <option value="all">All</option>
                                                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($val->service_id); ?>"><?php echo e($val->service_name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="w-100">&nbsp;</label>
                                                        <button type="submit" class="btn btn-primary" id="searching_2" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
                                                            <b><i class="icon-search4"></i></b> Search
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </fieldset>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-hover d-none" id="datatable_scheme_info">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Service Name</th>
                                            <th>product Name</th>
                                            <th>Start Value</th>
                                            <th>End Value</th>
                                            <th>Fee</th>
                                            <th>Min Fee</th>
                                            <th>Max Fee</th>
                                            <th>Type</th>
                                            <th>Created At</th>
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


    <!-- add new Service -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addNewServiceModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Add New Global Service
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form role="add-new-service-form" action="<?php echo e(url('admin/global-billing/service/add')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Service Name <span class="requiredstar">*</span></label>
                                    <input type="text" class="form-control addServiceInput" name="service_name" placeholder="Enter service name" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Service Slug <span class="requiredstar">*</span></label>
                                    <input type="text" class="form-control addServiceInput" id="service_slug" name="service_slug" placeholder="Enter service slug" required>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="formBtnAddNewService" class="btn btn-primary" disabled type="submit" data-request="ajax-submit" data-target='[role="add-new-service-form"]' data-callbackfn="addNewServiceCb">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Update Service Info -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="updateServiceModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Update Global Service
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form role="update-service-form" action="<?php echo e(url('admin/global-billing/service/update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="row_id" id="update_row_id">
                    <div class="modal-body">


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Service Name <span class="requiredstar">*</span></label>
                                    <input type="text" class="form-control updateServiceInput" name="service_name" id="update_service_name" placeholder="Enter service name" required>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit" id="formBtnUpdateService" disabled data-request="ajax-submit" data-target='[role="update-service-form"]' data-callbackfn="updateServiceCb">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Add and Update Products -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addUpdateProductsModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="xttl-chart-loader" id="addUpdateProductOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Service: <b id="modal-service-name"></b>
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form role="add-update-product-form" action="<?php echo e(url('admin/global-billing/products')); ?>" method="POST">

                    <div class="modal-body">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="service_id" id="modal_service_id">

                        <div class="table-responsive">

                            <table class="table table-bordered" id="modal-product-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Product Slug</th>
                                        <th scope="col" class="fit">Min Order Value</th>
                                        <th scope="col" class="fit">Max Order Value</th>
                                        <th scope="col" class="fit">Tax Value</th>
                                        <th scope="col" class="fit">Status</th>
                                        <th scope="col" class="fit">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <button type="button" class="btn border add_new_row" id="add_new_product_row"><i class="os-icon os-icon-plus"></i> Add More Product</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary" id="formBtnUpdateProduct" disabled type="submit" data-request="ajax-submit" data-target='[role="add-update-product-form"]' data-callbackfn="updateProductListCb">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- View Products List -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="viewProductListModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Service: <b id="modal-view-service-name"></b>
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>

                <div class="modal-body">

                    <div class="table-responsive">

                        <table class="table table-bordered" id="modal-view-product-table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Product Name</th>
                                    <th scope="col">Product Slug</th>
                                    <th scope="col" class="fit">Min Order Value</th>
                                    <th scope="col" class="fit">Max Order Value</th>
                                    <th scope="col" class="fit">Tax Value(%)</th>
                                    <th scope="col" class="fit">Status</th>
                                    <th scope="col">Created Date</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" type="button" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

    <!-- add and update Product Fee -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addUpdateProductFeeModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="xttl-chart-loader" id="addUpdateProductFeeOverlay">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <b id="modal-product-name"></b>
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form role="add-update-product-fee-form" action="<?php echo e(url('admin/global-billing/product-fee')); ?>" method="POST">

                    <div class="modal-body">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="product_id" id="modal_product_id">

                        <div class="table-responsive">

                            <table class="table table-bordered" id="modal-product-fee-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col" class="fee-type">Fee Type</th>
                                        <th scope="col">Start Value</th>
                                        <th scope="col">End Value</th>
                                        <th scope="col">Fee</th>
                                        <th scope="col">Min Fee</th>
                                        <th scope="col">Max Fee</th>
                                        <th scope="col" class="fit">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <button type="button" class="btn border add_new_row" id="add_new_product_fee_row"><i class="os-icon os-icon-plus"></i> Add More Fee</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary" id="formBtnUpdateProductFee" disabled type="submit" data-request="ajax-submit" data-target='[role="add-update-product-fee-form"]' data-callbackfn="updateProductFeeListCb">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<?php else: ?>

<div class="content-w">
    <div class="content-box">
        <div class="element-wrapper">

            <div class="row">
                <div class="col-md-12">
                    <h5 class="form-header">
                        <?php echo e($page_title); ?>

                    </h5>
                </div>
            </div>

            <div class="element-box">
                <div class="element-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="xttl-chart-loader d-none" id="serviceListOverlay">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>

                            <h5 class="mb-4">Services List</h5>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-hover" id="dt-service-list">
                                    <thead>
                                        <tr>
                                            <th>S.N.</th>
                                            <th>Service Name</th>
                                            <th>Status</th>
                                            <th>Activation Allowed</th>
                                            <th>Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
                            <h5>Scheme Fee Info</h5>

                            <form id="searchForm_2">

                                <fieldset class="form-group">

                                    <div class="row">


                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="">Service Name:</label>
                                                <select class="form-control select2" name="service_id" id="serviceSchemeFeeInfo">
                                                    <option value="all">All</option>
                                                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($val->service_id); ?>"><?php echo e($val->service_name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="w-100">&nbsp;</label>
                                                        <button type="submit" class="btn btn-primary" id="searching_2" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
                                                            <b><i class="icon-search4"></i></b> Search
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </fieldset>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped table-hover d-none" id="datatable_scheme_info">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Service Name</th>
                                            <th>product Name</th>
                                            <th>Start Value</th>
                                            <th>End Value</th>
                                            <th>Fee</th>
                                            <th>Min Fee</th>
                                            <th>Max Fee</th>
                                            <th>Type</th>
                                            <th>Created At</th>
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

</div>


<!-- View Products List -->
<div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="viewProductListModal" role="dialog" tabindex="-1">

    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Service: <b id="modal-view-service-name"></b>
                </h5>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">

                    <table class="table table-bordered" id="modal-view-product-table">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Product Name</th>
                                <th scope="col" class="fit">Min Order Value</th>
                                <th scope="col" class="fit">Max Order Value</th>
                                <th scope="col" class="fit">Tax Value(%)</th>
                                <th scope="col" class="fit">Status</th>
                                <th scope="col" class="text-center">Created Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>

<script src="<?php echo e(asset('js/handlebars.js')); ?>"></script>
<script src="<?php echo e(asset('js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(asset('js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/buttons.print.min.js')); ?>"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });
    });
</script>

<?php if($role === 'super-admin'): ?>
<script src="<?php echo e(asset('admin-js/global-billing.js')); ?>"></script>
<?php else: ?>
<script src="<?php echo e(asset('admin-js/global-billing-support.js')); ?>"></script>
<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/admin/global_billing.blade.php ENDPATH**/ ?>