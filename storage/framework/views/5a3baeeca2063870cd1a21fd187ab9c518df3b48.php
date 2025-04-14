

<?php $__env->startSection('content'); ?>

<div class="content-w">
    <div class="content-box custom-content-box">
        <div class="element-wrapper">
            <div class="element-box">
                <div class="form-desc">
                    &nbsp;
                </div>
                <form id="searchForm">
                <!-- <?php echo csrf_field(); ?> -->
                    <fieldset class="form-group">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user_id">Filter by User</label>
                                    <select class="form-control select2" name="user_id">
                                        <option value="">-- Select User --</option>
                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>" <?php if(isset($_GET['user_id']) && $_GET['user_id'] == $user->id): ?> selected  <?php endif; ?>><?php echo e($user->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="integration_id">Filter by Integration</label>
                                    <select class="form-control select2" name="integration_id">
                                        <option value="">-- Select Integration --</option>
                                        <?php $__currentLoopData = $integrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $integration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($integration->integration_id); ?>" <?php if(isset($_GET['integration_id']) && $_GET['integration_id'] == $integration->integration_id): ?> selected  <?php endif; ?>><?php echo e($integration->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="from" class="form-control" id="fromDate" <?php if(isset($_GET['from'])): ?> value="<?php echo e($_GET['from']); ?>"  <?php endif; ?> />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">To Date <span class="requiredstar"></span></label>
                                    <input type="date" name="to" class="form-control" id="toDate" <?php if(isset($_GET['to'])): ?> value="<?php echo e($_GET['to']); ?>"  <?php endif; ?> />
                                </div>
                            </div>
                          
                            <div class="col-md-2">

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary" id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
                                                <b><i class="icon-search4"></i></b> Search
                                            </button>
                                            <button type="button" class="btn btn-warning btn-labeled legitRipple" id="formReset" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Reset">
                                                <b><i class="icon-rotate-ccw3"></i></b> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
                <div class="table-responsive custom-table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th>#Id</th>
                                <th>Name</th>
                                <th>Payin</th>
                                <th>Payout</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($item['id']); ?></td>
                                <td><?php echo e($item['name']); ?></td>
                                <td>
                                    Success: <?php echo e($item['payin']['success'] ?? 0); ?>, 
                                    Pending: <?php echo e($item['payin']['pending'] ?? 0); ?>, 
                                    Rejected: <?php echo e($item['payin']['rejected'] ?? 0); ?>

                                </td>
                                <td>
                                    Hold: <?php echo e($item['payout']['hold'] ?? 0); ?>,
                                    Processing: <?php echo e($item['payout']['processing'] ?? 0); ?>,
                                    Cancelled: <?php echo e($item['payout']['cancelled'] ?? 0); ?>,
                                    Reversed: <?php echo e($item['payout']['reversed'] ?? 0); ?>,
                                    Failed: <?php echo e($item['payout']['failed'] ?? 0); ?>

                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo e(url('public/js//dataTables.buttons.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/pdfmake.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/jszip.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/vfs_fonts.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.html5.min.js')); ?>"></script>
<script src="<?php echo e(url('public/js/buttons.print.min.js')); ?>"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
        var url = "<?php echo e(custom_secure_url('/fetchpipetxn')); ?>";
        var options = [
            { "orderable": false, "searchable": false, "defaultContent": '', "data": 'count',
              "render": function(data, type, full, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { "data": "id" },
            { "data": "name" },
            { "data": "payin" },
            { "data": "payout",
              "render": function(data, type, full) {
                    const checked = data === '1' ? 'checked' : '';
                    return `<label class="switch serviceActivation" data-id="${full.id}"><input type="checkbox" ${checked}><span class="slider round"></span></label>`;
                }
            }
        ];
        // datatableSetup(url, options, onDraw);
        $('.dataTables_wrapper').css("width",$(".table-responsive").width());
        // $('#datatable').DataTable({
        //     "ajax": url,
        //     "columns": options
        // });
    });
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/integration_valume.blade.php ENDPATH**/ ?>