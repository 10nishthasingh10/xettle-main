<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
class ResellerController extends Controller
{
    public function index()
    {
        $data['page_title'] =  "All Reseller";
        $data['site_title'] =  "All Reseller";
        $data['view']       = ADMIN . '/' . ".reseller";
        $data['dateFrom']   = date('Y-m-d');
        $data['dateTo']     = date('Y-m-d');
        $data['userData']   = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();

        return view($data['view'])->with($data);
    }

    public function addReseller(Request $request)
	{
		$validator = Validator::make($request->all(),[
			'name' => 'required',
			'email' => 'required|email',
            'password' => 'required',
            'status' => 'required',
		]);

		if($validator->fails())
		{
			$message = json_decode(json_encode($validator->errors()),true);
			return ResponseHelper::missing('Some params are missing',$message);
		}
            DB::table('resellers')->insert([

                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $request->status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $messages = 'Record inserted successfully';

        $this->status = true;
        $this->modal = true;
        $this->alert = true;
        $this->message = $messages;
        $this->title = "Reseller";
        $this->redirect = true;
        return $this->populateresponse();
	}

    public function resellerDetails(Request $request, $id)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $data['page_title'] = "User Details";
            $data['site_title'] = "User Details";
            $reseller = Reseller::find($id);
            $data['reseller_id'] = $reseller ? $reseller->id : null;
            $data['user_id'] = $reseller ? User::where('reseller', $reseller->id)->pluck('id')->toArray() : [];

            $data['userData']   = DB::table('users')->select(DB::raw('id, name as userName'))->where('is_admin', '0')->get();
            
            return view(ADMIN . '/' . ".userDetails", $data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

public function userRecords(Request $request, $id)
{
    $reseller = Reseller::findOrFail($id);
    $query = User::where('reseller', $reseller->id);
    if ($request->filled('searchText')) {
        $searchText = $request->input('searchText');
        $query->where(function ($q) use ($searchText) {
            $q->where('name', 'like', '%' . $searchText . '%')
            ->orWhere('email', 'like', '%' . $searchText . '%')
            ->orWhere('mobile', 'like', '%' . $searchText . '%');
        });
    }

    if ($request->filled('user_id')) {
        $userId = $request->input('user_id');
        $query->where('id', $userId);
    }

    if ($request->filled('from') && $request->filled('to')) {
        $from = $request->input('from');
        $to = $request->input('to');
        $query->whereBetween('created_at', [$from, $to]);
    }

    // $users = $query->get(['id', 'name', 'email', 'mobile', 'is_active', 'created_at']);
    $users = $query->select('id', 'name', 'email', 'mobile', 'is_active', DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_date"))->get();

    $totalRecords = $users->count();
    $filteredRecords = $totalRecords;

    return response()->json([
        'draw' =>$request->input('draw'),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $users
    ]);
}


public function addResellerCommission(Request $request)
{
    
    $validator = Validator::make($request->all(),[
        'payin_rate' => 'required',
        'payout_rate' => 'required',
        'minimum_payinAmount' => 'required',
        'minimum_payoutAmount' => 'required',
    ]);

    if($validator->fails())
    {
        $message = json_decode(json_encode($validator->errors()),true);
        return ResponseHelper::missing('Some params are missing',$message);
    }
        DB::table('reseller_commission')->insert([
            'user_id'  => $request->user_id,
            'reseller_id' => $request->reseller_id,
            'payin_rate' => $request->payin_rate,
            'payout_rate' => $request->payout_rate,
            'minimum_payinAmount' => $request->minimum_payinAmount,
            'minimum_payoutAmount' => $request->minimum_payoutAmount,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $messages = 'Record inserted successfully';

    $this->status = true;
    $this->modal = true;
    $this->alert = true;
    $this->message = $messages;
    $this->title = "Reseller Commission";
    $this->redirect = true;
    return $this->populateresponse();
}


}
