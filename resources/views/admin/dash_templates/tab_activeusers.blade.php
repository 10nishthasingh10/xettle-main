<div class="tab-pane" id="tab_LastTxn">
    <div class="row">
        <div class="col-md-12">

            <div class="element-box">
                @if(!Auth::user()->hasRole('aeps-support'))
                <h5 class="element-header">
                    Active by Users
                </h5>

                <form id="searchForm">
                    <fieldset class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group lats_txn">
                                    <label class="w-100">Filter User <span class="requiredstar"></span></label>
                                    <select class="form-control select2 w-100" name="user-lst_txn">
                                        <option value="0">All User</option>
                                        @foreach($userList as $row)
                                        <option value="{{$row->id}}">{{$row->name}} - {{$row->email}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group lats_txn">
                                    <label class="w-100">Filter Service <span class="requiredstar"></span></label>
                                    <select class="form-control w-100" name="service-lst_txn">
                                        <option value="0">All Services</option>
                                        @foreach($serviceList as $row)
                                        <option value="{{$row->service_id}}">{{$row->service_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">From Date <span class="requiredstar"></span></label>
                                    <input type="date" name="date-lst_txn" value="{{date('Y-m-d')}}" max="{{date('Y-m-d')}}" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="w-100">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary" id="searching" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching">
                                                <b><i class="icon-search4"></i></b> Filter
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


                <fieldset class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="dt-last_txn" class="table table-bordered table-striped table-hover dataTable no-footer w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User Name</th>
                                            <th>User Email</th>
                                            <th>user Mobile</th>
                                            <th>Txn Date</th>
                                            <th>Service Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </fieldset>

                @endif
            </div>

        </div>
    </div>
</div>