<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Perks\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $modalStatus;
    protected $status;
    protected $jsondata;
    protected $status_code;
    protected $error_code;
    protected $ajax;
    protected $redirect;
    protected $modal;
    protected $successimage;
    protected $alert;
    protected $message;
    protected $prefix;
    protected $login;
    protected $verifyOtp;
    protected $title;
    protected $modalClose;
    protected $modalId;
    protected $classname;
    protected $hideclass;
    protected $language;
    protected $message_object;
    protected  const SUCCESS_STATUS="SUCCESS";
    protected  const FAILED_STATUS="FAILED";
    protected  const ERROR_STATUS="ERROR";
    protected  const PENDING_STATUS="PENDING";
    public function __construct(Request $request){
        $this->modalStatus      = true;
        $this->jsondata         = (object)[];
        $this->message          = "";
        $this->error_code       = "no_error_found";
        $this->status           = false;
        $this->status_code      = 200;
        $this->redirect         = false;
        $this->verifyOtp        = false;
        $this->title            = 'Good job!';
        $this->modal            = false;
        $this->alert            = false;
        $this->successimage     = asset('images/success.gif');
        $this->ajax             = 'api';
        $this->cartmessage      ="";
        $this->classname        = "";
        $this->hideclass        = "";
        $this->message_object   = false;
        $this->modalClose   = false;
        $this->modalId   = "";
        if($request->ajax()){
            $this->ajax = 'web';
        }

        $json = json_decode(file_get_contents('php://input'),true);
        if(!empty($json)){
            $this->post = $json;
        }else{
            $this->post = $request->all();
        }
        if($request->is('api/*')){
            /*RECORDING API REQUEST IN TABLE*/
        }
        $request->replace($this->post);
    }


    public function populate($data)
    {
     //   $data['error_code'] = (!empty($data['message']))? "" : trans(sprintf("%s",$this->message));
        $data['message']    = trans(sprintf("%s",$this->message));

        if(empty($data['status'])){
            $data['status']     = $this->status;
            //$data['error_code'] = $this->message;
        }

        //$data['status_code'] = $this->status_code;

        $data = json_decode(json_encode($data),true);

        array_walk_recursive($data, function(&$item,$key){
            if($key === 'default_card_detail'){
                $item = (object)[];
            } else if (gettype($item) == 'integer' || gettype($item) == 'float' || gettype($item) == 'NULL'){
                $item = trim($item);
            }
        });

        if(empty($data['data'])){
            $data['data'] = (object) $data['data'];
        }else{
            array_walk_recursive($data['data'], function(&$value, $key){
            });
        }
        return $data;
    }
    protected function populateresponse()
    {
        if(empty($this->status)){
            $data['status']= $this->status;
            $response      = new Response($this->message);

            if($this->ajax == 'api'){
                $data['errors'] = $response->api_error_response();
                $data = json_decode(json_encode($data),true);
                if(empty($this->jsondata)){
                    $data['data'] = (object) $this->jsondata;
                }
                $data['status_code'] = $this->status_code = 200;
                $data['title'] = $this->title;
                $data['modalClose'] = $this->modalClose;
                $data['modalStatus'] = $this->modalStatus;
                $data['modalId'] = $this->modalId;
                $data['error_code']  = $this->error_code;
                return $data;
            }else{
                $data['errors']    = $response->web_error_response();
                return ([
                    'data'         => $data['errors'],
                    'status'       => $this->status,
                    'status_code'  => $this->status_code,
                    'message'      => $this->message,
                    'modalStatus'  => $this->modalStatus,
                    'login'        => $this->login,
                    'verifyOtp'    => $this->login,
                    'title'        =>$this->title,
                    'nomessage'    => true,
                    'modal'        => $this->modal,
                    'successimage' => $this->successimage,
                    'message_object'         => $this->message_object,
                    'modalClose' => $this->modalClose,
                    'modalId' => $this->modalId
                ]);
            }
        }else{
            if($this->ajax == 'api'){
                return [
                    'status'        => $this->status,
                    'status_code'   => $this->status_code,
                    'data'          => $this->jsondata,
                    'message'       => $this->message,
                    'modalStatus'  => $this->modalStatus,
                    'title'        =>$this->title,
                    'modalClose' => $this->modalClose,
                    'modalId' => $this->modalId
                ];
            }else{
                
                return [
                    'status'        => $this->status,
                    'status_code'   => $this->status_code,
                    'redirect'      => $this->redirect,
                    'data'          => $this->jsondata,
                    'modal'         => $this->modal,
                    'successimage'  => $this->successimage,
                    'message'       => $this->message,
                    'modalStatus'  => $this->modalStatus,
                    'alert'         => $this->alert,
                    'login'      => $this->login,
                    'title'        =>$this->title,
                    'verifyOtp'      => $this->verifyOtp,
                    'message_object'         => $this->message_object,
                    'classname'    => $this->classname,
                    'hideclass'     => $this->hideclass ,
                    'modalClose' => $this->modalClose,
                    'modalId' => $this->modalId
                ];
            }
        }
    }

    protected function populateapiresponse(){
        if(empty($this->status)){
            $data['status']= $this->status;
            $response      = new Response($this->message);
            if($this->ajax == 'api'){
                $data['errors'] = $response->api_error_response();
                $data = json_decode(json_encode($data),true);
                if(empty($this->jsondata)){
                    $data['data'] = (object) $this->jsondata;
                }
                $data['status_code'] = $this->status_code = 200;
                $data['error_code']  = $this->error_code;
                return $data;
            }else{
                $data['errors']    = $response->web_error_response();
                return ([
                    'data'         => $data['errors'],
                    'status'       => $this->status,
                    'status_code'  => $this->status_code,
                    'message'      => $this->message,
                    'title'        =>$this->title,
                ]);
            }
        }else if($this->status=='ERROR'){
            $data['status']= $this->status;
            $response      = new Response($this->message);
            if($this->ajax == 'api'){
                $data['errors'] = $response->api_error_response();
                $data = json_decode(json_encode($data),true);
                if(empty($this->jsondata)){
                    $data['data'] = (object) $this->jsondata;
                }
                $data['status_code'] = $this->status_code = 200;
                $data['error_code']  = $this->error_code;
                return $data;
            }else{
                $data['errors']    = $response->web_error_response();
                return ([
                    'data'         => $data['errors'],
                    'status'       => $this->status,
                    'status_code'  => $this->status_code,
                    'message'      => $this->message,
                    'title'        =>$this->title,
                ]);
            }
        }else{
            if($this->ajax == 'api'){
                return [
                    'status'        => $this->status,
                    'status_code'   => $this->status_code,
                    'data'          => $this->jsondata,
                    'message'       => $this->message,
                    'title'        =>$this->title,
                ]; 
            }else{

                return [
                    'status'        => $this->status,
                    'status_code'   => $this->status_code,
                    'data'          => $this->jsondata,
                    'message'       => $this->message,
                    'title'        =>$this->title,
                ];
            }
        }
    }

}
