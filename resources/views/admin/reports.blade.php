<!DOCTYPE html>
<html>
    <head>
        <title>Datatables AJAX pagination with Search and Sort - Laravel 7</title>

        <!-- Meta -->
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">

        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <style>
    #example {
        font-size: 14px;
        font-weight: 400;
    }
    .pagination>li>a, .pagination>li>span {
    position: relative;
    float: left;
    padding: 6px 12px;
    margin-left: -1px;
    line-height: 1.42857143;
    color: #428bca;
    text-decoration: none;
    background-color: #fff;
    border: 1px solid #ddd;
}

.pagination>.active>a, .pagination>.active>span, 
.pagination>.active>a:hover, .pagination>.active>span:hover,
 .pagination>.active>a:focus, .pagination>.active>span:focus
 {
z-index: 2;
    color: #fff;
    cursor: default;
    background-color: #428bca;
    border-color: #428bca;
}
</style>
    </head>
    
    <body>

<table id="example" class="table table-striped " role="grid" aria-describedby="user-list-page-info">
                                <thead>
                                <tr>
                                <th>Sr No</th>
                                    <th>Order Ref Id</th>
                                    <th>Batch id</th>
                                    <th>Payout Reference</th>
                                    <th>Amount</th>
                                    <th>Payout Mode</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                            $number = 1;
                                            $numElementsPerPage = $data->perPage(); // How many elements per page
                                            $pageNumber = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                            $currentNumber = ($pageNumber - 1) * $numElementsPerPage + $number;
                                            @endphp
                                        @foreach($data as $datas)<tr>
                                        <td>
                                        {{ $currentNumber++}}
                                    </td>
                                        <td>{{ $datas->order_ref_id }}</td>
                                        <td>{{ $datas->ord_batch_id }}</td>
                                        <td>{{ $datas->payout_reference }}</td>
                                        <td>{{ $datas->amount }}</td>
                                        <td>{{ $datas->mode }}</td>
                                        <td>{{ $datas->ord_status }}</td>
                                        <td>{{ $datas->created_at }}</td>
                                        <td>Action</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                            </table>
                            @if (isset($data) && $data->lastPage() > 1)

                            <ul class="pagination" style="margin:10px;">

                                <?php
                                $interval = 10;
                                $interval = isset($interval) ? abs(intval($interval)) : 3 ;
                                $from = $data->currentPage() - $interval;
                                if($from < 1){
                                    $from = 1;
                                }

                                $to = $data->currentPage() + $interval;
                                if($to > $data->lastPage()){
                                    $to = $data->lastPage();
                                }
                                ?>

                                <!-- first/previous -->
                                @if($data->currentPage() > 1)
                                    <li>
                                        <a href="{{ $data->url(1) }}&{{$queryString}}" aria-label="First">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ $data->url($data->currentPage() - 1) }}&{{$queryString}}" aria-label="Previous">
                                            <span aria-hidden="true">&lsaquo;</span>
                                        </a>
                                    </li>
                                @endif

                                <!-- links -->
                                @for($i = $from; $i <= $to; $i++)
                                    <?php
                                    $isCurrentPage = $data->currentPage() == $i;
                                    ?>
                                    <li class="{{ $isCurrentPage ? 'active' : '' }}">
                                        <a href="{{ !$isCurrentPage ? $data->url($i) : '#' }}&{{$queryString}}">
                                            {{ $i }}
                                        </a>
                                    </li>
                                @endfor

                                <!-- next/last -->
                                @if($data->currentPage() < $data->lastPage())
                                    <li>
                                        <a href="{{ $data->url($data->currentPage() + 1) }}&{{$queryString}}" aria-label="Next">
                                            <span aria-hidden="true">&rsaquo;</span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ $data->url($data->lastpage()) }}&{{$queryString}}" aria-label="Last">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                @endif

                            </ul>

                        @endif
                        <script src="{{url('public/js/handlebars.js')}}" ></script>

	<script id="details-template"  type="text/x-handlebars-template">

    <table class="expandtable">
        <tr>
            <td><b>Contact Id :</b></td><td>@{{contact_id}}</td><td><b>Full name :</b></td><td>@{{contact.first_name}} @{{contact.last_name}}</td><td><b>Email :</b></td><td>@{{contact.email}}</td>
        </tr>
        <tr>
            <td><b>Account No :</b></td><td>@{{contact.account_number}}</td><td><b>IFSC :</b></td><td>@{{contact.account_ifsc}}</td><td><b>Vpa :</b></td><td>@{{contact.account_vpa}}</td>
        </tr>
		<tr><td><b>Order Id :</b></td><td> @{{order_id}}</td><td><b>Product Id :</td><td>@{{product_id}}</td><td><b>Integration Id:</td><td>@{{integration_id}}</td>
        </tr>
		<tr><td><b>Amount :</b></td><td> @{{amount}}</td><td><b>Currency :</td><td>@{{currency}}</td><td><b>Fee:</td><td>@{{fee}}</td>
        </tr>
		<tr><td><b>Tax :</b></td><td> @{{tax}}</td><td><b>Mode :</td><td>@{{mode}}</td><td><b>Purpose:</b></td><td> @{{purpose}}</td>
        </tr>
		<tr><td><b>Payout Id:</td><td>@{{payout_id}}</td><td><b>Fund Account Id :</b></td><td> @{{fund_account_id}}</td><td><b>Narration :</td><td>@{{narration}}</td>
        </tr>
		<tr><td><b>Remark:</td><td>@{{remark}}</td><td><b>Status:</b></td><td> @{{status}}</td><td><b>Created At:</b></td><td> @{{new_created_at}}</td>
        </tr>
        <tr><td><b>Status Code:</td><td>@{{status_code}}</td><td><b>Status Response:</b></td><td> @{{status_response}}</td>
        </tr>
        <tr><td><b>Failed Status Code:</td><td>@{{failed_status_code}}</td><td><b>Bank Reference :</b></td><td> @{{bank_reference}}</td>
        </tr>
        <tr><td><b>Payout Reference:</td><td>@{{bulk_payout_detail.payout_reference}}</td><td></td><td></td><td></td><td></td>
        </tr>
		</table>
</script>
<script>
$(document).ready(function(){
var template = Handlebars.compile($("#details-template").html());

 // Add event listener for opening and closing details
 $('#example tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
		var table=$("#example").DataTable();
        var row = table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child( template(row.data()) ).show();
            tr.addClass('shown');
        }
    });
});

$(document).ready(function () {

    $('#example').DataTable( {
        dom: 'Bfrtip',
        paging: false,
        info: false,
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    } );
});
</script>

    </body>
</html>