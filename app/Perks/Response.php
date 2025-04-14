<?php
namespace Perks;

/**
*
*/
class Response
{
	private $errors;
	function __construct($data){
		$this->errors 		= $data;
		$this->response 	= $data;
	}

	public function api_error_response(){
		$this->errors = json_decode(json_encode($this->errors));
		$errorData = [];
		if(!empty($this->errors)){
			$i = 0;
            foreach ($this->errors as $key => $value) {
                $errorData[$i]['key'] = $key;
                $errorData[$i]['value'] = $value[0];
                break;
            }
        }
        return ($errorData);
    }

    public function web_error_response(){

        $error_array = json_decode(json_encode($this->errors),true);
        if (isset($this->error_array) && count($this->error_array) > 0)
        return (object)[array_keys($error_array)[0] => [current($error_array)[0]]];
        return false;

	}


    public function json_change($array, $newkey, $oldkey) {
        $json = str_replace('"'.$oldkey.'":', '"'.$newkey.'":', json_encode($array));
        return json_decode($json,true);
    }
}

?>

