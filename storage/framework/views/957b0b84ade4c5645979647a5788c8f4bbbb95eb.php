<?php $__env->startSection('title','Payout Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="content-w">
                <div class="content-box">
                <div class="element-wrapper">

                    <div class="element-box">
                    <h5 class="form-header">
                    <?php echo e($page_title); ?>

                    </h5>
                    <form id="orderForm" role="cancel-request-form" action="<?php echo e(url('admin/user/adduserpermission/'.$id)); ?>" data-DataTables="datatable" method="POST">
                    	<?php echo csrf_field(); ?>
                    	<div class="row">
                    		<div class="col-sm-6">
                    			<div class="form-group">
                    				<label>Role</label>
                    				<select class="form-control" name="role_id">
                    					<option></option>
                    					<?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    					<option value="<?php echo e($role->id); ?>" <?php if(isset($user_role->role_id)&&$user_role->role_id==$role->id): ?> <?php echo e('selected'); ?> <?php endif; ?>><?php echo e($role->name); ?></option>
                    					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    				</select>
                    			</div>
                    		</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-sm-6">
                    			<?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $per): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    			<div class="form-check">
                    				
                    				<label class="form-check-label">

                    					<input class="form-check-input" type="checkbox" name="permission_id[]" value="<?php echo e($per->id); ?>" <?php if(in_array($per->id,$user_permission)): ?> <?php echo e('checked'); ?> <?php endif; ?>>
                    					<?php echo e($per->name); ?>

                    				</label>
                    				
                    				
                    			</div>
                    			<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    		</div>
                    	</div>
                		<div class="form-buttons-w">
				            <button class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="cancel-request-form"]'> Submit</button>
		          		</div>
                    </form>
                    </div>
                </div>
            </div>
        </div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\xettle-main\resources\views/admin/user_permission.blade.php ENDPATH**/ ?>