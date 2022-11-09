<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inventries;
use App\User;
use Illuminate\Support\Facades\Http;
use App\Encompasses;
use App\Orders;
use Storage;
use Str;
use File;
use URL;
use App\Inventry_hisotories;

class InventryController extends Controller
{
    public function inventry_create(Request $request, $truck_id)
    {
        $data = $request->all();
        $url = str_replace('public', '', URL::to('/'));
        $validator = \Validator::make($data, [
            'item_name'=> 'required|max:255',
            'user_id'  => 'required',
            'brand_name' => 'required',
            'description' => 'required',
            'item_price' => 'required',
            'bin_id'   => 'required',
            'item_code'=> 'required',
            'quantity' => 'required'
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error'], 400);
        }
        $item_img_path = "";
        if(!empty($request->item_image_base64)){
            $image_64 = $request->item_image_base64;
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

            $item_img_path = 'public/uploads/item/'.time().'.'.$extension; //Str::random(15).'.png'; 

            // decode the base64 file 
            $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->input('item_image_base64')
             )); 
            Storage::put($item_img_path, $file); 
            $item_img_path = $url.'storage/app/'.$item_img_path;
        }else{
            if(!empty($request->item_image)){
                $item_img_path = $request->item_image;
            }
            
        }
        $inventry = Inventries::where('truck_id'  , $truck_id)
             ->where('bin_id'    , $data['bin_id'])
             ->where('item_code' , $data['item_code'])
             ->get();
        if(count($inventry)!= 0){
            $inventry_id   = $inventry[0]->id;
            $prev_quantity = $inventry[0]->quantity;
            $new_quantity  = $request->quantity;
            $inventry      = Inventries::findOrFail($inventry_id);
            $inventry->basePN      = $request->basePN;
            $inventry->quantity = $prev_quantity+$new_quantity;
            $inventry->item_image = $item_img_path;
            $inventry->save();
            

        }else{
            $inventry = new Inventries();
            $inventry->fill($data);
            $inventry->item_image = $item_img_path;
            $inventry->truck_id = $truck_id;
            $inventry->save();
        }

        $data = array(
            'id'              => $inventry->id,
            'truck_id'        => $inventry->truck_id,
            'bin_id'          => $inventry->bin_id,
            'user_id'         => $inventry->user_id,
            'item_code'       => $inventry->item_code,
            'basePN'         =>  $inventry->basePN,
            'brand_name'      => $inventry->brand_name,
            'description'     => $inventry->description,
            'item_price'      => $inventry->item_price,
            'quantity'        => $inventry->quantity,
            'item_name'       => $inventry->item_name,
            'item_image'      => $inventry->item_image

        );
        $inventry_history = new Inventry_hisotories();
        $inventry_history->inventry_id = $inventry['id'];
        $inventry_history->item_data   = json_encode($data);
        $inventry_history->status      = 'created';
        $inventry_history->save();
        $jsonData = [
        'status' => 'success',
        'code' => 200,
        'item' => $data
        ];
        return response()->json($jsonData);
        //return response(['message' => 'Created successfully'], 200);
    }

    public function inventry_get($id){
        $url = str_replace('public', '', URL::to('/'));
        $get_inventry = Inventries::find($id);
        //if(count($get_inventry) != 0){
            $inventry[] = [
                   
                        'id'            => $get_inventry['id'],
                        'user_id'       => $get_inventry['user_id'],
                        'truck_id'      => $get_inventry['truck_id'],
                        'item_name'     => $get_inventry['item_name'],
                        'quantity'      => $get_inventry['quantity'],
                        'bin_id'        => $get_inventry['bin_id'],
                        "basePN"        => $get_inventry['basePN'],
                        'brand_name'      => $get_inventry['brand_name'],
                        'description'     => $get_inventry['description'],
                        'item_price'       => $get_inventry['item_price'],
                        'item_code'     => $get_inventry['item_code'],
                        'item_image'    => $get_inventry['item_image']
                        
                    ];
         /*}else{
            $inventry = 'No data';
         }   */
            $jsonData = [
                'status' => 'success',
                'code' => 200,
                'item' => $inventry,
            ];                            

        return response()->json($jsonData);
    }
    public function inventry_delete($id){
        $inventry = Inventries::find($id)/*->delete()*/;
        if(empty($inventry)){
            return response(['message' => 'This inventry not deleted'], 400);
        }else{ 
        $data  = array(
                        'id'            => $inventry['id'],
                        'user_id'       => $inventry['user_id'],
                        'truck_id'      => $inventry['truck_id'],
                        'item_name'     => $inventry['item_name'],
                        'quantity'      => $inventry['quantity'],
                        'bin_id'        => $inventry['bin_id'],
                        "basePN"        => $inventry['basePN'],
                        'brand_name'      => $inventry['brand_name'],
                        'description'     => $inventry['description'],
                        'item_price'       => $inventry['item_price'],
                        'item_code'     => $inventry['item_code'],
                        'customer_details'  => json_decode($inventry['customer_details'], true),
                        'item_image'    => $inventry['item_image']

        );

        $inventry_history = new Inventry_hisotories();
        $inventry_history->inventry_id = $inventry['id'];
        $inventry_history->item_data   = json_encode($data);
        $inventry_history->status   = 'deleted';
        $inventry_history->save();
        $inventry->delete();
        return response(['message' => 'Inventry deleted successfully'], 200);
        }
    }
    public function inventry_update(Request $request, $id){
        $url = str_replace('public', '', URL::to('/'));
        $inventry = Inventries::findOrFail($id);
        $quantity = $inventry['quantity'];
        if($quantity >= $request->quantity){
            if($quantity != 0){
                $quantity = $quantity-$request->quantity;
            }
            $input    = $request->all();
            if(!empty($input['customer_details'])){
                $input['customer_details'] = json_encode($input['customer_details'],true);
            }
            if($quantity !=0){
                $input['quantity'] = $quantity;
                $inventry->fill($input)->save();
                $newQuantity = $inventry['quantity'];
            }else{
                $inventry->delete();
                $newQuantity = 0;
                $invenstatus = 'deleted';
            }
            $userRepairQuantity = $request->quantity;
            $invenstatus = 'use and repair';
            $this->add_inventory_history($inventry, $invenstatus, $userRepairQuantity);
            $update_inventry = [
                       
                            'id'                => $inventry['id'],
                            'truck_id'          => $inventry['truck_id'],
                            'user_id'           => $inventry['user_id'],
                            'item_name'         => $inventry['item_name'],
                            'quantity'          => $newQuantity,
                            'bin_id'            => $inventry['bin_id'],
                            'item_code'         => $inventry['item_code'],
                            "basePN"            => $inventry['basePN'],
                            'item_price'       => $inventry['item_price'],
                            'brand_name'      => $inventry['brand_name'],
                            'description'     => $inventry['description'],
                            'customer_details'  => json_decode($inventry['customer_details'], true),
                            'item_image'        => $inventry['item_image']
                            
            ];
            $responseStatus = 'success';
        }else{
            $responseStatus = false;
            $update_inventry = [];
        }
            
            $jsonData = [
                'status' => $responseStatus,
                'code'   => 200,
                'item'   => $update_inventry,
            ];                            

        return response()->json($jsonData);

        //return response(['message' => 'updated successfully'], 200);
    }

    public function inventry_manual_update(Request $request, $id){
        $url = str_replace('public', '', URL::to('/'));
        $inventry = Inventries::findOrFail($id);
        $quantity = $inventry['quantity'];
        $input = $request->all();

        if($quantity < $request->quantity){
            //$quantity = $request->quantity + $quantity;
            $input['quantity'] = $request->quantity;
            $newQuantitylog = $request->quantity - $quantity;
            $invenstatus = 'transfer in';
        }elseif($quantity > $request->quantity){
            $newQuantitylog = $quantity - $request->quantity;
            $input['quantity'] = $request->quantity;
            $invenstatus = 'transfer out';
        }elseif($request->quantity == 0){
                $inventry->delete();
                $newQuantitylog = 0;
                $invenstatus = 'deleted';
        }
        $inventry->fill($input)->save();
        if($quantity != $request->quantity){
            $this->add_inventory_history($inventry, $invenstatus, $newQuantitylog);
            $update_inventry = [
                       
                            'id'                => $inventry['id'],
                            'truck_id'          => $inventry['truck_id'],
                            'user_id'           => $inventry['user_id'],
                            'item_name'         => $inventry['item_name'],
                            'quantity'          => $inventry['quantity'],
                            'bin_id'            => $inventry['bin_id'],
                            'item_code'         => $inventry['item_code'],
                            "basePN"            => $inventry['basePN'],
                            'item_price'       => $inventry['item_price'],
                            'brand_name'      => $inventry['brand_name'],
                            'description'     => $inventry['description'],
                            'customer_details'  => json_decode($inventry['customer_details'], true),
                            'item_image'        => $inventry['item_image']              
            ];
            $responseStatus = 'success';
        }else{
            $responseStatus = 'success';
            $update_inventry = [];
        }
            
            $jsonData = [
                'status' => $responseStatus,
                'code'   => 200,
                'item'   => $update_inventry,
            ];                            

        return response()->json($jsonData);

        //return response(['message' => 'updated successfully'], 200);
    }
    public function inventry_gets(Request $request, $id){
        $url = str_replace('public', '', URL::to('/'));
        $truck_id = $request->truck_id;
        $bin_id   = $request->bin_id;
        $sorting  = $request->sorting; //global_quantity and global_type
        $sorting_type = $request->sorting_type;
        $setting      = $request->setting;
        $quantity     = $request->setting_quantity;
        if(empty($sorting_type) && empty($sorting)){
            $sorting_type = 'DESC';
            $sorting   = 'id';
        }
        
        
        $getData = Inventries::with('trucks')->where('user_id',"=", $id);

        if(!empty($setting) && !empty($quantity)){
            if($setting == "lessthan"){
              $getData = $getData->where('quantity',"<", $quantity );
            }elseif($setting == "equal"){
                $getData = $getData->where('quantity',"=", $quantity );
            }
            
        }
        if(!empty($truck_id)){
            $getData = $getData->orwhere('truck_id', $truck_id );
        }
        if(!empty($bin_id)){
            $getData = $getData->orwhere('bin_id', $bin_id);
        }
        $getData = $getData->orderBy($sorting, $sorting_type);
        $getData = $getData->get();
        
        if(count($getData) != 0){
            for ($data=0; $data < count($getData) ; $data++) { 
                $binname_arr  = $getData[$data]->trucks->binName;
                if(empty($binname_arr)){
                    $getData[$data]->delete();
                    $allData[] = $allData;
                }else{
                    $bins   = json_decode($binname_arr,true);
                    $binId  = $getData[$data]->bin_id;
                    $index  = array_search($binId, array_column($bins, 'id'));
                    $binName = $bins[$index]['bin_name'];
                    $allData[] = [
                   
                        'id'            => $getData[$data]->id,
                        'truck_id'      => $getData[$data]->truck_id,
                        'truck_name'    => $getData[$data]->trucks->truckName,
                        'bin_id'        => $getData[$data]->bin_id,
                        'bin_name'      => $binName,
                        'item_name'     => $getData[$data]->item_name,
                        'quantity'      => $getData[$data]->quantity,    
                        'user_id'       => $getData[$data]->user_id,
                        "basePN"        => $getData[$data]->basePN,
                        'brand_name'    => $getData[$data]->brand_name,
                        'description'   => $getData[$data]->description,    
                        'item_price'    => $getData[$data]->item_price,
                        'setting'       => $getData[$data]->setting,
                        'fix_quantity'  => $getData[$data]->fix_quantity,
                        'item_code'     => $getData[$data]->item_code,
                        'item_image'    => $getData[$data]->item_image
                        
                    ];
                }
            }
        }else{
            $allData = [];
        }
            $jsonData = [
                'status' => 'success',
                'code' => 200,
                'item' => $allData,
            ];                            

        return response()->json($jsonData);

        
    }

    public function inventry_transfer(Request $request, $user_id){
        $data = $request->all();
        $validator = \Validator::make($data, [
            'inventry_id' => 'required',
            'item_code'  => 'required|max:255',
            'from_truck' => 'required',
            'from_bin'   => 'required',
            'to_truck'   => 'required',
            'to_bin'     => 'required',
            'quantity'   => 'required'
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error'], 400);
        }

        $new_quantity  =  $request->quantity;
        $from_inventry_id = $request->inventry_id;
        $from_inventry               = Inventries::findOrFail($from_inventry_id);
        if($from_inventry['quantity'] >= $new_quantity){
            $to_inventry = Inventries::where('truck_id', $data['to_truck'])
             ->where('bin_id'    , $data['to_bin'])
             ->where('item_code' , $data['item_code'])
            ->get();
            $message = "";
            if(count($to_inventry)!= 0 && $request->quantity != 0){
                
                $to_inventry_id              = $to_inventry[0]->id;
                $prev_quantity               = $to_inventry[0]->quantity;
                $transfer_inventry           = Inventries::findOrFail($to_inventry_id);
                $transfer_inventry->quantity = $prev_quantity+$new_quantity;
                $transfer_inventry->item_image = $from_inventry['item_image'];
                $transfer_inventry->basePN     = $from_inventry['basePN'];
                $transfer_inventry->save();
                $this->add_inventory_history($transfer_inventry, 'transfer in', $new_quantity);

                $from_inventry_quantity      = $from_inventry['quantity'];
                $update_from_invetry_qua = $from_inventry_quantity-$new_quantity;
                $message = "transfer successfully";
                $invt_id = $to_inventry_id;
                if( $update_from_invetry_qua != 0){
                    $from_inventry->quantity     = $update_from_invetry_qua;
                    $outQuantity = $new_quantity;
                    $from_inventry->save();
                    $inventoryStatus = 'transfer out';
                }else{
                    $from_inventry->delete();
                    $outQuantity = $from_inventry['quantity'];
                    $message = "transfer successfully with delete previous inventry";
                    $invt_id = $from_inventry_id;
                    $inventoryStatus = 'deleted';
                }
                $this->add_inventory_history($from_inventry, $inventoryStatus, $outQuantity);


            }else{
                if($request->to_truck != 0 && $request->to_bin != 0 && $request->quantity != 0){
                    $inventry             = new Inventries();
                    $inventry->user_id    = $user_id;
                    $inventry->truck_id   = $request->to_truck;
                    $inventry->bin_id     = $request->to_bin;
                    $inventry->item_code  = $request->item_code;
                    $inventry->item_name  = $request->item_name;
                    $inventry->quantity   = $request->quantity;
                    $inventry->basePN     = $from_inventry->basePN;
                    $inventry->description = $from_inventry->description;
                    $inventry->brand_name = $from_inventry->brand_name;
                    $inventry->item_image = $from_inventry->item_image;
                    $inventry->item_price = $from_inventry->item_price;
                    $inventry->setting    = $from_inventry->setting;
                    $inventry->fix_quantity = $from_inventry->fix_quantity;
                    $inventry->save();
                    $inventoryStatus = 'transfer in';
                    //$message = "create new invenrty";
                    $this->add_inventory_history($inventry, $inventoryStatus, $inventry['quantity']);

                        $from_inventry               = Inventries::findOrFail($from_inventry_id);
                        $from_inventry_quantity   = $from_inventry['quantity'];
                        $update_from_invetry_qua = $from_inventry_quantity-$new_quantity;
                        $message = "transfer successfully";
                        if( $update_from_invetry_qua != 0){
                            $outQuantity = $new_quantity;
                            $from_inventry->quantity     = $update_from_invetry_qua;
                            $from_inventry->save();
                            $inventoryStatus = 'transfer out';
                        }else{
                            $from_inventry->delete();
                            $outQuantity = $from_inventry['quantity'];
                            $message = "transfer successfully with delete previous inventry";
                            $invt_id = $from_inventry_id;
                            $inventoryStatus = 'deleted';
                        }
                        $this->add_inventory_history($from_inventry, $inventoryStatus, $outQuantity);

                    $invt_id = $inventry['id'];
                }else{
                    $empty_inventry = Inventries::find($from_inventry_id)->delete();
                    $outQuantity    = $empty_inventry['quantity'];
                    $message = "create new invenrty";
                    $invt_id = $inventry['id'];
                    $inventoryStatus = 'deleted';
                    $this->add_inventory_history($empty_inventry, $inventoryStatus, $outQuantity);
                }
            }
            $data = [
                        "message"=> $message,
                        "inventry_id" =>$invt_id
                ];
            $responseStatus = "success";

            //add inventory history table 

        }else{
            $data = [];
            $responseStatus = false;
        }
        $jsonData = [
                'status' => $responseStatus  ,
                'code'   => 200,
                'item'   => $data
            ];
        return response()->json($jsonData);
    }
    public function add_inventory_history($inventoryUpdation, $inventoryStatus, $new_quantity){
        $update_inventry = [
                       
                            'id'                => $inventoryUpdation['id'],
                            'user_id'           => $inventoryUpdation['user_id'],
                            'truck_id'          => $inventoryUpdation['truck_id'],
                            'item_name'         => $inventoryUpdation['item_name'],
                            'quantity'          => $new_quantity,
                            'bin_id'            => $inventoryUpdation['bin_id'],
                            'item_code'         => $inventoryUpdation['item_code'],
                            "basePN"            => $inventoryUpdation['basePN'],
                            'customer_details'  => json_decode($inventoryUpdation['customer_details'], true),
                            'brand_name'    => $inventoryUpdation['brand_name'],
                            'description'   => $inventoryUpdation['description'],
                            'item_price'       => $inventoryUpdation['item_price'],
                            'item_image'        => $inventoryUpdation['item_image']
                            
            ];
            $inventry_history = new Inventry_hisotories();
            $inventry_history->inventry_id = $inventoryUpdation['id'];
            $inventry_history->item_data   = json_encode($update_inventry);
            $inventry_history->status      = $inventoryStatus;
            $inventry_history->save();

    }
    public function inventry_count($user_id){
        $user_inventries =count(Inventries::where('user_id',"=", $user_id)->get());
        $jsonData = [
                'status' => 'success',
                'code' => 200,
                'user_inventries' => $user_inventries,
            ];                            

        return response()->json($jsonData);

    }
    public function specific_setting(Request $request, $user_id){
        $data = $request->all();

        $increase_quantity = Inventries::where('id', $data['inventry_id'])->update(['fix_quantity' => $data['specific_quantity'], 'setting' => $data['specific_type'] ]);
        $jsonData = [
                'status' => 'success',
                'code' => 200,
                'setting' => 'Save setting',
            ];                            

        return response()->json($jsonData);
    }
    public function global_setting(Request $request, $user_id){
        $data = $request->all();

          $increase_quantity = Inventries::where('user_id', $user_id)->update(['fix_quantity' => $data['global_quantity'], 'setting' => $data['global_type'] ]);
        $jsonData = [
                'status' => 'success',
                'code' => 200,
                'setting' => 'Save setting',
            ];                            

        return response()->json($jsonData);

    }

    public function low_inventry(Request $request, $user_id){
        $low_inventry         = count(Inventries::where('user_id', $user_id)->whereRaw('quantity < fix_quantity')->get());
        $allInventries = Inventries::where('user_id',"=", $user_id);
        //$total_inventries_sum = $allInventries->sum('item_price');

        $total_trucks       = Encompasses::where('user_id',"=", $user_id)->get();
        $total_bins = 0;
        foreach ($total_trucks as $truck => $data) {
            $total_bins = $total_bins+$data['bins'];
        }

        $getInven = $allInventries->get();
        $totalInventoryPrice = 0;
        $totalQuantity = 0;
        foreach($getInven as $inven){
            $quantity = $inven->quantity;
            $totalQuantity = $totalQuantity+$quantity;
            $item_price = $inven->item_price;
            $InventoryPrice = $quantity*$item_price;
            $totalInventoryPrice = $totalInventoryPrice+$InventoryPrice;
         }
         //dd($totalInventoryPrice);
        $jsonData = [
                'status' => 'success',
                'code' => 200,
                'low' => $total_bins,
                'total_invetory_amount'=> $totalInventoryPrice,
                'high' => $totalQuantity//count($getInven)
            ];                            

        return response()->json($jsonData);

    }

    public function order_near_by(Request $request, $user_id){
        $url = str_replace('public', '', URL::to('/'));
        $data = $request->all();
        $getTrucks = Inventries::with('trucks', 'user')->where('user_id', '!=', $user_id)
         ->where('item_code', 'like', '%'  . $data['item_code'] . '%')
         ->orWhere('brand_name', 'like', '%'  . $data['item_code'] . '%')
         ->orWhere('item_name', 'like', '%'  . $data['item_code'] . '%')
         ->orWhere('description', 'like', '%'  . $data['item_code'] . '%')
        ->get();
        if(count($getTrucks) != 0){

            for ($data=0; $data < count($getTrucks) ; $data++) { 
                $allData[] = [
               
                    'userName'       => $getTrucks[$data]->user->name,
                    'email'          => $getTrucks[$data]->user->email,
                    'lat'            => $getTrucks[$data]->user->lat,
                    'lng'            => $getTrucks[$data]->user->lng,
                    'address'        => $getTrucks[$data]->user->address,
                    'phone'          => $getTrucks[$data]->user->phone,
                    'truckName'      => $getTrucks[$data]->trucks->truckName,
                    'category'       => $getTrucks[$data]->trucks->category,
                    'itemCode'       => $getTrucks[$data]->item_code,
                    'itemName'       => $getTrucks[$data]->item_name,
                    'item_image'    => $getTrucks[$data]->item_image

                ];
            }
        }else{
           $allData = []; 
        }
            $jsonData = [
                'status' => 'success',
                'code' => 200,
                'item' => $allData,
            ];                            

        return response()->json($jsonData);
    }

    public function search_inventry(Request $request, $user_id){
        $data = $request->all();
        $url = str_replace('public', '', URL::to('/'));
        $getData = Inventries::where('user_id', '=', $user_id)
         ->where('item_code' , 'like', '%'. $data['item_code'] . '%')
         ->orWhere('brand_name', 'like', '%'  . $data['item_code'] . '%')
         ->orWhere('item_name', 'like', '%'  . $data['item_code'] . '%')
         ->orWhere('description', 'like', '%'  . $data['item_code'] . '%')
        ->get();

        if(count($getData) != 0){
            for ($data=0; $data < count($getData) ; $data++) { 
                $binname_arr  = $getData[$data]->trucks->binName;
                if(empty($binname_arr)){
                    $getData[$data]->delete();
                    $allData[] = $allData;
                }else{
                    $bins   = json_decode($binname_arr,true);
                    $binId  = $getData[$data]->bin_id;
                    $index  = array_search($binId, array_column($bins, 'id'));
                    $binName = $bins[$index]['bin_name'];

                $allData[] = [
               
                    'id'            => $getData[$data]->id,
                    'truck_id'      => $getData[$data]->truck_id,
                    'truck_name'    => $getData[$data]->trucks->truckName,
                    'bin_name'      => $binName,
                    'item_name'     => $getData[$data]->item_name,
                    'quantity'      => $getData[$data]->quantity,    
                    'user_id'       => $getData[$data]->user_id,
                    'bin_id'        => $getData[$data]->bin_id,
                    'item_code'     => $getData[$data]->item_code,
                    'basePN'        => $getData[$data]->basePN,
                    'item_price'    => $getData[$data]->item_price,
                    'item_name'     => $getData[$data]->item_name,
                    'brand_name'    => $getData[$data]->brand_name,
                    'description'   => $getData[$data]->description,
                    'item_image'    => $getData[$data]->item_image
                    
                ];
                }
            }
        }else{
            $allData = [];
        }
            $jsonData = [
                'status' => 'success',
                'code' => 200,
                'item' => $allData,
            ];                            

        return response()->json($jsonData);
    }

    public function get_truck_inventory_CSV(Request $request){
        $getAll_inventory = Inventries::whereDate('created_at','=', date('Y-m-d'))->with('trucks', 'user')->get();
      
        if(!empty($getAll_inventory)){
            $string = "Customer#    Truck#    basePN    OnHand    OnPo    Average Cost \n";
            if(count($getAll_inventory) != 0){

                for ($i=0; $i < count($getAll_inventory) ; $i++) { 
                    $encompassCustomerInfo  = json_decode($getAll_inventory[$i]->user->encompass_user);
                    //\DB::enableQueryLog(); // Enable query log
                    $allOrderBasedOnBasePN = Orders::where('user_id','=', $getAll_inventory[$i]->user->id)
                    ->whereDate('created_at','=', date('Y-m-d'))
                    ->where('status','=', 'success')
                    ->whereJsonContains('request_parameter->data->parts', ['basePN' => $getAll_inventory[$i]->basePN])
                    ->get();
                        //dd(\DB::getQueryLog()); // Show results of log
                    $ordersIds = [];
                    if(!is_null($encompassCustomerInfo)){
                        foreach ($allOrderBasedOnBasePN as  $orders) {
                            $orderStatusData = $this->get_order_status($encompassCustomerInfo->customerNumber, $encompassCustomerInfo->customerPassword, $orders->order_id);
                            $orderStatus = $orderStatusData->object()->data;
                            if($orderStatusData->status() != 400){
                                $status = $orderStatus->records[0]->parts[0]->status;
                                if($status != 'SHIPPED'){
                                    $ordersIds =  [$orders->order_id];
                                }
                            }
                        }
                        
                         $string .=   $encompassCustomerInfo->customerNumber.'    '.$getAll_inventory[$i]->trucks->id.'    '. $getAll_inventory[$i]->basePN .'    '.$getAll_inventory[$i]->quantity.'    '.count($ordersIds).'    '.$getAll_inventory[$i]->item_price."\n" ;
                    }
                }
            }
             $fileName = 'inventory_'.date('Ymd_His').'.txt';
            \Storage::put('trucktransaction/'.$fileName, $string);
        }
    }
    public function get_order_status($customerNumber , $customerPassword, $order_id){
        if(!is_null($customerNumber) || !empty($customerPassword)){
            // $endPoint = config('app.ENCOMPASS_ORDER_STATUS_API');
            $endPoint = config('custom.encompass_order_status_api');                                                                                           
            $params = [
                "settings" => [
                    "jsonUser" => "SIMPLYPARTS",
                    "jsonPassword" => "YU4ZGF47GND8",
                    "customerNumber"=> $customerNumber,
                    "customerPassword" => $customerPassword,
                    "programName" => "JSON.ORDER.STATUS"

                ],
                "data"=>[
                    "referenceNumber"=> $order_id,
                ]
            ];
            $header = [
                "Content-Type" => "application/json"
            ];
            $response = Http::post($endPoint, $params
            );

            return $response;
        }
    }

    public function get_truck_inventory_transation(Request $request){
        $getAll_transation = Inventry_hisotories::whereDate('created_at','=', date('Y-m-d'))->get();
        if(!empty($getAll_transation)){
            $string = "EncompassCustomer#    Truck#    basePN    Date    Quantity    Code    Unit Cost \n";
            if(count($getAll_transation) != 0){
                for ($i=0; $i < count($getAll_transation) ; $i++) { 
                    $transationData  = json_decode($getAll_transation[$i]->item_data);
                    $getUser = $transationData->user_id;
                    if(!empty($transationData)){
                        $code = '';
                        if($getAll_transation[$i]->status == 'transfer in'){
                            $code = 'T';
                            $sign = "+";
                        }
                        if($getAll_transation[$i]->status == 'transfer out'){
                            $code = 'T';
                            $sign = "-";
                        }
                        if($getAll_transation[$i]->status == 'created'){
                            $code = 'R';
                            $sign = "+";
                        }
                        if($getAll_transation[$i]->status == 'deleted'){
                            $code = 'A';
                            $sign = "-";
                        }
                        if($getAll_transation[$i]->status == 'use and repair'){
                            $code = 'S';
                            $sign = "-";
                        }
                        $date = date('d-m-Y', strtotime($getAll_transation[$i]->created_at));
                        $encompass_info = User::where('id', $getUser)->pluck('encompass_user')->toArray();
                        $customerNumber = json_decode($encompass_info[0]);
                        if(!is_null($customerNumber)){
                            $string .=   $customerNumber->customerNumber.'    '.$transationData->truck_id.'    '. $transationData->basePN .'    '.$date.'    '.$sign.$transationData->quantity.'    '.$code.'    '.$transationData->item_price."\n" ;
                        }
                    }
                }
            }
            $fileName = 'transaction_'.date('Ymd_His').'.txt';
            \Storage::put('trucktransaction/'.$fileName, $string);
        }
    }
}
