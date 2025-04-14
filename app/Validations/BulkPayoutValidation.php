<?php 
namespace Validations;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;


class BulkPayoutValidation{
	protected $data;
	public function __construct($data){
		$this->data = $data;
	}

	public function batchCancel()
	{
		$validate = Validator::make($this->data->all(),[
			'userId'       => ['required'],
			'batchId'       => ['required'],
            'remarks'       => ['required'],
		]);
		return $validate;
	}
}


?>