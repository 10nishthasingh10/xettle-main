<div class="tab-pane" id="tab_TopUsers">
    <div class="row">
        <div class="col-md-12">

            <div class="element-box">
                <?php if(!Auth::user()->hasRole('aeps-support')): ?>
                <h5 class="element-header">
                    Top Users (Balances)
                </h5>
                <fieldset class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="datatable" class="table table-bordered table-striped table-hover dataTable no-footer w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User Info</th>
                                            <th>Business Name</th>
                                            <th class="text-right">Total Balance (&#8377;)</th>
                                            <th class="text-right">Primary (&#8377;)</th>
                                            <th class="text-right">Payout (&#8377;)</th>
                                            <th class="text-right">DMT (&#8377;)</th>
                                            <th class="text-right">Recharge (&#8377;)</th>
                                            <th class="text-right">Validation (&#8377;)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div><?php /**PATH /home/pgpaysecureco/public_html/resources/views/admin/dash_templates/tab_topusers.blade.php ENDPATH**/ ?>