<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validations\OrderValidation as Validations;
use App\Models\ProductCommission;
use App\Models\Order;
use App\Models\Product;

use Cashfree;
class OrderController extends Controller
{

    public function index()
    {
   
        $Order = Order::listing('array','*',"",'orders.id-asc');
        $this->message="Record found successfull";
        
        $this->data=['orders'=>$Order];
    
        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => $this::SUCCESS_STATUS,
                'data'      => $this->data
            ])
        );

    }

    public function store(Request $request)
    {
        dd("345");
        $validation=new Validations($request);
        $validator=$validation->addOrder();
        if($validator->fails()){
            $this->message=$validator->errors();
           
        }else{
           
            $commission_amount=0;
            $ord='ord_'.Cashfree::generateRandomString(12);
            $product =Product::where('product_id',$request->product_id)->first();
            
            $slug=$product->slug;
            
            switch ($slug) {
               case "settlx_basic":
                if($request->amount >=$product->min_order_value && $request->amount <=$product->max_order_value){
                    $ProductCommission =ProductCommission::where('product_id',$product->id)->first();
                    if($ProductCommission->type=='fixed'){
                        // fee call
                        $commission_amount=$ProductCommission->amount;
                    }else{
                        $commission_amount=($request->amount*$ProductCommission->amount/100);
                    }
                    
                } 

                break;
                case "settlx_basic1":
                    if($request->amount >=$product->min_order_value && $request->amount <=$product->max_order_value){
                        $ProductCommission =ProductCommission::where('product_id',$product->id)->first();
                        if($ProductCommission->type=='fixed'){
                            $commission_amount=$ProductCommission->amount;
                        }else{
                            $commission_amount=($request->amount*$ProductCommission->amount/100);
                        }
                        
                    } 
    
                    break;
            }
            $Order= new Order;
            $Order->currency=$request->currency;
            $Order->amount=$request->amount;
            $Order->product_id=$request->product_id;
            $Order->order_id=$ord;
            $Order->integration_id=$request->integration_id;
            
           // $Order->commission=$commission_amount;
            $Order->tax=($commission_amount*18/100);
            $Order->mode=$request->mode;
            $Order->contact_id=$request->contact_id;
            $Order->purpose=$request->purpose;
            $Order->narration=$request->narration;
            $Order->fee=$request->fee;
            $Order->remark=$request->remark;
            $Order->save();
            
            $this->message="Order Created Successfull";
            $this->data=$Order;
        
            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => $this::SUCCESS_STATUS,
                    'data'      => $this->data
                ])
            );
    
        }

        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => $this::FAILED_STATUS,
                'data'      => []
            ])
        );

    }


    public function update(Request $request,$id)
    {
        $validation=new Validations($request);
        $validator=$validation->updateOrder();
    
        $validator->after(function($validator) use ($request,$id){
               
            $Order =Order::where('order_id',$id)->first();
    
            if(empty($Order)){
                $validator->errors()->add('order_id','order_id is not valid');
            }
           
        });
    
        if($validator->fails()){
            $this->message=$validator->errors();
           
        }else{
           
            $Order= Order::where('order_id',$id)->first();
            
            $Order->currency=$request->currency;
            $Order->amount=$request->amount;
        
            $Order->mode=$request->mode;
            
            $Order->purpose=$request->purpose;
            $Order->save();
            $Order= Order::where('order_id',$id)
            ->select('product_id','order_id','contact_id','currency','amount','fee','tax','mode','purpose','narration',
            'remark','status','created_at','updated_at')->first();
        
            $this->data=['order'=>$Order];
            $this->message="Order Updated Successfull";
              
            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => $this::SUCCESS_STATUS,
                    'data'      => $this->data
                ])
            );
        
        }
    
        return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => $this::FAILED_STATUS,
                    'data'      => []
                ])
            );

    }

   

        public function updateStatus(Request $request,$order_id){

            $validation=new Validations($request);
            $validator=$validation->updateOrderStatus();
            $validator->after(function($validator) use ($request,$order_id){
            
            $Order =Order::where('order_id',$order_id)->first();
            
            if(empty($Order)){
                $validator->errors()->add('order_id','order_id_id is not valid');
            }
            
        });

        if($validator->fails()){
            $this->message=$validator->errors();
            
        }else{
            
                
            $Contact= Order::where('order_id',$order_id)
            ->select('product_id','order_id','contact_id','currency','amount','fee','tax','commission','mode','purpose','narration',
            'remark','status','created_at','updated_at')->first();
            
            $Contact->status='cancelled';
            $Contact->save();
        
            $this->data=['order'=>$Contact];
            $this->message="Order Cancelled Successfull";
            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => $this::SUCCESS_STATUS,
                    'data'      => $this->data
                ])
            );
        
        
            }

            return response()->json(
                $this->populate([
                    'message'   => $this->message,
                    'status'    => $this::FAILED_STATUS,
                    'data'      => []
                ])
            );
        }

    public function show($order_id)
    {

        $Order = Order::select('product_id','order_id','contact_id','currency','amount','fee','tax','commission','mode','purpose','narration',
        'remark','status','created_at','updated_at')->where('order_id',$order_id)->first(); 
        $this->message="Record found Successfull";
        $this->data=['order'=>$Order];
    
        return response()->json(
            $this->populate([
                'message'   => $this->message,
                'status'    => $this::FAILED_STATUS,
                'data'      => $this->data
            ])
        );

    }



}
