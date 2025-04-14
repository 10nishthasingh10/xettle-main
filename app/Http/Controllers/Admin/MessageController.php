<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\User;
use App\Jobs\SendTransactionEmailJob;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;

class MessageController extends Controller
{
	public function getList()
	{
		$data['page_title'] =  "Message List";
        $data['site_title'] =  "Message List";
        $data['view']       = ADMIN . '/' . ".Messages.message_list";
        $data['userData']   = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
        return view($data['view'])->with($data);
	}

	public function addMessage(Request $request)
	{
		$validator = Validator::make(
                $request->all(),
                [
                    'subject' => "required",
                    'message' => "required",
                    
                ],
                [
                    'required' => ""
                ]
            );


            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }
		$subject = $request->subject;
		$message = $request->message;
		$messages = new Message();
		$messages->subject = $subject;
		$messages->message = $message;
		$messages->created_at = date('Y-m-d H:i:s');
		$messages->save();
		$this->status = true;
        $this->modal = true;
        $this->alert = true;
        $this->message = "Message added Successfully";
        $this->title = "Add Message";
        $this->redirect = true;
        return $this->populateresponse();

	}

	public function sendEmail(Request $request)
	{
		$validator = Validator::make(
                $request->all(),
                [
                    'userId' => "required",
                ],
                [
                    'required' => ""
                ]
            );


            if ($validator->fails()) {
                $message = json_decode(json_encode($validator->errors()), true);
                return ResponseHelper::missing('Some params are missing.', $message);
            }

		$userIds = $request->userId;
		$message_id = $request->message_id;
		//$userIds = explode(',',$userIds);
		if($message_id)
		{
			$messages = Message::where('id',$message_id)->first();
		}
		if($userIds[0]==0)
		{	
			$users = User::select('name','email')->where('is_admin','0')->where('is_active','1')->get();
		}else
		{	
			$users = User::select('name','email')->whereIn('id',$userIds)->get();
			
		}

		foreach($users as $user)
		{
			$data['email']=$user->email;
			$data['name'] = $user->name;
			$data['message_subject'] = $messages->subject;
			$data['message'] = $messages->message;
			//print_r($data);
			dispatch(new SendTransactionEmailJob($data,'sendMessage'));
			
		}
			$this->status = true;
	        $this->modal = true;
	        $this->alert = true;
	        $this->message = "Mail sent Successfully";
	        $this->title = "Sent Mail";
	        $this->redirect = true;
	        return $this->populateresponse();
	}

	public function deleteMessage(Request $request,$id)
	{
		if($id)
		{
			Message::where('id',$id)->delete();
			$this->status = true;
	        $this->modal = true;
	        $this->alert = true;
	        $this->message = "Message deleted Successfully";
	        $this->title = "Message";
	        $this->redirect = true;
	        return $this->populateresponse();
		}
	}

	public function viewMessage(Request $request,$id)
	{
		if($id)
		{
			$message = Message::where('id',$id)->first();
			$data['view']       = "emails.sendMessage";
			$data['message'] = $message->message;
			$data['name'] = 'User Name';
			$view = view($data['view'])->with($data);
			//$view=\View::make::(ADMIN . '/' . ".emails.sendMessage",compact($message));
    		return ['html'=>$view->render()];
		}
		
	}
}