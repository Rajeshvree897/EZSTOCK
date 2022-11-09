<?php

namespace App\Http\Controllers;
use App\Encompasses;
use App\Inventries;

use Illuminate\Http\Request;

class EnncompassController extends Controller
{
    
    public function encompass_create(Request $request)
    {
       
        $data = $request->all();
        $validator = \Validator::make($data, [
            'truckName' => 'required|max:255',
            'bins' => 'required'
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error'], 400);
        }
        
       // $binsNameRange = range('A', 'Z');
        for ($binsName=0; $binsName<$request->bins ; $binsName++) { 
            $count = $binsName+1;
            $binsNames[] = array(
                'bin_name'      => (string)$count,
                'id'            => $count,
                'bin_category'  => $request->category
            );
        }
        $binsNames = json_encode($binsNames);
        $encompasses = new Encompasses();
        $encompasses->fill($data);
        $encompasses->binName = $binsNames;
        $encompasses->save();
        $jsonData = [
        'status' => 'success',
        'code' => 200,
        'item' => [
            'id'        => $encompasses->id,
            'truckName' => $encompasses->truckName,
            'bins'      => $encompasses->bins,
            'user_id'   => $encompasses->user_id,
            'category'  => $encompasses->category,
            'binName'   => json_decode($encompasses->binName),
            ]
        ];
        return response()->json($jsonData);
        //return response(['message' => 'Created successfully'], 200);
    }
    public function encompass_update(Request $request, $id){
        $encompasses = Encompasses::findOrFail($id);
        $input    = $request->all();
        if(!empty($request->binArr) || !empty($request->truckName)){
            $bins_arr = $request->binArr;
            //dd($request->binArr);
            /*$binsNameRange = range('A', 'Z');
            for ($binsName=0; $binsName<count($bins_arr) ; $binsName++) { 
                $count = $binsName+1;
                $bin = $bins_arr[$binsName];
                $binsNames[] = array(
                    'bin_name'      =>$binsNameRange[$bin-1],
                    'id'            => $bin,
                    'bin_category'  => $request->category
                );
            }*/
            //$input['binName'] = json_encode($binsNames);
            if(!is_null($bins_arr)){
                $input['bins']  = count($bins_arr);
                $encompasses->binName = json_encode($bins_arr);
            }
            $encompasses->fill($input)->save;
            $encompasses->save();
            $jsonData = [
            'status' => 'success',
            'code' => 200,
            'item' => [
                'id'        => $encompasses->id,
                'truckName' => $encompasses->truckName,
                'bins'      => $encompasses->bins,
                'user_id'   => $encompasses->user_id,
                'category'  => $encompasses->category,
                'binName'   => json_decode($encompasses->binName),
                ]
            ];
        }else{
            $jsonData = [
            'status' => 'failed',
            'code' => 400,
            'item' => []
            ];
        }
        return response()->json($jsonData);

        //return response(['message' => 'updated successfully'], 200);

    }
    public function encompass_delete( $id){
        $encompasses = Encompasses::find($id)->delete();
        if(empty($encompasses)){
            return response(['message' => 'This record not deleted'], 400);

        }else{   
        return response(['message' => 'deleted successfully'], 200);
        }

    }
    public function encompass_get( $user_id){
         $getData = Encompasses::with('related_inventories')->where('user_id',"=", $user_id)->get();
        //$getData = Encompasses::where('user_id', $id)->get();
        if(count($getData) != 0){

            for ($data=0; $data < count($getData) ; $data++) { 
                $no_of_inventory = count($getData[$data]->related_inventories);

                $allData[] = [
               
                    'id'        => $getData[$data]->id,
                    'truckName' => $getData[$data]->truckName,
                    'bins'      => $getData[$data]->bins,
                    'binName'   => json_decode($getData[$data]->binName),    
                    'user_id'   => $getData[$data]->user_id,
                    'category'  => $getData[$data]->category,
                    'no_of_inventory' => $no_of_inventory
                    
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

    public function delete_bin(Request $request, $truck_id){
        $data = $request->all();
        $encompasses = Encompasses::find($truck_id);
        $binname_arr  = $encompasses->binName;
        $bins   = json_decode($binname_arr,true);
        $binId  = $data['bin_id']; 
        $index  = array_search($binId, array_column($bins, 'id'));
        unset($bins[$index]);
        if(empty($bins)){
            $encompasses->delete();
        }else{
            $bins = array_values($bins);
            $encompasses = Encompasses::findOrFail($truck_id);
            $encompasses->binName = json_encode($bins, true);
            $encompasses->bins = $encompasses->bins-1;
            $encompasses->save();
        }
        $inventry = Inventries::where('user_id', $data['user_id'])
        ->where('truck_id', $truck_id)
        ->where('bin_id', $data['bin_id'])->get();
    
        foreach($inventry as $inventry_del){
            $inventry_del->delete();
        }
        $jsonData = [
        'status' => 'success',
        'code' => 200,
        'item' => "Bin deleted successfully"
        ];
        return response()->json($jsonData);
    }
    public function truck_summary(Request $request, $user_id){
        $truckSummary = [];
        $getData = Encompasses::with('related_inventories')->where('user_id',"=", $user_id)->orderBy('id', 'ASC')->get();
        if(count($getData) != 0){

            for ($data=0; $data < count($getData) ; $data++) {
                $total_inventries =Inventries::where('truck_id',"=", $getData[$data]->id);
                $inventoryQty = $total_inventries->sum('quantity');
                $getTruckInven = $total_inventries->get();
                $totalInventoryPrice = 0;
                foreach($getTruckInven as $inven){
                    $quantity = $inven->quantity;
                    $item_price = $inven->item_price;
                    $InventoryPrice = $quantity*$item_price;
                    $totalInventoryPrice = $totalInventoryPrice+$InventoryPrice;
                 }
                $bins = json_decode($getData[$data]->binName);
                $binsListing = [];
                $binIDS = [];
                if(!empty($bins)){
                    foreach($bins as $bin){
                        $binName = $bin->bin_name;
                        $binID = $bin->id;
                        $binsInvetory =Inventries::where('truck_id',"=", $getData[$data]->id)->where('bin_id', '=', $binID);
                        $allInventory  = $binsInvetory->get();
                        $totalBinPrice = 0;
                        foreach ($allInventory as $inventory) {
                            $binQuantity = $inventory->quantity;
                            $binItemPrice = $inventory->item_price;
                            $binPrice = $binQuantity*$binItemPrice;
                            $totalBinPrice = $totalBinPrice+$binPrice;
                        }
                        $binInventoryQty = $binsInvetory->sum('quantity');
                        $allInventory  = $binsInvetory->get();
                        $binsListing[] = [
                            "bin_id"  => $binID,
                            "binName" => $binName,
                            "binInventoryQty" => $binInventoryQty,
                            "totalBinPrice" => $totalBinPrice

                        ];

                    }
                }

                $truckSummary[] = [
                                "truck_id" => $getData[$data]->id,
                                'truckName'         => $getData[$data]->truckName,
                                'inventoryQty'      => $inventoryQty,
                                'totalInventoryPrice'   => $totalInventoryPrice,
                                'binsListing'   => $binsListing
                                               
                ];
            }
        }

         $jsonData = [
                'status' => 'success',
                'code' => 200,
                'data' => $truckSummary,
            ];                            

        return response()->json($jsonData);
    }
}
