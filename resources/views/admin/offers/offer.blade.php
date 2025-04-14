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
<!--begin::Table-->
<div class="content-w">
    <div class="content-box custom-content-box">
        <div class="element-wrapper">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="form-header">
                        {{$page_title}}
                    </h5>
                </div>
                <div class="col-md-4 text-right">
                    <a class="bold-label text-primary" href="#">
                        <div class="value font-1-5" id="totalCountVanApi">
                            <button class="btn btn-success showModal" >Add Offer</button>
                        </div>
                    </a>
                </div>
            </div>
            <div class="element-box">
               <div class="table-responsive custom-table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
 
                                <th>Title</th>
                                <th>Offer Id</th>
                                <th>Category</th>
                                <th>Short Description</th>
                                <th>Shared Description</th>
                                <th>Description</th>
                                <th>Logo</th>
                                <th>Shared Logo</th>
                                <th>Description Image</th>
                                <th>Expired At</th>
                                <th>Status</th>
                                
                                <th>Created At</th>
                                <th>Action</th>
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
    <!-- assign schemes to user -->
    <div aria-hidden="true" aria-labelledby="exampleModalLabel" class="modal fade" id="addMessageModal" role="dialog" tabindex="-1">

        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Add Offer
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form id="orderForm" role="add-message-form" action="{{url('admin/offer/add-offer')}}" data-DataTables="datatable" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-1">
                                <label class="col-form-label" for="account-old-password">Title</label>
                                   <input class="form-control" type="text" name="title">
                            </div>
                            <div class="col-12 col-sm-6 mb-1">
                                <label class="col-form-label" for="account-old-password">Category</label>
                                   <select class="form-control" name="category_id">
                                       <option value=""></option>
                                        @foreach($categoryList as $cat)
                                            <option value="{{$cat->id}}">{{$cat->title}}</option>
                                        @endforeach
                                   </select>
                            </div>
                            
                            
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-1">
                                <label class="col-form-label" for="account-old-password">Offer Link</label>
                                <input class="form-control" type="text" name="offer_link">
                            </div>
                            <div class="col-12 col-sm-6 mb-1">
                                <label class="col-form-label" for="account-old-password">Tracking URL</label>
                                <input class="form-control" type="text" name="track_url">
                            </div>
                            
                            
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-12 mb-1">
                                <label class="col-form-label" for="account-old-password">Short Description</label>
                                   <textarea id="mytextarea" class="form-control" name="short_description"></textarea>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-12 mb-1">
                                <label class="col-form-label" for="account-old-password">Shared Description</label>
                                   <textarea id="" class="form-control" name="shared_description"></textarea>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-12 mb-1">
                                <label class="col-form-label" for="">Description</label>
                                <textarea class="" id="editor" name="long_description"></textarea>
                                <div class="text-muted"><b>Insert Emoji:</b>
                                    <ul><li>Type text start with ":". For example, :smile or :+1:.</li>
                                    <li>For Windows: Press Window + .(dot) </li>
                                    <li>For Mac: Press Ctrl + cmd + space</li>
                                </ul>

                                </div>
                                
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-12 col-sm-6 mb-1">
                                <label class="col-form-label" for="account-old-password">Button Text</label>
                                <select class="form-control" name="button_text">
                                    <option value="">Select Text</option>
                                    <option value="Redeem Now">Redeem Now</option>
                                    <option value="Get Offer">Get Offer</option>
                                    <option value="Claim Offer">Claim Offer</option>
                                    <option value="View Offer">View Offer</option>
                                    <option value="Share">Share</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 mb-1">
                                <label class="col-form-label" for="account-old-password">Expired</label>
                                <input type="date" id="fp-date-time" class="form-control" value="{{date('Y-m-d')}}" min="{{date('Y-m-d')}}" placeholder="YYYY-MM-DD HH:MM" name="expired_at">
                            </div>
                            
                            
                            <!-- <div>Note: Image dimensions 900 X 300</div> -->
                        </div>
                        
                       <div class="row">
                            <div class="col-12 col-sm-6 mb-1">
                                <label class="col-form-label" for="account-old-password">Offer Logo</label>
                                <input class="form-control" type="file" name="offer_logo" accept="image/*">
                            </div>
                            <div class="col-12 col-sm-6 mb-1">
                                
                                <label class="col-form-label" for="account-old-password">Shared Image</label>
                                <input type="file" class="form-control" name="shared_image" accept="image/*">
                            
                            </div>
                            <div class="col-12 col-sm-6 mb-1">
                                
                                <label class="col-form-label" for="account-old-password">Description Image</label>
                                <input type="file" class="form-control" name="desc_image" accept="image/*">
                            
                            </div>
                            <div class="col-12 col-sm-6 mb-1">
                                
                                <label class="col-form-label" for="account-old-password">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Select</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            
                                <input type="hidden" name="id">
                            </div>
                        </div>
                        <div class="">
                            <label class="col-form-label" for="account-old-password">Input Fields</label>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="" style="margin-right : 20px;"> Pincode : </label>
                                    <label class="switch" id="" data-user="">
                                        <input type="checkbox" name="pincode" value="1">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target='[role="add-message-form"]' value="Submit" />
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div aria-hidden="false" aria-labelledby="exampleModalLabel" class="modal fade" id="viewMessageModal" role="dialog" >

        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
               
                    <div class="modal-body">
                        <div class="view_data">
                        </div>
                        

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        
                    </div>

            </div>
        </div>
    </div>

@section('scripts')

<script src="{{url('public/js//dataTables.buttons.min.js')}}"></script>
<script src="{{url('public/js/pdfmake.min.js')}}"></script>
<script src="{{url('public/js/jszip.min.js')}}"></script>
<script src="{{url('public/js/vfs_fonts.js')}}"></script>
<script src="{{url('public/js/buttons.html5.min.js')}}"></script>
<script src="{{url('public/js/buttons.print.min.js')}}"></script>
<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<!-- <script src="{{asset('summernote/summernote-ext-emoji.js')}}"></script> -->
<!-- <script src="https://cdn.tiny.cloud/1/8rv1vdufeaeblv9aoksxnaikizcjfycjqsjapc4chojq2yg8/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script> -->
<script type="text/javascript">
    $(document).ready(function () {
      var self = this;

      // load github's emoji list
      $.ajax({
        url: 'https://api.github.com/emojis'
      }).then(function (data) {
        var emojis = Object.keys(data);
        var emojiUrls = data;

        $('#editor').summernote({
          placeholder: '',
          height: 300,
          hintDirection: 'top',
          hint: [{
            search: function (keyword, callback) {
              callback($.grep(emojis, function (item) {
                return item.indexOf(keyword)  === 0;
              }));
            },
            match: /\B:([\-+\w]+)$/,
            template: function (item) {
              var content = emojiUrls[item];
              return '<img src="' + content + '" width="20" /> :' + item + ':';
            },
            content: function (item) {
              var url = emojiUrls[item];
              if (url) {
                return $('<img />').attr('src', url).css('width', 20)[0];
              }
              return '';
            }
          }]
        });
      });
    });
  </script>
<script>

//   $(document).ready(function() {
//     document.emojiSource = '{{asset("summernote/pngs")}}/';
//       $('#editor').summernote({
//             placeholder: 'Type your message here',
//             tabsize: 2,
//             height: 300,
            
//           });

// });
</script>
<script>
  // tinymce.init({
  //   selector: "#mytextarea",
  //   plugins: "emoticons",
  //   toolbar: "emoticons",
  //   toolbar_location: "top",
  //   menubar: false,
  //   height:200
  // });
</script>

<script>
    $(document).ready(function() {

        // let currDate = new Date().toJSON().slice(0,10);
        let currDate = `{{date('Y-m-d', strtotime('-1 day'))}}`;
        $('#toDate').attr('max', currDate);
        $('#fromDate').attr('max', currDate);


        var url = "{{custom_secure_url('admin/fetch')}}/offer_list/0";
        var onDraw = function() {};
        var options = [ {
                "data": "title"
            },
            {
                "data":"offer_id"
            },
            {
                "data":"category_id",
                render:function(data,type,full)
                {
                    if(data != null && full.category != null)
                    {
                        return full.category.title;
                    }
                    return '';
                }
            },
            {
                "data":"short_description",
                render:function(data,type,full)
                {
                    return '<button class="btn btn-primary btn-sm view-data" data-des="'+data+'">Show</button>';
                }
            },
            {
                "data":"shared_description",
                render:function(data,type,full)
                {
                    return '<button class="btn btn-primary btn-sm view-data" data-des="'+data+'">Show</button>';
                }
            },
            {
                "data":"description",
                render:function(data,type,full)
                {
                    return "<button class='btn btn-primary btn-sm view-data' data-des='"+data+"'>Show</button>";
                }
            },
            {
                "data":"offer_logo",
                render:function(data,type,full)
                {
                    if(data != null && data !='')
                    {
                        return '<a href="javascript:void(0)" class="btn btn-primary btn-sm view-image" data-des="'+data+'">Show</a>';
                    }
                    
                    return '';
                }
            },
            {
                "data":"shared_image",
                render:function(data,type,full)
                {
                    if(data != null && data !='')
                    {
                        return '<a href="javascript:void(0)"  class="btn btn-primary btn-sm view-image" data-des="'+data+'">Show</a>';
                    }
                    
                    return '';
                }
            },
            {
                "data":"desc_image",
                render:function(data,type,full)
                {
                    if(data != null && data !='')
                    {
                        return '<a href="javascript:void(0)"  class="btn btn-primary btn-sm view-image" data-des="'+data+'">Show</a>';
                    }
                    
                    return '';
                }
            },
            {
                "data":"expired_at"
            },
            {
                data:"status",
                render:function(data,type,full)
                {
                    
                    if(data == '1')
                    {
                        return showSpan('active');
                    }else
                    {
                        return showSpan('inactive');
                    }
                }
            },
            {
                "data": "new_created_at"
            },
            {
                "data": "file_url",
                "orderable": false,
                render: function(data, type, full, meta) {
                    
                        return '<span class="edit btn btn-primary btn-sm"><a href="javascript:void(0)"  data-id="'+full.id+'" class="edit-offer"  tooltip="Edit Offer"  style="color:white;text-decoration:none" ><i class="os-icon os-icon-edit"></i></a></span>';
                    
                }
            }
        ];
        datatableSetup(url, options, onDraw);
    });
    $('.showModal').on('click',function(){
        $("#editor").summernote("reset");
        $('form#orderForm')[0].reset();
        $('#addMessageModal').modal('show');
    });
    $('#datatable').on('click','.edit-offer',function(){
        $('form#orderForm')[0].reset();
        $("#editor").summernote("reset");
        var id = $(this).data('id');
        var csrf = $('meta[name="csrf-token"]').attr('content')
        $.ajax({
            url:"{{url('admin/offer/getOffer/')}}/"+id,
            type:"get",
            data:{_token:csrf,id:id},
            success:function(resp){

                if(resp.status == 'SUCCESS')
                {
                    $('#addMessageModal input[name=title]').val(resp.data.title);
                    $('#addMessageModal textarea[name=short_description]').val(resp.data.short_description);
                    $('#addMessageModal textarea[name=shared_description]').val(resp.data.shared_description);
                    $('#addMessageModal textarea[name=long_description]').val(resp.data.description);
                    $('#addMessageModal input[name=offer_link]').val(resp.data.offer_link);
                    $('#addMessageModal input[name=track_url]').val(resp.data.track_url);
                    $('#addMessageModal input[name=expired_at]').val(resp.data.expired_at);
                    $('#editor').summernote('code', resp.data.description);
                    $('#addMessageModal select[name=category_id] option[value="'+resp.data.category_id+'"]').prop("selected", true);
                    $('#addMessageModal select[name=button_text] option[value="'+resp.data.button_text+'"]').prop("selected", true);
                    $('#addMessageModal select[name=status] option[value="'+resp.data.status+'"]').prop("selected", true);
                    $('#addMessageModal input[name=id]').val(resp.data.id);
                    console.log(resp.data.input_fields);
                    if(resp.data.input_fields=='pincode')
                    {
                        $('#addMessageModal input[name=pincode]').prop('checked', true);
                    }
                    $('#addMessageModal').modal('show');
                }
            }
        })
    });
    /*$('form#searchForm').submit(function() {
        $('#searching').attr('disabled', 'disabled');
        $('#searching').text('loading');
        var from = $(this).find('input[name="from"]').val();
        var to = $(this).find('input[name="to"]').val();
        var reports = $(this).find('input[name="reports"]').val();
        var userId = $(this).find('input[name="userId"]').val();

        setTimeout(function()
        {
            $('#searching').attr('disabled', false);
            $('#searching').text(' Generate Excel File');
            $('#datatable').dataTable().api().ajax.reload();
        },  3 * 60 * 1000);
        return false;
    }); */
</script>
<script type="text/javascript">
    function datatableSetup(urls, datas, onDraw = function() {}, ele = "#datatable", element = {}) {
        var options = {
            processing: true,
            serverSide: true,
            ordering: true,
            "searching": true,
            "lengthMenu": [
                [10, 25, 50, 75, 100, 200, 500, 1000],
                [10, 25, 50, 75, 100, 200, 500, 1000]
            ],
            dom: "Bfrltip",
            buttons: [
                
            ],
            order: [],
            columnDefs: [{
                'targets': [0],
                /* column index [0,1,2,3]*/
                'orderable': false,
                /* true or false */
            }],
            language: {
                paginate: {
                    'first': 'First',
                    'last': 'Last',
                    'next': '&rarr;',
                    'previous': '&larr;'
                }
            },
            drawCallback: function() {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
            },
            preDrawCallback: function() {
                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
            },
            ajax: {
                url: urls,
                type: "post",
                data: function(d) {
                    $("")
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.from = $('#searchForm').find('[name="from"]').val();
                    d.to = $('#searchForm').find('[name="to"]').val();
                    d.userIds = $('#searchForm').find('[name="userId"]').val();
                    d.searchText = $('#searchForm').find('[name="searchText"]').val();
                    d.reports = $('#searchForm').find('[name="reports"]').val();
                    d.status = $('#searchForm').find('[name="status"]').val();
                },
                beforeSend: function() {},
                complete: function(data) {
                    $('#searchForm').find('button:submit').button('reset');
                    $('#formReset').button('reset');
                },
                error: function(response) {}
            },
            columns: datas
        };
        $.each(element, function(index, val) {
            options[index] = val;
        });
        var DT = $(ele).DataTable(options).on('draw.dt', onDraw);
        return DT;
    }
    $('#formReset').click(function() {
        $('form#searchFormData')[0].reset();
        $('#formReset').button('loading');
        $('#datatable').dataTable().api().ajax.reload();
    });
    
    function deleteMessage(id)
    {
        if(confirm('Are you sure, want to delete this message?'))
        {
            $.ajax({
                url:"{{custom_secure_url('admin/messages/deleteMessage/')}}"+"/"+id,
                type:"POST",
                data:{id:id,"_token": "{{ csrf_token() }}"},
                success:function(result)
                {
                    console.log(result);
                    if (result.status) {
                        $('#datatable').dataTable().api().ajax.reload();
                    }
                }
            })
        }
    }

    $('#datatable').on('click','.view-data',function(){
        var des = $(this).attr('data-des');
        var modal=$('#viewMessageModal');
        modal.find('.modal-body').html(des);
        modal.modal('show');
    });
    $('#datatable').on('click','.view-image',function(){
        var des = $(this).attr('data-des');
        var modal=$('#viewMessageModal');
        modal.find('.modal-body').html('<img class="img-fluid" src="'+des+'">');
        modal.modal('show');
    });
</script>
@endsection
@endsection