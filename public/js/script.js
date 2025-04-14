var $token = $('meta[name="csrf-token"]').attr('content');
$(document).on('keypress keyup','[data-request="isnumeric"]', function(event){
    if(event.which == 8){
    } else if((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
        event.preventDefault();
    }
});

function checkInternet(){
    if(!navigator.onLine) {
        Swal.fire({
            // title: "Failed",
            text: "Make sure your computer has an active Internet connection.",
            icon: "error",
            buttonsStyling: !1,
            confirmButtonText: "Close",
            customClass: {
                confirmButton: "btn btn-danger"
            }
        });
        return false;
    }

    return true;
}

$(document).on('click','[data-request="ajax-submit"]',function(e){
    e.preventDefault();

    if(!checkInternet()) {
        return false;
    }

    var isUserConfirmed = $(this).data('confirmation');

    if (isUserConfirmed === 'yes') {
        if(!confirm('Are you sure?')){
            return false;
        }
    }
	
    $('#popup').show();  
    $('#keydata').hide();  
    $('.alert').remove(); 
    $(".has-error").removeClass('has-error');
    $('.help-block').remove();
    var $this       = $(this);
    var clicktext   = $(this).text();
    $this.text('Pending...').prop('disabled', true);
    var $target     = $this.data('target');
    var $url        = $($target).attr('action');
    var $method     = $($target).attr('method');
    var formId     = $($target).attr('id');
    var $dataTablesReload  = $($target).attr('data-dataTables');
    var $modal      = $this.data('modal');
    var $data       = new FormData($($target)[0]);
    if(!$method){ $method = 'get'; }

    $.ajax({
        url: $url, 
        data: $data,
        cache: false, 
        type: $method, 
        dataType: 'json',
        contentType: false, 
        processData: false,
        success: function($response){

            if($dataTablesReload != null && $dataTablesReload != 'NAN' && $dataTablesReload != 'undefined'){
                reloadDatatable($dataTablesReload);
            }
            if ($response.status === true) {

                if($response.data !=null){
                    if($response.data.apikey != null){
                        $('#keydata').show();
                        $('#key_data').val($response.data.key); 
                        $('#secret_data').val($response.data.secret); 
                        $('#kt_modal_create_api_key_submit').hide();
                    }
                    if(!$response.login && $response.modalStatus){
                        Swal.fire({

                            title: $response.title,
                            text: $response.message,
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then((result) => {
                            // Reload the Page
                              // Modal close
                            if ($response.modalClose){
                                if ($response.modalId != "") {
                                    $('#'+$response.modalId).modal('hide');
                                }
                            }
                            if($response.redirect){
                                if($response.modal){
                                    location.reload();
                                }
                            }
                        });
                    }
                }else{
                    if(!$response.login && $response.modalStatus){
                        Swal.fire({
                            title: $response.title,
                            text: $response.message,
                            icon: "success",
                            buttonsStyling: !1,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then((result) => {
                              // Modal close
                            if ($response.modalClose){
                                if ($response.modalId != "") {
                                    $('#'+$response.modalId).modal('hide');
                                }
                            }
                            if($response.redirect != true){
                                // window.location.href=$response.redirect;
                                setTimeout(function(){
                                    window.location.href = $response.redirect;
                                },2000);
                            } else {
                                // Reload the Page
                                location.reload();
                            }
                        });
                    }
                }

                if($response.cartmessage){
                    $("."+$response.classname).html($response.cartmessage);
                    setTimeout( function(){
                        $(".deliveryreponse").hide();
                    }, 1000*10);
                }
                if($response.login && $response.verifyOtp) {
                    // let str =  $('meta[name="base-url"]').attr('content') + '/' + $response.redirect;
                    // window.location.href = str.replace("http", "https");
                    window.location.replace($response.redirect);
                    return true;
                }
                if($response.hideclass){
                    $("#encrypt_user_id").val($response.data.user_id);
                    $(".deliveryreponseOtp").text($response.message);
                    $(".mobilehide").hide();
                    $(".showotpform").show();
                }

               // $('#'+formId).trigger('reset');

                /*if($response.redirect){
                    if($response.modal){
                        $($target).trigger('reset');
                        $($modal).attr('data-success',$response.redirect);
                        swal({
                            html: $response.message,
                            showLoaderOnConfirm: false,
                            showCancelButton: false,
                            showCloseButton: true,
                            showConfirmButton: true,
                            allowEscapeKey: false,
                            allowOutsideClick:false,
                            imageUrl :  false,
                            imageClass: 'success-image-popup',
                            customClass: 'success-popup-custom-class',
                            confirmButtonText: 'Ok'
                        }).then(function(isConfirm){
                            if(isConfirm){
                                if($response.redirect != true){
                                    setTimeout(function(){
                                        window.location.href = $response.redirect;
                                    },1000);
                                }
                            }
                        },function (dismiss){}).catch(swal.noop);
                    }else{
                        $.toast({
                            text: $response.message, 
                            icon: 'info',
                            showHideTransition: 'slide', 
                            allowToastClose: true, 
                            hideAfter: 3000, 
                            stack: 5, 
                            position: 'bottom-right',
                            textAlign: 'left',  
                            loader: true,  
                            loaderBg: '#9EC600',  
                            beforeShow: function () {},
                            afterShown: function () {}, 
                            beforeHide: function () {}, 
                            afterHidden: function () {}
                        });
                        if($response.redirect != true){
                            // window.location.href=$response.redirect;
                            setTimeout(function(){
                                window.location.href = $response.redirect;
                            },2000);
                        }
                    }
                }*/
            }else{

                if($response.message.length > 0 && $response.message!=='M0000'){
                    $('.messages').html($response.message);
                }
                if (Object.size($response.data) > 0) {
                    if(typeof grecaptcha != 'undefined'){
                        grecaptcha.reset();
                    }
                    show_validation_error($response.data);
                }
            }
            $this.text(clicktext).prop('disabled', false);
            $('#popup').hide();

            if($response.message_object){
                Swal.fire({
                    title: 'Oops...',
                    text: $response.message.message,
                    icon: "error",
                    buttonsStyling: !1,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then((result) => {
                    // Reload the Page
                    if($response.redirect){
                        location.reload();
                    }
                });
            }

            //calling callback function
            let cbFn = $this.data('callbackfn');
            if(cbFn){
                window[cbFn].call(undefined, $response);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) { 
            var message = "Sorry, Internal Server Error.";
            if(XMLHttpRequest.status != null) {
                if(XMLHttpRequest.status == '429' || XMLHttpRequest.status == 429) {
                    message = "Too Many Requests. Please try after some time.";
                } else if(XMLHttpRequest.status == '419' || XMLHttpRequest.status == 419) {
                    message = "CSRF token missmatch.";
                } else if(XMLHttpRequest.status == '401' || XMLHttpRequest.status == 401) {
                    message = "The login session has been expired, please login again.";
                }
            }
            Swal.fire({
            text: message,
            icon: "error",
            buttonsStyling: !1,
            confirmButtonText: "Ok, got it!",
            customClass: {
                confirmButton: "btn btn-primary"
            }
        }).then((result) => {
            // Reload the Page
            location.reload();
          });
           // alert("Status: " + textStatus);
            // alert("Error: " + errorThrown); 
            $this.text(clicktext).prop('disabled', false);
        }
    }); 
});

$(document).on('click','[data-request="ajax-api-submit"]',function(e){
    e.preventDefault();
	
    $('#popup').show();  
    $('#keydata').hide();  
    $('.alert').remove(); 
    $(".has-error").removeClass('has-error');
    $('.help-block').remove();
    var $this       = $(this);
    var clicktext   = $(this).text();
    $this.text('Pending...').prop('disabled', true);
    var $target     = $this.data('target');
    var $url        = $($target).attr('action');
    var $method     = $($target).attr('method');
    var $dataTablesReload  = $($target).attr('data-dataTables');
    var $modal      = $this.data('modal');
    var $data       = new FormData($($target)[0]);
    if(!$method){ $method = 'get'; }
    $.ajax({
        url: $url, 
        data: $data,
        cache: false, 
        type: $method, 
        dataType: 'json',
        contentType: false, 
        processData: false,
        success: function($response){
            if($dataTablesReload != null && $dataTablesReload != 'NAN' && $dataTablesReload != 'undefined'){
                reloadDatatable($dataTablesReload);
            }
            if ($response.status === true) {
                Swal.fire({
                    title: $response.title,
                    text: $response.message,
                    icon: "success",
                    buttonsStyling: !1,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then((result) => {
                    // Modal close
                    if ($response.modalClose){
                        if ($response.modalId != "") {
                            $('#'+$response.modalId).modal('hide');
                        }
                    }
                    // Reload the Page
                    if($response.redirect){
                        if($response.modal){
                            location.reload();
                        }
                    }
                });
            }else{

                if($response.message.length > 0 && $response.message!=='M0000'){
                    $('.messages').html($response.message);
                }
                if (Object.size($response.data) > 0) {
                    show_validation_error($response.data);
                }
            }
            $this.text(clicktext).prop('disabled', false);
            $('#popup').hide();

            if($response.message_object){
                Swal.fire({
                   
                    title: 'Oops...',
  
                    text: $response.message.message,
                    icon: "error",
                    buttonsStyling: !1,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then((result) => {
                    // Reload the Page
                    location.reload();
                  });
          }
           
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) { 
            var message = "Sorry, Internal Server Error.";
            if(XMLHttpRequest.status != null) {
                if(XMLHttpRequest.status == '429' || XMLHttpRequest.status == 429) {
                    message = "Too Many Requests. Please try after some time.";
                }
            }
            Swal.fire({
            text: message,
            icon: "error",
            buttonsStyling: !1,
            confirmButtonText: "Ok, got it!",
            customClass: {
                confirmButton: "btn btn-primary"
            }
        }).then((result) => {
            // Reload the Page
            location.reload();
          });
           // alert("Status: " + textStatus);
            // alert("Error: " + errorThrown); 
            $this.text(clicktext).prop('disabled', false);
        }
    }); 
});

$(document).on('click','[data-request="ajax-confirm"]',function(){
    $('.alert').remove(); $(".has-error").removeClass('has-error');$('.error-message').remove();
    var $this       = $(this);
    var $url        = $this.data('url');
    var $ask        = $this.data('ask');
    var $askImage   = $this.data('ask_image');
    swal({
        html: $ask,
        showLoaderOnConfirm: true, 
        showCancelButton: true, 
        showCloseButton: true, 
        allowEscapeKey: false, 
        allowOutsideClick:false, 
        imageUrl : false,
        imageClass: 'ask-image-popup',        
        confirmButtonText: "YES, SURE", 
        cancelButtonText: 'NOT NOW', 
        confirmButtonColor: '#0FA1A8', 
        cancelButtonColor: '#CFCFCF',
        preConfirm: function (res) {
            return new Promise(function (resolve, reject) {
                if (res === true) {
                    $.ajax({
                        method: "POST",
                        url: $url,
                    })
                    .done(function($response) {
					
                        if($response.status == true){
                            if(typeof LaravelDataTables !== 'undefined'){
                                LaravelDataTables["dataTableBuilder"].draw();
                            }
                            if($response.message){
                                // $('.content').prepend($response.message);
                                if($('.alert').length > 0){
                                    $('html, body').animate({
                                        scrollTop: ($('.alert').offset().top-100)
                                    }, 200);
                                }
                            }
                            // if($response.redirect != true){
                            if($response.redirect === false){
                                $tableinstance = $('#dataTableBuilder').DataTable();
                                $tableinstance.draw(false);
                            }else if($response.redirect != true){
                                location.reload();
                            }else if($response.redirect === true){
                                location.reload();
                            }else if($($response.redirect).length > 0){
                                $($response.redirect).remove();
                            }
                            resolve();              
                        }
                    });
                }
            })
        }
    }).then(function(isConfirm){},function (dismiss){}).catch(swal.noop);
});

function show_validation_error(msg) {
    if ($.isPlainObject(msg)) {
        $data = msg;
    }else {
        $data = $.parseJSON(msg);
    }
    
    $.each($data, function (index, value) {
        var name    = index.replace(/\./g, '][');
        
        if (index.indexOf('.') !== -1) {
            name = name + ']';
            name = name.replace(']', '');
        }
        if (name.indexOf('[]') !== -1) {
            $('form [name="' + name + '"]').last().closest('').addClass('has-error');
            $('form [name="' + name + '"]').last().closest('.form-group').find('').append('<span class="help-block">'+ value +'</span>');
        }else if($('form [name="' + name + '[]"]').length > 0){
            $('form [name="' + name + '[]"]').closest('.form-group').addClass('has-error');
            $('form [name="' + name + '[]"]').parent().after('<span class="help-block">'+ value +'</span>');
        }else{
            if($('form [name="' + name + '"]').attr('type') == 'checkbox' || $('form [name="' + name + '"]').attr('type') == 'radio'){
                if($('form [name="' + name + '"]').attr('type') == 'checkbox'){
                    $('form [name="' + name + '"]').closest('.form-group').addClass('has-error');
                    $('form [name="' + name + '"]').parent().after('<span class="help-block">'+ value +'</span>');
                }else{
                    $('form [name="' + name + '"]').closest('.form-group').addClass('has-error');
                    $('form [name="' + name + '"]').parent().parent().append('<span class="help-block">'+ value +'</span>');
                }
            }else if($('form [name="' + name + '"]').get(0)){
                if($('form [name="' + name + '"]').get(0).tagName == 'SELECT'){
                    $('form [name="' + name + '"]').closest('.form-group').addClass('has-error');
                    if($('form [name="' + name + '"]').parent().hasClass('form-group'))
                        $('form [name="' + name + '"]').parent().after('<span class="help-block">'+ value +'</span>');
                    else
                        $('form [name="' + name + '"]').after('<span class="help-block">'+ value +'</span>');
                }else if($('form [name="' + name + '"]').attr('type') == 'password' && $('form [name="' + name + '"]').hasClass('hideShowPassword-field')){
                    $('form [name="' + name + '"]').closest('.form-group').addClass('has-error');
                    $('form [name="' + name + '"]').parent().after('<span class="help-block">'+ value +'</span>');
                }else{
                    $('form [name="' + name + '"]').closest('.form-group').addClass('has-error');
                    $('form [name="' + name + '"]').after('<span class="help-block">'+ value +'</span>');
                }
            }else{
                $('form [name="' + name + '"]').closest('.form-group').addClass('has-error');
                $('form [name="' + name + '"]').after('<span class="help-block">'+ value +'</span>');
            }
        }
        // $('.error-message').html($('.error-message').text().replace(".,",". "));
    });
    /*SCROLLING TO THE INPUT BOX*/
    scroll();
}

function scroll() {
    if ($(".help-block").not('.modal .help-block').length > 0) {
        $('html, body').animate({
            scrollTop: ($(".help-block").offset().top - 100)
        }, 200);
    }
}

function strip_html_tags(str){
    if ((str===null) || (str==='')){
        return false;
    }else{
        str = str.toString();
    }
    return str.replace(/<[^>]*>/g, '');
}

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

$(document).on("click",'[data-click="editproject"]',function(e) {
    e.preventDefault();
    var action  = $(this).data('href');
    var method  = $(this).data('method');
    var popup_target =  $(this).data('click');
    $.ajax({
        url:action,
        method:method,
        data:{"_token": "{{ csrf_token() }}"},
        success : function(data) {
            var obj = JSON.parse(data);
            $(".myform").html(obj);
            $('#'+popup_target).modal('show');
            $("#datepicker").datepicker();   
        }
    });
});

function resetform($target){
    $("#"+$target).trigger("reset");
    scroll();
}

$(document).on('click','[data-request="reset-form"]',function(e){
    var $target = $(this).data('targetform');
    resetform($target)
});

$('[data-request="select"]').select2();

    $(".toggle-password").click(function() {

        $(this).toggleClass("fa-eye fa-eye-slash");
        var input = $($(this).attr("toggle"));
        if (input.attr("type") == "password") {
        input.attr("type", "text");
        } else {
        input.attr("type", "password");
        }
    });

    function copySecretCode() {
        var copyText = document.getElementById("secret_data");

        copyText.select();

        document.execCommand("copy");


    }

    function themeColorChange($url,$user_id){
        $.ajax({
            url:$url,
            type: "post",
            data: {'user_id':$user_id},
            success: function(data){

            }
        });
    }

    function reloadDatatable(datatableId) {
        $("#" +datatableId).DataTable().ajax.reload();
    }

    function showSpan($status, capitalize = 'yes')
    {
        var label ="";
        var $html = "";
        if (['Active','Success','Processed','Credit','Cr','CR','Settled','Verified','srv_1626077095'].includes($status.capitalize())) {
            $label = "badge-success";
        } else if (['Failed','Cancelled','Rejected','Cancel','Debit','Dr','DR','srv_1626077390', 'Suspended', 'Blocked'].includes($status.capitalize())) {

            $label = "badge-danger";
        } else if (['Pending','Progress','Processing','InActive', 'Inactive', 'Unsettled','srv_1626344088', 'Initiate'].includes($status.capitalize())) {
            $label = "badge-warning";
        }else if (['Queued','srv_1626077505'].includes($status.capitalize())) {
            $label = "badge-primary";
        }else if (['Reversed','Disputed'].includes($status.capitalize())) {
            $label = "badge badge-dark";
        }else if (['Hold'].includes($status.capitalize())) {
            $label = "badge-info";
        }else{
            $label = "badge-default";
        }
        if(capitalize == 'yes'){
            $html = '<span class="badge '+$label+'">'+$status.capitalize()+'</span>';
        } else {
            $html = '<span class="badge '+$label+'">'+$status+'</span>';
        }
        return $html;
    }

    String.prototype.capitalize = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    }

    String.prototype.format = function() {
        var formatted = this;
        for( var arg in arguments ) {
            formatted = formatted.replace("{" + arg + "}", arguments[arg]);
        }
        return formatted;
    }
    function numberWithCommas(x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }

    function changeNumberFormat(val) {
        if(val >= 10000000) val = (val/10000000).toFixed(2) + ' Cr';
        else if(val >= 100000) val = (val/100000).toFixed(2) + ' Lac';
        else if(val >= 1000) val = (val/1000).toFixed(2) + ' K';
        return val;
    }

function statusSpan(status) {
    if (status == 'pending')
        return `<span class="badge badge-danger">Pending</span>`;
    else if (status == 'approved')
        return `<span class="badge badge-success">Approved</span>`;
    else if (status == 'rejected')
        return `<span class="badge badge-dark">Rejected</span>`;
}

function copyText2Clipboard(clickEle, targetEle) {
    /* Get the text field */
    var copyText = document.getElementById(targetEle);

    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */

    /* Copy the text inside the text field */
    navigator.clipboard.writeText(copyText.value);

    var clickEleText = document.getElementById(clickEle).innerHTML;
    document.getElementById(clickEle).innerHTML = `<i class='fa fa-spin fa-spinner'></i>`;
    // document.getElementById(clickEle).innerHTML = '<i class="far fa-check-circle"></i>';

    setTimeout(() => {
        document.getElementById(clickEle).innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            document.getElementById(clickEle).innerHTML = clickEleText;
        }, 800);
    }, 500);
}