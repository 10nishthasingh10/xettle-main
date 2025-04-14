<?php $__env->startSection('title', $site_title); ?>
<?php $__env->startSection('content'); ?>
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
        padding: 1rem !important;
    }

    .cc-fee-tax .element-box {
        margin-bottom: 0.8rem !important;
    }
</style>
<div class="content-w">
    <div class="content-box">
        <div class="element-wrapper">

            <div class="element-box">
                <h5 class="form-header">
                    <?php echo e($page_title); ?>

                </h5>
                <form id="searchForm">
                    <fieldset class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" value="<?php echo e($dateFrom); ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" value="<?php echo e($dateTo); ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">User <span class="requiredstar"></span></label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        <?php $__currentLoopData = $userData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($val->id); ?>"><?php echo e($val->userName); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-90px" id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
                                                Search
                                            </button>
                                            <button type="button" class="btn btn-warning btn-labeled legitRipple" id="formReset" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Reset">
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="element-content">
                  <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row">
                                <div class="col-sm-6">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-danger" href="#">
                                        <div class="value font-1-5" id="tpv"></div>
                                        <div class="label text-dark">Total Volume</div>
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="#">
                                        <div class="value font-1-5" id="tft"></div>
                                        <div class="label text-dark">Total Fee & Tax</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row mb-xl-2 mb-xxl-3">
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="<?php echo e(url('admin/orders')); ?>">
                                        <div class="value font-1-1" id="smartPayoutTotal"></div>
                                        <div class="label">Smart Payout</div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="<?php echo e(url('admin/upiCallback')); ?>">
                                        <div class="value font-1-1" id="upiStackTotal"></div>
                                        <div class="label">UPI Stack</div>
                                    </a>
                                </div>

                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="<?php echo e(url('admin/van-callback')); ?>">
                                        <div class="value font-1-1" id="partnerVANTotal"></div>
                                        <div class="label">Partner's VAN</div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-success" href="<?php echo e(url('admin/smart-collect/callbacks')); ?>">
                                        <div class="value font-1-1" id="smartCollectTotal"></div>
                                        <div class="label">Smart Collect</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                        <div class="tablos">
                            <div class="row mb-xl-2 mb-xxl-3">
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="#">
                                        <div class="value font-1-1" id="smartPayoutFeeAndTaxTotal"></div>
                                        <div class="label">Smart Payouts Fee & Tax</div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="#">
                                        <div class="value font-1-1" id="upiStackFeeAndTaxTotal"></div>
                                        <div class="label">UPI Stack Fee & Tax</div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="#">
                                        <div class="value font-1-1" id="partnerVANFeeAndTax"></div>
                                        <div class="label">Partner's VAN Fee & Tax</div>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="#">
                                        <div class="value font-1-1" id="smartCollectFeeAndTax"></div>
                                        <div class="label">Smart Collect Fee & Tax</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-xxl-12">
                     <div class="tablos">
                            <div class="row">
                                <div class="col-sm-6">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-danger" href="<?php echo e(url('admin/reports/aeps')); ?>">
                                        <div class="value font-1-5" id="aepsTotal"></div>
                                        <div class="label text-dark">AEPS (Total Volume)</div>
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a class="element-box el-tablo centered trend-in-corner bold-label text-primary" href="<?php echo e(url('admin/reports/aeps')); ?>">
                                        <div class="value font-1-5" id="aepsTotalCommission"></div>
                                        <div class="label text-dark">AEPS (Total Commission)</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>

<script src="<?php echo e(url('public/js/handlebars.js')); ?>"></script>
<script src="<?php echo e(url('public/js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            containerCssClass: "xettle-select2"
        });
    });

    $('form#searchForm').submit(function() {
        $('#searchForm').find('button:submit').button('loading');
        $
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        getPayoutRecords(from, to);
       // getAepsRecords(from, to);
        return false;
    });
    var from = $(this).find('input[name="from"]').val();
    var to = $(this).find('input[name="to"]').val();
    getPayoutRecords(from, to);
   // getAepsRecords(from, to);

    var totalVolumeCount = 0;
    var totalVolumeAmount = 0;

    var totalTaxFeeCount = 0;
    var totalFeeAmount = 0;
    var totalTaxAmount = 0;
    var isAjax = false;

    $('#formReset').click(function() {
        $('form#searchForm')[0].reset();
        $('#formReset').button('loading');
        $(this).find('select[name="user_id"]').val(null);
        $(".select2").val(null).trigger('change');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        getPayoutRecords(from, to);
        getAepsRecords(from, to);
    });

    function getPayoutRecords(from, to) {

        if (!isAjax) {

            $('#searching').html('Searching');

            totalVolumeCount = 0;
            totalVolumeAmount = 0;
            totalTaxFeeCount = 0;
            totalFeeAmount = 0;
            totalTaxAmount = 0;

            isAjax = true;

            $.ajax({
                url: "<?php echo e(secure_url('admin/allreports/payout')); ?>",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    from: from,
                    to: to,
                    user_id: $('#searchForm').find('[name="user_id"]').val()
                },
                success: (response) => {
                    totalVolumeCount += response.success ? (response.success.totalCount) : 0;
                    totalVolumeAmount += (response.success.totalAmount != null) ? (response.success.totalAmount) : 0;

                    totalTaxFeeCount += response.success ? (response.success.totalCount) : 0;
                    totalFeeAmount += (response.success.totalFee != null) ? (response.success.totalFee) : 0;
                    totalTaxAmount += (response.success.totalTax != null) ? (response.success.totalTax) : 0;

                    if (response.success.totalAmount != null)
                        $('#smartPayoutTotal').html(`${response.success.totalCount} | ${setTooltipAndvalue(response.success.totalAmount)}`);
                    else
                        $('#smartPayoutTotal').html(`${response.success.totalCount} | ₹0`);

                    if (response.success.totalFee != null)
                        $('#smartPayoutFeeAndTaxTotal').html(`${response.success.totalCount} | ${setTooltipAndvalue(response.success.totalFee)} | ${setTooltipAndvalue(response.success.totalTax)}`);
                    else
                        $('#smartPayoutFeeAndTaxTotal').html(`${response.success.totalCount} | &#8377;0 | &#8377;0`);

                    getUPIRecords(from, to);
                    getAepsRecords(from, to);
                }
            });
        }
    }

    function getUPIRecords(from, to) {
        $.ajax({
            url: "<?php echo e(secure_url('admin/allreports/upi')); ?>",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                from: from,
                to: to,
                user_id: $('#searchForm').find('[name="user_id"]').val()
            },
            success: (response) => {
                totalVolumeCount += response.upi_stack ? (response.upi_stack.totalCount) : 0;
                totalVolumeAmount += (response.upi_stack.totalAmount != null) ? (response.upi_stack.totalAmount) : 0;

                totalTaxFeeCount += response.upi_stack_fee_and_tax ? (response.upi_stack_fee_and_tax.totalCount) : 0;
                totalFeeAmount += (response.upi_stack_fee_and_tax.totalUpiFee != null) ? (response.upi_stack_fee_and_tax.totalUpiFee) : 0;
                totalTaxAmount += (response.upi_stack_fee_and_tax.totalUpiTax != null) ? (response.upi_stack_fee_and_tax.totalUpiTax) : 0;

                if (response.upi_stack.totalAmount != null)
                    $('#upiStackTotal').html(`${response.upi_stack.totalCount} | ${setTooltipAndvalue(response.upi_stack.totalAmount)}`);
                else
                    $('#upiStackTotal').html(`${response.upi_stack.totalCount} | ₹0`);

                if (response.upi_stack_fee_and_tax.totalUpiFee != null)
                    $('#upiStackFeeAndTaxTotal').html(`${response.upi_stack_fee_and_tax.totalCount} | ${setTooltipAndvalue(response.upi_stack_fee_and_tax.totalUpiFee)} | ${setTooltipAndvalue(response.upi_stack_fee_and_tax.totalUpiTax)}`);
                else
                    $('#upiStackFeeAndTaxTotal').html(`${response.upi_stack_fee_and_tax.totalCount} | &#8377;0 | &#8377;0`);

                getVanRecords(from, to);
            }
        });
    }

    function getVanRecords(from, to) {
        $.ajax({
            url: "<?php echo e(secure_url('admin/allreports/van')); ?>",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                from: from,
                to: to,
                user_id: $('#searchForm').find('[name="user_id"]').val()
            },
            success: function(response) {
                totalVolumeCount += response[0].totalCount ? (response[0].totalCount) : 0;
                totalVolumeAmount += (response[0].totalAmount != null) ? (response[0].totalAmount) : 0;

                totalVolumeCount += response[2].totalCountSmartCollect ? (response[2].totalCountSmartCollect) : 0;
                totalVolumeAmount += (response[2].totalAmountSmartCollect != null) ? (response[2].totalAmountSmartCollect) : 0;

                totalTaxFeeCount += response[1].totalCountPartnerFeeAndTax ? (response[1].totalCountPartnerFeeAndTax) : 0;
                totalFeeAmount += (response[1].totalPartnerFee != null) ? (response[1].totalPartnerFee) : 0;
                totalTaxAmount += (response[1].totalPartnerTax != null) ? (response[1].totalPartnerTax) : 0;

                totalTaxFeeCount += response[3].totalCountSmartCollectFeeAndTax ? (response[3].totalCountSmartCollectFeeAndTax) : 0;
                totalFeeAmount += (response[3].totalSmartCollectFee != null) ? (response[3].totalSmartCollectFee) : 0;
                totalTaxAmount += (response[3].totalSmartCollectTax != null) ? (response[3].totalSmartCollectTax) : 0;

                //Partner's VAN
                if (response[0].totalAmount != null) {
                    $('#partnerVANTotal').html(`${response[0].totalCount} | ${setTooltipAndvalue(response[0].totalAmount)}`);
                } else {
                    $('#partnerVANTotal').html(`${response[0].totalCount} | ₹0`);
                }
                //Partner's VAN Fee and Tax
                if (response[1].totalPartnerFee != null) {
                    $('#partnerVANFeeAndTax').html(`${response[1].totalCountPartnerFeeAndTax} | ${setTooltipAndvalue(response[1].totalPartnerFee)} | ${setTooltipAndvalue(response[1].totalPartnerTax)}`);
                } else {
                    $('#partnerVANFeeAndTax').html(`${response[1].totalCountPartnerFeeAndTax} | &#8377;0 | &#8377;0`);
                }
                //Smart Collect UPI
                if (response[2].totalAmountSmartCollect != null) {
                    $('#smartCollectTotal').html(`${response[2].totalCountSmartCollect} | ${setTooltipAndvalue(response[2].totalAmountSmartCollect)}`);
                } else {
                    $('#smartCollectTotal').html(`${response[2].totalCountSmartCollect} | ₹0`);
                }
                //smart collect VAN
                if (response[3].totalSmartCollectFee != null) {
                    $('#smartCollectFeeAndTax').html(`${response[3].totalCountSmartCollectFeeAndTax} | ${setTooltipAndvalue(response[3].totalSmartCollectFee)} | ${setTooltipAndvalue(response[3].totalSmartCollectTax)}`);
                } else {
                    $('#smartCollectFeeAndTax').html(`${response[3].totalCountSmartCollectFeeAndTax} | &#8377;0 | &#8377;0`);
                }
                showTotal();

                isAjax = false;
                $('#searching').html('Search');
            }
        });
    }

    function showTotal() {
        $('#tpv').html(`${totalVolumeCount} | ${setTooltipAndvalue(totalVolumeAmount)}`);
        $('#tft').html(`${totalTaxFeeCount} | ${setTooltipAndvalue(totalFeeAmount)} | ${setTooltipAndvalue(totalTaxAmount)}`);

        $('[data-toggle="tooltip"]').tooltip();
    }


    function setTooltipAndvalue(number) {
        return `<span data-placement="top" data-toggle="tooltip" type="button" data-original-title="&#8377;${(number).toFixed(2)}">&#8377;${amt2SortAmt(number)}</span>`;
    }
    function getAepsRecords(from, to) {


            $('#searching').html('Searching');

            $.ajax({
                url: "<?php echo e(secure_url('admin/allreports/aeps')); ?>",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    from: from,
                    to: to,
                    user_id: $('#searchForm').find('[name="user_id"]').val()
                },
                success: (response) => {

                    if (response.transaction.totalAmount != null)
                    $('#aepsTotal').html(`${response.transaction.totalCount} | ${setTooltipAndvalue(Number(response.transaction.totalAmount))}`);
                    else
                        $('#aepsTotal').html(`0 | ₹0`);

                    if (response.commission.totalAmount != null)
                        $('#aepsTotalCommission').html(`${response.commission.totalCount} | ${setTooltipAndvalue(Number(response.commission.totalAmount))}`);
                    else
                        $('#aepsTotalCommission').html(`0 | &#8377;0`);
                }
            });
    }
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/pgpaysecureco/public_html/resources/views/admin//reports/reports.blade.php ENDPATH**/ ?>