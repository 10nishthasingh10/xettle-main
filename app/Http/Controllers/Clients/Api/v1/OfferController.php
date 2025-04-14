<?php

namespace App\Http\Controllers\Clients\Api\v1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CommonHelper;
use App\Helpers\UATResponse;
use App\Helpers\ResponseHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    
    /**
     * Client Id Variable
     *
     * @var string
     */
    protected $key;

    /**
     *  Client Secret Variable
     * @var string
     */
    protected $secret;

    /**
     * Base Url Variable
     * @var string
     */
    protected $baseUrl;

    /**
     * header variable
     *
     * @var [array]
     */
    protected $header;

    /**
     * construct function init Client Key,Client Secret and Base Url
     */
    public function __construct()
    {
        
    }

    public function offerList(Request $request)
    {
        if(!empty($request->user_id))
        {
            $user_id = '"'.$request->user_id.'"';
            $offers = DB::table('offers')->select('offers.*',DB::raw("DATE_FORMAT(expired_at, '%d, %b') as expiry_date"),'offer_category.title as category_name',DB::raw('concat(track_url,'.$user_id.') as short_link'))->join('offer_category','offer_category.id','=','offers.category_id')->where('offers.status','1')->whereDate('offers.expired_at','>=',date('Y-m-d'))->get();

            if($offers->isNotEmpty())
            {
                return ResponseHelper::success('Record fetched successfully',$offers);
            }
        }
        

    	return ResponseHelper::failed('Record not found');

    }


}
