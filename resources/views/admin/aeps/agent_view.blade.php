@extends('layouts.admin.app')
@section('title','AEPS Agent Details')
@section('content')
<link rel="stylesheet" href="https://rawgit.com/LeshikJanz/libraries/master/Bootstrap/baguetteBox.min.css">
<link href="{{asset('css/lightgallery.css')}}" rel="stylesheet">
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
    .photo-gallery {
  color:#313437;
  background-color:#fff;
}

.photo-gallery p {
  color:#7d8285;
}

.photo-gallery h2 {
  font-weight:bold;
  margin-bottom:40px;
  padding-top:40px;
  color:inherit;
}

@media (max-width:767px) {
  .photo-gallery h2 {
    margin-bottom:25px;
    padding-top:25px;
    font-size:24px;
  }
}

.photo-gallery .intro {
  font-size:16px;
  max-width:500px;
  margin:0 auto 40px;
}

.photo-gallery .intro p {
  margin-bottom:0;
}

.photo-gallery .photos {
  padding-bottom:20px;
}

.photo-gallery .item {
  padding-bottom:30px;
}



.container.gallery-container {
    background-color: #fff;
    color: #35373a;
    min-height: 100vh;
    padding: 30px 50px;
}

.gallery-container h1 {
    text-align: center;
    margin-top: 50px;
    font-family: 'Droid Sans', sans-serif;
    font-weight: bold;
}

.gallery-container p.page-description {
    text-align: center;
    margin: 25px auto;
    font-size: 18px;
    color: #999;
}

.tz-gallery {
    padding: 40px;
}

/* Override bootstrap column paddings */
.tz-gallery .row > div {
    padding: 2px;
}

.tz-gallery .lightbox img {
    width: 100%;
    border-radius: 0;
    position: relative;
}

.tz-gallery .lightbox:before {
    position: absolute;
    top: 50%;
    left: 50%;
    margin-top: -13px;
    margin-left: -13px;
    opacity: 0;
    color: #fff;
    font-size: 26px;
    font-family: 'Glyphicons Halflings';
    content: '\e003';
    pointer-events: none;
    z-index: 9000;
    transition: 0.4s;
}


.tz-gallery .lightbox:after {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    content: '';
    transition: 0.4s;
}

.tz-gallery .lightbox:hover:after,
.tz-gallery .lightbox:hover:before {
    opacity: 1;
}

.baguetteBox-button {
    background-color: transparent !important;
}

@media(max-width: 768px) {
    body {
        padding: 0;
    }
}
</style>
<style>
            body{
                background-color: #152836
            }
            .demo-gallery > ul {
                margin-bottom: 0;
            }
            .demo-gallery > ul > li {
                float: left;
                margin-bottom: 15px;
                margin-right: 20px;
                /*width: 200px;*/
            }
            .demo-gallery > ul > li a {
                border: 3px solid #FFF;
                border-radius: 3px;
                display: block;
                overflow: hidden;
                position: relative;
                float: left;
            }
            .demo-gallery > ul > li a > img {
                -webkit-transition: -webkit-transform 0.15s ease 0s;
                -moz-transition: -moz-transform 0.15s ease 0s;
                -o-transition: -o-transform 0.15s ease 0s;
                transition: transform 0.15s ease 0s;
                -webkit-transform: scale3d(1, 1, 1);
                transform: scale3d(1, 1, 1);
                height: 100%;
                width: 100%;
            }
            .demo-gallery > ul > li a:hover > img {
                -webkit-transform: scale3d(1.1, 1.1, 1.1);
                transform: scale3d(1.1, 1.1, 1.1);
            }
            .demo-gallery > ul > li a:hover .demo-gallery-poster > img {
                opacity: 1;
            }
            .demo-gallery > ul > li a .demo-gallery-poster {
                background-color: rgba(0, 0, 0, 0.1);
                bottom: 0;
                left: 0;
                position: absolute;
                right: 0;
                top: 0;
                -webkit-transition: background-color 0.15s ease 0s;
                -o-transition: background-color 0.15s ease 0s;
                transition: background-color 0.15s ease 0s;
            }
            .demo-gallery > ul > li a .demo-gallery-poster > img {
                left: 50%;
                margin-left: -10px;
                margin-top: -10px;
                opacity: 0;
                position: absolute;
                top: 50%;
                -webkit-transition: opacity 0.3s ease 0s;
                -o-transition: opacity 0.3s ease 0s;
                transition: opacity 0.3s ease 0s;
            }
            .demo-gallery > ul > li a:hover .demo-gallery-poster {
                background-color: rgba(0, 0, 0, 0.5);
            }
            .demo-gallery .justified-gallery > a > img {
                -webkit-transition: -webkit-transform 0.15s ease 0s;
                -moz-transition: -moz-transform 0.15s ease 0s;
                -o-transition: -o-transform 0.15s ease 0s;
                transition: transform 0.15s ease 0s;
                -webkit-transform: scale3d(1, 1, 1);
                transform: scale3d(1, 1, 1);
                height: 100%;
                width: 100%;
            }
            .demo-gallery .justified-gallery > a:hover > img {
                -webkit-transform: scale3d(1.1, 1.1, 1.1);
                transform: scale3d(1.1, 1.1, 1.1);
            }
            .demo-gallery .justified-gallery > a:hover .demo-gallery-poster > img {
                opacity: 1;
            }
            .demo-gallery .justified-gallery > a .demo-gallery-poster {
                background-color: rgba(0, 0, 0, 0.1);
                bottom: 0;
                left: 0;
                position: absolute;
                right: 0;
                top: 0;
                -webkit-transition: background-color 0.15s ease 0s;
                -o-transition: background-color 0.15s ease 0s;
                transition: background-color 0.15s ease 0s;
            }
            .demo-gallery .justified-gallery > a .demo-gallery-poster > img {
                left: 50%;
                margin-left: -10px;
                margin-top: -10px;
                opacity: 0;
                position: absolute;
                top: 50%;
                -webkit-transition: opacity 0.3s ease 0s;
                -o-transition: opacity 0.3s ease 0s;
                transition: opacity 0.3s ease 0s;
            }
            .demo-gallery .justified-gallery > a:hover .demo-gallery-poster {
                background-color: rgba(0, 0, 0, 0.5);
            }
            .demo-gallery .video .demo-gallery-poster img {
                height: 48px;
                margin-left: -24px;
                margin-top: -24px;
                opacity: 0.8;
                width: 48px;
            }
            .demo-gallery.dark > ul > li a {
                border: 3px solid #04070a;
            }
            .home .demo-gallery {
                padding-bottom: 80px;
            }
            img.img-responsive {
    height: 190px !important;
    width: 190px !important;
}
        </style>

			<div class="content-w">
                <div class="content-box">
                <!--------------------
                START - Color Scheme Toggler
                -------------------->
                <div class="element-wrapper">
  
  <div class="element-box">
    <h5 class="form-header">
      {{$page_title}}
    </h5>
</div>
    <div class="element-content">
        <div class="element-box">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <table class="table table-lightborder">
                           <tbody>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{$first_name.' '. $middle_name.' '.$last_name}}</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile</th>
                                        <td>{{$mobile}}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{$email}}</td>
                                    </tr>
                                    <tr>
                                        <th>Merchant Code</th>
                                        <td>{{$merchant_code}}</td>
                                    </tr>
                                    <tr>
                                        <th>DOB</th>
                                        <td>{{$dob}}</td>
                                    </tr>
                                    <tr>
                                        <th>Aadhaar</th>
                                        <td>{{$aadhaar_no}}</td>
                                    </tr>
                                    <tr>
                                        <th>Pan</th>
                                        <td>{{$pan_no}}</td>
                                    </tr>
                                    <tr>
                                        <th>Address</th>
                                        <td>{{$address}}</td>
                                    </tr>
                                    <tr>
                                        <th>Shop Name</th>
                                        <td>{{$shop_name}}</td>
                                    </tr>
                                    <tr>
                                        <th>Shop Address</th>
                                        <td>{{$shop_address}}</td>
                                    </tr>
                                    <tr>
                                        <th>Pin Code</th>
                                        <td>{{$pin_code}}</td>
                                    </tr>
                                    <tr>
                                        <th>Shop Pin Code</th>
                                        <td>{{$shop_pin}}</td>
                                    </tr>
                                    <tr>
                                        <th>States</th>
                                        <td>{{$states_name}}</td>
                                    </tr>
                                    <tr>
                                        <th>District</th>
                                        <td>{{$district_name}}</td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{$created_at}}</td>
                                    </tr>
                                    <tr>
                                        <th>Documents Uploaded At</th>
                                        <td>{{$ekyc_documents_uploaded_at}}</td>
                                    </tr>
                                    @if($doc_accepted_at)
                                    <tr>
                                        <th>Documents Accepted At</th>
                                        <td>{{$doc_accepted_at}}</td>
                                    </tr>
                                    @endif
                                    @if($doc_rejected_at)
                                    <tr>
                                        <th>Documents Rejected At</th>
                                        <td>{{$doc_rejected_at}}</td>
                                    </tr>
                                    @endif
                                </tbody>
                        
                        </table>
                        
                    </div>
                </div>
            </div>
            <!-- <div class="tz-gallery">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Aadhaar Front</label>
                        @if(isset($aadhaar_front_url))
                        <div><a href="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$aadhaar_front_url)}}" class="lightbox" data-lightbox="photos"><img class="img-fluid" style="width:100px;height:100px" src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$aadhaar_front_url)}}">
                        </a>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Aadhaar Back</label>
                        @if(isset($aadhaar_back_url))
                        <div >
                        <a  class="lightbox" href="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$aadhaar_back_url)}}" data-lightbox="photos"><img class="img-fluid"  style="width:100px;height:100px" src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$aadhaar_back_url)}}">
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Pan Front</label>
                        @if(isset($pan_front_url))
                        <div>
                        <a  class="lightbox" href="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$pan_front_url)}}" data-lightbox="photos"><img class="img-fluid" style="width:100px;height:100px" src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$pan_front_url)}}">
                              
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Shop Photo</label>
                        @if(isset($shop_photo_url))
                        <div >
                        <a   class="lightbox" href="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$shop_photo_url)}}" data-lightbox="photos"><img class="img-fluid"  style="width:100px;height:100px" src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$shop_photo_url)}}">
                              
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Photo</label>
                        @if(isset($photo_url))
                        <div>
                        <a  class="lightbox" href="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$photo_url)}}" data-lightbox="photos"><img class="img-fluid"  style="width:100px;height:100px" src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$photo_url)}}">
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
               
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <div>@if((isset($status))&&$status=='pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif((isset($status))&&$status=='accepted')
                                <span class="badge badge-success">Accepted</span>
                            @elseif((isset($status))&&$status=='rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @endif
                        </div>
                        <div>{{isset($remarks)?$remarks:''}}</div>
                    </div>
                </div>
            </div>
            </div> -->
            <div class="row">
            <div class="demo-gallery">
            <ul id="lightgallery" class="list-unstyled">
                @if(isset($aadhaar_front_url))
                <li class="col-xs-6 col-sm-4 col-md-2"  data-src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$aadhaar_front_url)}}">
                    <a href="">
                        <img class="img-responsive" src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$aadhaar_front_url)}}" alt="Thumb-1" style="height: 200px;">
                    </a>
                </li>
                @endif
                @if(isset($aadhaar_back_url))
                <li class="col-xs-6 col-sm-4 col-md-2" data-src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$aadhaar_back_url)}}">
                    <a href="">
                        <img class="img-responsive" src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$aadhaar_back_url)}}" alt="Thumb-2" style="height: 200px;">
                    </a>
                </li>
                @endif
                @if(isset($pan_front_url))
                <li class="col-xs-6 col-sm-4 col-md-2" data-src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$pan_front_url)}}" >
                    <a href="">
                        <img class="img-responsive" src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$pan_front_url)}}" alt="Thumb-3" style="height: 200px;">
                    </a>
                </li>
                @endif
                @if(isset($shop_photo_url))
                <li class="col-xs-6 col-sm-4 col-md-2" data-src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$shop_photo_url)}}">
                    <a href="">
                        <img class="img-responsive" src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$shop_photo_url)}}" alt="Thumb-4" style="height: 200px;">
                    </a>
                </li>
                @endif
                @if(isset($photo_url))
                <li class="col-xs-6 col-sm-4 col-md-2" data-src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$photo_url)}}">
                    <a href="">
                        <img class="img-responsive" src="{{env('AEPS_KYC_URL')}}/public/storage/{{str_replace('public/','',$photo_url)}}" alt="Thumb-4" style="height: 200px;">
                    </a>
                </li>
                @endif
                
            </ul>
            
            </div>
            <div class="col-xs-6 col-sm-4 col-md-1">
                    <div class="form-group">
                        <label>Status</label>
                        <div>@if((isset($status))&&$status=='pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif((isset($status))&&$status=='accepted')
                                <span class="badge badge-success">Accepted</span>
                            @elseif((isset($status))&&$status=='rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @endif
                        </div>
                        <div>{{isset($remarks)?$remarks:''}}</div>
                    </div>
                </div>
            </div>
            <div class=" text-right">
                        <div class="value font-1-5" id="totalCountVanApi">
                        @if ((isset($status)) && ($status=='pending' || $status == 'accepted'))
                                <button class="btn btn-success status-action" onclick="sendKYCAttachment('{{$id}}')" >
                                @if ($is_attachment_send == 0)
                                    Send Attachemnt
                                @else
                                    Re-Send Attachemnt
                                @endif
                            </button>

                        @endif

                            @if((isset($status))&&($status=='pending' || $status=='accepted'))
                                <button class="btn btn-danger status-action" data-value="rejected" data-target="#statusModal" data-toggle="modal">Reject</button>
                            @endif
                            @if((isset($status))&&($status=='pending' || $status=='rejected'))
                            <button class="btn btn-success status-action" data-value="accepted" data-target="#statusModal" data-toggle="modal">Accept</button>
                            @endif

                        @if(in_array($documents_status, ['pending', 'accepted']) && $is_ekyc_documents_uploaded && (isset($airtel_is_ekyc) || isset($icici_is_ekyc)) && in_array(isset($paytm_is_ekyc), ['0', '2', '3']))
                            <button class="btn btn-success" id="activate_paytm" data-value="approved">Activate Paytm</button>
                        @endif
                        @if(in_array($documents_status, ['pending', 'accepted']) && $is_ekyc_documents_uploaded && (isset($airtel_is_ekyc) || isset($icici_is_ekyc)) && in_array(isset($sbm_is_ekyc), ['0', '2', '3']))
                            <button class="btn btn-success" id="activate_sbm" data-value="approved">Activate SBM</button>
                        @endif
                        @if ($documents_status == 'pending')
                            @if($is_ekyc_documents_uploaded && (isset($airtel_is_ekyc) || isset($icici_is_ekyc)))
                                <button class="btn btn-success" id="document_status" data-value="approved">Document Approved</button>
                            @endif
                        @endif
                        <a class="btn btn-primary" href="{{url('admin/aeps/agents')}}">Back</a>
                    </div>
                </div>
        </div>

    </div>

</div>
</div>
</div>
</div>
<div class="modal fade " id="imagemodal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" style="max-width:1000px">
            <div class="modal-content">
                <img class="modal-img" />
            </div>
        </div>
    </div>
<div class="modal fade " id="statusModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Change status of remark
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true"> ×</span></button>
                </div>
                <form id="orderForm" role="assign-scheme-form" action="{{url('admin/aeps/changeStatus/'.$id)}}" data-datatables="datatable" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Remark <span class="requiredstar">*</span></label>
                                    <textarea class="form-control" name="remarks"></textarea>
                                    <input type="hidden" name="action" id="action">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button"> Close</button>
                        <input class="btn btn-primary" type="submit" data-request="ajax-submit" data-target="[role='assign-scheme-form']" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/picturefill/2.3.1/picturefill.min.js"></script>
        <script src="https://cdn.rawgit.com/sachinchoolur/lightgallery.js/master/dist/js/lightgallery.js"></script>
        <script src="https://cdn.rawgit.com/sachinchoolur/lg-pager.js/master/dist/lg-pager.js"></script>
        <script src="https://cdn.rawgit.com/sachinchoolur/lg-autoplay.js/master/dist/lg-autoplay.js"></script>
        <script src="https://cdn.rawgit.com/sachinchoolur/lg-fullscreen.js/master/dist/lg-fullscreen.js"></script>
        <script src="https://cdn.rawgit.com/sachinchoolur/lg-zoom.js/master/dist/lg-zoom.js"></script>
        <script src="https://cdn.rawgit.com/sachinchoolur/lg-hash.js/master/dist/lg-hash.js"></script>
        <script src="https://cdn.rawgit.com/sachinchoolur/lg-share.js/master/dist/lg-share.js"></script>
        <script src="{{asset('js/lg-rotate.js')}}"></script>
        <script>
            lightGallery(document.getElementById('lightgallery'));
        </script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.8.1/baguetteBox.min.js"></script> -->
<script>
    //baguetteBox.run('.tz-gallery');
</script>
<script type="text/javascript">
    $(function(){
      $(".image img").on("click",function(){
         var src = $(this).attr("src");
         $(".modal-img").prop("src",src);
      });
    });
    $('.status-action').click(function(){
        var action = $(this).data('value');
        $('#action').val(action);
    });

    function sendKYCAttachment(id) {
                $.ajax({
                    url: "{{custom_secure_url('admin/accounts/sendKYCAttachment')}}",
                    type: "post",
                    data: {
                        'id': id,
                        'userId': "{{encrypt(Auth::user()->id)}}",
                        _token:$('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                       if (data.status) {
                        Swal.fire({
                            title: 'Send Attachment',
                            text: 'KYC attachment file has been send',
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                             location.reload();
                        });

                       } else {
                        Swal.fire({
                            title: 'Oops...',
                            text: data.message,
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                       }
                    }
                });
            }
    $('#activate_paytm').on('click',function(){

        var action = ($(this).data('value'));
        if(confirm('Are you sure, Do you want to activate PAYTM?'))
        {
            $.ajax({
                url:"{{custom_secure_url('admin/aeps/paytmkyc')}}",
                type:"POST",
                data:{_token:$('meta[name="csrf-token"]').attr('content'),id:"{{$id}}", action:action, route:"paytm"},
                success:function(response)
                {
                    if (response.status) {
                        Swal.fire({
                            title: 'Paytm KYC',
                            text: 'Paytm KYC approved',
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                             location.reload();
                        });

                   } else {
                        Swal.fire({
                            title: 'Oops...',
                            text: response.message,
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                   }
                }
            });
        }
    });

    $('#activate_sbm').on('click',function(){
        var action = ($(this).data('value'));
        if(confirm('Are you sure, Do you want to activate SBM?'))
        {
            $.ajax({
                url:"{{custom_secure_url('admin/aeps/paytmkyc')}}",
                type:"POST",
                data:{_token:$('meta[name="csrf-token"]').attr('content'),id:"{{$id}}", action:action, route:'sbm'},
                success:function(response)
                {
                    if (response.status) {
                        Swal.fire({
                            title: 'SBM KYC',
                            text: 'SBM KYC approved',
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                            location.reload();
                        });

                } else {
                        Swal.fire({
                            title: 'Oops...',
                            text: response.message,
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                }
                }
            });
        }
    });

    $('#document_status').on('click',function(){
        var action = ($(this).data('value'));
        if(confirm('Are you sure, Do you want to activate document status?'))
        {
            $.ajax({
                url:"{{custom_secure_url('admin/aeps/paytmkyc')}}",
                type:"POST",
                data:{_token:$('meta[name="csrf-token"]').attr('content'),id:"{{$id}}", action:action, route:'icici'},
                success:function(response)
                {
                    if (response.status) {
                        Swal.fire({
                            title: 'KYC',
                            text: 'KYC approved',
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            // cancelButtonColor: '#d33',
                            // confirmButtonText: 'Yes'
                        }).then((result) => {
                            location.reload();
                        });

                } else {
                        Swal.fire({
                            title: 'Oops...',
                            text: response.message,
                            icon: "error",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                }
                }
            });
        }
    });
</script>
@endsection
