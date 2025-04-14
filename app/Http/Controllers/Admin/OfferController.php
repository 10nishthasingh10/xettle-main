<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;
use App\Models\Offer;

class OfferController extends Controller
{
	public function index()
	{
		$data['page_title'] = 'Offers';
		$data['site_title'] =  "Offer List";
		$data['view'] = 'admin/offers/offer';
		$data['categoryList'] = DB::table('offer_category')->where('status','1')->get();
		return view($data['view'],$data);
	}

	public function addOffer(Request $request )
	{
		$required = (empty($request->id)?'required|':'');
		$validator = Validator::make($request->all(),[

			'title' =>'required',
			'category_id' => 'required',
			'short_description' => 'required',
			'shared_description' => 'required',
			'long_description' => 'required',
			'offer_logo' => $required.'image|mimes:jpg,jpeg,png',
			'desc_image' => $required.'image|mimes:jpg,jpeg,png',
			'offer_link' => 'required',
			'track_url' => 'required',
			'expired_at' => 'required',
			'status' => 'required'
			
		]);

		if($validator->fails())
		{
			$message = json_decode(json_encode($validator->errors()),true);
			return ResponseHelper::missing('Some params are missing',$message);
		}else
		{
			$input_fields='';
			
			if($request->file('offer_logo'))
			{
				$file = $request->file('offer_logo');
				$imageExtension = $request->offer_logo->getClientOriginalExtension();
                $filename = time().'.'.$imageExtension;
                $path = $request->offer_logo->move(public_path('/upload/offers'), $filename);
                
			}
			if($request->file('shared_image'))
			{
				$file = $request->file('shared_image');
				$imageExtension = $request->shared_image->getClientOriginalExtension();
                $shared_filename = time().'.'.$imageExtension;
                $path = $request->shared_image->move(public_path('/upload/offers'), $shared_filename);
                
			}
			if($request->file('desc_image'))
			{
				$file = $request->file('desc_image');
				$imageExtension = $request->desc_image->getClientOriginalExtension();
                $desc_image = time().'.'.$imageExtension;
                $path = $request->desc_image->move(public_path('/upload/offers'), $desc_image);
                
			}

			if(!empty($request->pincode) && $request->pincode == '1')
			{
				$input_fields = 'pincode';

			}
			if(!empty($request->id))
			{
				$banner = Offer::where('id',$request->id)->first();
				$banner->title = $request->title;
				$banner->category_id = $request->category_id;
				$banner->button_text = $request->button_text;
				if(!empty($filename))
				{
					$banner->offer_logo = asset('upload/offers').'/'.$filename;
				}
				if(!empty($shared_filename))
				{
					$banner->shared_image = asset('upload/offers').'/'.$shared_filename;
				}
				if(!empty($desc_image))
				{
					$banner->desc_image = asset('upload/offers').'/'.$desc_image;
				}
				$banner->short_description = $request->short_description;
				$banner->shared_description = $request->shared_description;
				$banner->description = $request->long_description;
				$banner->offer_link = $request->offer_link;
				$banner->track_url = $request->track_url;
				$banner->expired_at = $request->expired_at;
				$banner->status = $request->status;
				$banner->input_fields = !empty($banner->input_fields)?$banner->input_fields.','.$input_fields:$input_fields;
				$banner->updated_at = date('Y-m-d H:i:s');
				$banner->save();
				
				$msg = 'Offer updated successfully.';
			}else
			{
				DB::table('offers')->insert([
					'title' => $request->title,
					'category_id' => $request->category_id,
					'button_text' => $request->button_text,
					'offer_id' => 'XTL'.time(),
					'short_description' => $request->short_description,
					'shared_description' => $request->shared_description,
					'description' => $request->long_description,
					'offer_logo' => !empty($filename)?asset('upload/offers').'/'.$filename:'',
					'shared_image' => !empty($shared_filename)?asset('upload/offers').'/'.$shared_filename:'',
					'desc_image' => !empty($desc_image)?asset('upload/offers').'/'.$desc_image:'',
					'offer_link' => $request->offer_link,
					'track_url' => $request->track_url,
					'expired_at' => $request->expired_at,
					'input_fields' => $input_fields,
					'status' => $request->status,
					'created_at' => date('Y-m-d H:i:s')
				]);
				$msg = 'Offer inserted successfully.';
			}
			
			
			$this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message = $msg;
            $this->title = "Offers";
            $this->redirect = true;
            return $this->populateresponse();

		}
	}

	public function getOffer(Request $request,$id)
	{
		if($id)
		{
			$banner = DB::table('offers')->where('id',$id)->first();
			if(!empty($banner))
			{
				return ResponseHelper::success('Record fetched successfully',$banner);
			}
			return ResponseHelper::failed('Record not found');
		}
	}

	public function categoryList()
	{
		$data['page_title'] = 'Category';
		$data['site_title'] =  "Category List";
		$data['view'] = 'admin/offers/category';
		return view($data['view'],$data);
	}

	public function addCategory(Request $request)
	{
		$required = (empty($request->id)?'required|':'');
		$validator = Validator::make($request->all(),[
			'title' => 'required',
			'logo' => $required.'image|mimes:jpg,jpeg,png',
			'description' => 'required',
			'status' => 'required'
		]);

		if($validator->fails())
		{
			$message = json_decode(json_encode($validator->errors()),true);
			return ResponseHelper::missing('Some params are missing',$message);
		}
		else
		{
			if($request->file('logo'))
			{
				$file = $request->file('logo');
				$imageExtension = $request->logo->getClientOriginalExtension();
                $filename = time().'.'.$imageExtension;
                $path = $request->logo->move(public_path('/upload/category'), $filename);
                
			}
			if(!empty($request->id))
			{
				$update_array = [

					'title' => $request->title,
					
					'description' => $request->description,
					'status' => $request->status,
					'updated_at' => date('Y-m-d H:i:s')
				];
				if(!empty($filename))
				{
					$update_array['logo'] = asset('upload/category').'/'.$filename;
				}
				DB::table('offer_category')->where('id',$request->id)->update($update_array);
				$messages = 'Record updated successfully';
			}
			else
			{
				DB::table('offer_category')->insert([

					'title' => $request->title,
					'logo' => (!empty($filename)?asset('upload/category').'/'.$filename:''),
					'description' => $request->description,
					'status' => $request->status,
					'created_at' => date('Y-m-d H:i:s')
				]);

				$messages = 'Record inserted successfully';
			}

			$this->status = true;
            $this->modal = true;
            $this->alert = true;
            $this->message = $messages;
            $this->title = "Category";
            $this->redirect = true;
            return $this->populateresponse();
		}
	}

	public function getOfferCategory(Request $request,$id)
	{
		if($id)
		{
			$banner = DB::table('offer_category')->where('id',$id)->first();
			if(!empty($banner))
			{
				return ResponseHelper::success('Record fetched successfully',$banner);
			}
			return ResponseHelper::failed('Record not found');
		}
	}
}