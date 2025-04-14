<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Integration;
use App\Models\Service;
use App\Models\UPICollect;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class IntegrationController extends Controller
{
    public function index()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $data['page_title'] =  "Integration List";
            $data['site_title'] =  "Integration List";
            $data['view']       = ADMIN . '.integration';
            $data['services'] = DB::table('integrations')->get();

            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function updateServiceActivation(Request $request, $id)
    {
        try{
            if (Auth::user()->hasRole('super-admin')) {
                $integration = Integration::find($id);
                if (!$integration) {
                    $messages = 'Integration not found';

                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = $messages;
                    $this->title = "Integration";
                    $this->redirect = true;
                    return $this->populateresponse();
                }

                    $currentStatus = $integration->is_active;
                    $newStatus = $currentStatus == '1' ? '0' : '1';

                    $integration->is_active = $newStatus;
                    $integration->save();

                    $messages = 'Integration activation status updated successfully';

                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = $messages;
                    $this->title = "Integration";
                    $this->redirect = true;
                    return $this->populateresponse();
                } else {
                    return abort(404);
                }
        } catch (Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            $this->redirect = false;
            return $this->populateresponse();
        }
    }


    public function addIntegration(Request $request)
	{
		$validator = Validator::make($request->all(),[
			'name' => 'required',
			'slug' => 'required',
		]);

		if($validator->fails())
		{
			$message = json_decode(json_encode($validator->errors()),true);
			return ResponseHelper::missing('Some params are missing',$message);
		}
		    {
				DB::table('integrations')->insert([

					'name' => $request->name,
					'slug' => $request->slug,
					'integration_id' => "int_" . rand(111111111, 999999999),
					'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
				]);

				$messages = 'Record inserted successfully';

			$this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message = $messages;
            $this->title = "Integration";
            $this->redirect = true;
            return $this->populateresponse();
		}
	}

    public function getservices()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $data['page_title'] =  "Services";
            $data['site_title'] =  "Services";
            $data['view']       = ADMIN . '.service';
            $data['services'] = DB::table('global_services')
                ->select('service_id', 'service_name')
                ->orderBy('service_name', 'ASC')
                ->get();
                
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }
    public function statusActions(Request $request, $id)
    {
        try{
            if (Auth::user()->hasRole('super-admin')) {
                $service = Service::find($id);
                if (!$service) {
                    $messages = 'Service not found';

                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = $messages;
                    $this->title = "Service";
                    $this->redirect = true;
                    return $this->populateresponse();
                }

                    $currentStatus = $service->is_active;
                    $newStatus = $currentStatus == '1' ? '0' : '1';

                    $service->is_active = $newStatus;
                    $service->save();

                    $messages = 'Service activation status updated successfully';

                    $this->status = true;
                    $this->modal = true;
                    $this->alert = true;
                    $this->message = $messages;
                    $this->title = "Service";
                    $this->redirect = true;
                    return $this->populateresponse();
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            $this->status_code = '100';
            $this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message_object = true;
            $this->message  = array('message' => "Error: " . $e->getMessage());
            $this->redirect = false;
            return $this->populateresponse();
        }
    }

    public function getactivitylog()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $data['page_title'] =  "Activity Logs";
            $data['site_title'] =  "Activity Logs";
            $data['view']       = ADMIN . '.activitylogs';
            $data['activity_logs'] = DB::table('activity_logs')->get();
                
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function integrationValue()
    {
        if (Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('support')) {
            $data['page_title'] =  "Integration Volume";
            $data['site_title'] =  "Integration Volume";
            $data['view']       = ADMIN . '.integration_volume';
            $data['integrations'] = DB::table('integrations')->select(DB::raw('integration_id, name'))->get();
            $data['userData'] = DB::table('users')->select(DB::raw('id,concat(name," (",email,")") as userName'))->where('is_admin', '0')->get();
            return view($data['view'])->with($data);
        } else {
            $data['url'] = url('admin/dashboard');
            return view('errors.401')->with($data);
        }
    }

    public function viewPipeTxn(Request $request) {
            $integrationsQuery = DB::table('integrations');
        
            if ($request->has('integration_id') && $request->integration_id) {
                $integrationsQuery->where('integration_id', $request->integration_id);
            }
        
            $integrations = $integrationsQuery->get();
            $pipeData = [];
            foreach ($integrations as $integration) {
                $integration_id = $integration->integration_id;
                $upiCollectStatuses = ['success', 'pending', 'rejected'];
                $orderStatuses = ['hold', 'processing', 'cancelled', 'reversed', 'failed'];
        
                $upiCollectAmounts = [];
        
                foreach ($upiCollectStatuses as $status) {
                    $upiCollectAmounts[$status] = UpiCollect::where('status', $status)->where('integration_id', $integration_id)
                        ->when($request->has('user_id') && $request->user_id, function ($query) use ($request) {
                            return $query->where('user_id', $request->user_id);
                        })->when($request->has('from') && $request->from && $request->has('to') && $request->to, function ($query) use ($request) {
                            return $query->whereBetween('created_at', [$request->from, $request->to]);
                        })->sum('amount');
                }

                $payoutAmounts = [];
                foreach ($orderStatuses as $status) {
                    $payoutAmounts[$status] = Order::where('status', $status)
                        ->where('integration_id', $integration_id)
                        ->when($request->has('user_id') && $request->user_id, function ($query) use ($request) {
                            return $query->where('user_id', $request->user_id);
                        })->when($request->has('from') && $request->from && $request->has('to') && $request->to, function ($query) use ($request) {
                            $toDate = Carbon::parse($request->to)->endOfDay();
                            return $query->whereBetween('created_at', [$request->from, $toDate]);
                        })->sum('amount');
                }
    
                $pipeData[] = [
                    'id' => $integration->id,
                    'name' => $integration->name,
                    'integration_id' => $integration_id,
                    'payin' => $upiCollectAmounts,
                    'payout' => $payoutAmounts,
                ];
            }
            $totalRecords = count($pipeData);
            $filteredRecords = $totalRecords;

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $pipeData
            ]);

            // $allintegrations = DB::table('integrations')->get();
            // return view(ADMIN . '.integration_volume', ['data' => $pipeData, 'users' => $user,'integrations'=>$allintegrations]);
        // return response()->json(['data' => $pipeData]);
    }
}