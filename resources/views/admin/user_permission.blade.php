@extends('layouts.admin.app')
@section('title','Payout Dashboard')
@section('content')
<div class="content-w">
                <div class="content-box">
                <div class="element-wrapper">

                    <div class="element-box">
                    <h5 class="form-header">
                    {{$page_title}}
                    </h5>
                    <form id="orderForm" role="cancel-request-form" action="{{url('admin/user/adduserpermission/'.$id)}}" data-DataTables="datatable" method="POST">
                    	@csrf
                    	<div class="row">
                    		<div class="col-sm-6">
                    			<div class="form-group">
                    				<label>Role</label>
                    				<select class="form-control" name="role_id">
                    					<option></option>
                    					@foreach($roles as $role)
                    					<option value="{{$role->id}}" @if(isset($user_role->role_id)&&$user_role->role_id==$role->id) {{'selected'}} @endif>{{$role->name}}</option>
                    					@endforeach
                    				</select>
                    			</div>
                    		</div>
                    	</div>
                    	<div class="row">
                    		<div class="col-sm-6">
                    			@foreach($permissions as $per)
                    			<div class="form-check">
                    				
                    				<label class="form-check-label">

                    					<input class="form-check-input" type="checkbox" name="permission_id[]" value="{{$per->id}}" @if(in_array($per->id,$user_permission)) {{'checked'}} @endif>
                    					{{$per->name}}
                    				</label>
                    				
                    				
                    			</div>
                    			@endforeach
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

@endsection
@section('scripts')


@endsection