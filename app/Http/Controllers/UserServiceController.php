<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\UserService;
use DataTables;
use Auth;
class UserServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = UserService::leftJoin('services','services.id','user_services.service_id')
            ->select('*','user_services.created_at')
            ->where('user_services.is_active','1')
            ->where('user_services.user_id',Auth::user()->id)->get();
         
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->editColumn('created_at',function ($row){
                        return date('d-m-Y', strtotime($row['created_at']));
                    })
                    ->make(true);
        }
       
    }
}
