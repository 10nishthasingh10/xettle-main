<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Integration;
use App\Models\Transaction;
use Cashfree;
use App\Models\Apilog;
use Storage;
use DB;
class CronController extends Controller
{
    protected $tokens;

    public function testingQuery(Request $request)
    {
        try{
            DB::transaction(function () use($request) {
                $roleData = array(
                    "name" => $request->name,
                    "guard_name" => $request->guard_name,
                    "add_from" => "whit_trans",
                );
                $role = \App\Models\Role::create($roleData);

                $permissionData = array(
                    "role_id" => $role->role_id,
                );
                $permission = \App\Models\Permission::create($permissionData);

                $absData = array(
                    "name" => $request->name,
                    "amount" => $request->amount,
                );
                $abs = \App\Models\Abs::create($absData);
            });
            $status = true;
            $message = "Transaction Successfully";
            $messageReadbled = "";
        } catch (\Exception $e) {
            DB::rollBack();
            $status = false;
            $message = $e->getTraceAsString();
            $messageReadbled = $e->getMessage();
        }

        return response()->json(['status' => $status ,'messageReadbled' => $messageReadbled,'message' => $message]);
    }

    public function withowtTestingQuery(Request $request)
    {
                $roleData = array(
                    "name" => $request->name,
                    "add_from" => "whithout_trans",
                    "guard_name" => $request->guard_name,
                );
                $role = \App\Models\Role::create($roleData);
                $permissionData = array(
                    "role_id" =>$role->id,
                );
                $permission = \App\Models\Permission::create($permissionData);

                $absData = array(
                    "name" => $request->name,
                    "amount" => $request->amount,
                );
                $abs = \App\Models\Abs::create($absData);
            $status = true;
            $message = "Transaction Successfully";
            $messageReadbled = "";

        return response()->json(['status' => $status ,'messageReadbled' => $messageReadbled,'message' => $message ,"requsetAll" => $request->all()]);
    }

    public function testingUpdateQuery(Request $request)
    {
        try{
            DB::transaction(function () use($request) {
                $role = \App\Models\Role::find($request->role_id);
                $role->guard_name = $request->guard_name;
                $role->name = $request->name;
                $role->save();

                $permission = \App\Models\Permission::where('id',$request->permission_id)->first();
                $permission->sum_amount += $request->amount;
                $permission->save();

                $abs = \App\Models\Abs::find($request->abs_id);
                $abs->amount += $request->amount;
                $abs->name = $request->name;
                $abs->save();
            });
            $status = true;
            $message = "Transaction Successfully";
            $messageReadbled = "";
        } catch (\Exception $e) {
            DB::rollBack();
            $status = false;
            $message = $e->getTraceAsString();
            $messageReadbled = $e->getMessage();
        }

        return response()->json(['status' => $status ,'messageReadbled' => $messageReadbled,'message' => $message]);
    }

    public  function getBalance()
    {
        $cashfree = new Cashfree;
        return $cashfree->getBalance();
    }

}
