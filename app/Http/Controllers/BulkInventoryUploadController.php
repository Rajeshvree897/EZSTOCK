<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inventries;
use App\User;
use App\Encompasses;
use Storage;
use Response;
use Session;
use Str;
use File;
use URL;
use App\Inventry_hisotories;

class BulkInventoryUploadController extends Controller
{
    public function bulkCsvUpload(Request $request)
    {

    	
    	$bulk_inventoryCSV = $request->hasfile('bulk_inventory');
    	if($bulk_inventoryCSV){   
            $file       = $request->file('bulk_inventory');
            $filename   = $file->getClientOriginalName();
            $extension  = $file->getClientOriginalExtension(); //Get extension of uploaded file
            $tempPath   = $file->getRealPath();
            $fileSize   = $file->getSize(); //Get size of uploaded file in bytes
           
            //Where uploaded file will be stored on the server 

            $location = 'uploads/InventriesCSV'; //Created an "uploads" folder for that
            // Upload file

            $fileNameToStore = $filename . '_' . now() . '.' . $extension;
            $file->move($location, $fileNameToStore);

            // In case the uploaded file path is to be stored in the database 
            $filepath = public_path($location . "/" . $fileNameToStore);
            // Reading file
            $dataAll = fopen($filepath, "r");
            $column=fgetcsv($dataAll);
            while(!feof($dataAll)){
                $inventoryData[]=fgetcsv($dataAll);
            }
            fclose($dataAll); //Close after reading
            Session::put('inventoryCSVData', $inventoryData);
            Session::put('inventoryCSVfile', $filepath);
            $data = $this->inventoryUpdateInDB($inventoryData);
            return redirect()->back()->with($data);
             
        }else{
            return redirect()->back()->with('error', __('Empty file uploaed.'));
        }
    }
    public function bulkInventoryStore(Request $request)
    {
    	$inventoryData = Session::get('inventoryCSVData');
 		foreach ($inventoryData as $key => $importData) { 
            if(!empty($importData)) {
                $customerNumber = $importData[0];
                $userInfo = User::whereJsonContains('encompass_user->customerNumber', $customerNumber)->get();
				if(count($userInfo)){
					//\DB::enableQueryLog(); // Enable query log

					$truckInfo = Encompasses::where('user_id', $userInfo[0]->id)->where('truckName', $importData[1])->whereJsonContains('binName', ['bin_name'=>$importData[2]])->get();
					//dd(\DB::getQueryLog()); // Show results of log
					if(count($truckInfo)){
						$binsInfo = json_decode($truckInfo[0]->binName,true);
						$index  = array_search($importData[2], array_column($binsInfo, 'bin_name'));
						$binID  = $binsInfo[$index]['id'];
						$inventry = Inventries::where('truck_id'  , $truckInfo[0]->id)
			             ->where('bin_id'    , $binID)
			             ->where('item_code' , $importData[3])
			             ->where('basePN' , $importData[6])
			             ->get();
				        if(count($inventry)!= 0){
				            $inventry      = Inventries::findOrFail($inventry[0]->id);
				        }else{
				        	$newData[] = $truckInfo[0]->id;
				            $inventry = new Inventries();
				        }
				        	$inventry->truck_id = $truckInfo[0]->id;
				            $inventry->bin_id   = $binID;
				            $inventry->user_id  = $userInfo[0]->id;
				            $inventry->item_code = $importData[3];
				            $inventry->item_name = $importData[4];	
				            $inventry->quantity = $importData[5];			            
				            $inventry->basePN = $importData[6];
				            $inventry->brand_name = $importData[7];
				            $inventry->description = $importData[8];
				            $inventry->item_price = $importData[9];
				            $inventry->setting = $importData[10];
				            $inventry->fix_quantity = $importData[11];
				            $inventry->item_image = $importData[12];
				            $inventry->save();
					}
				}

            }
        }
        return redirect()->back()->with('success', __('Successfully Uploaded.'));;

    }
    public function deleteuploadCsv(Request $request)
    {
    	$inventryCSVFileStored = Session::get('inventoryCSVfile');
    	$inventryCSVFile = $inventryCSVFileStored;
    	if (File::exists($inventryCSVFile)) {
        //File::delete($image_path);
        unlink($inventryCSVFile);
    	}
    	return redirect()->back();
    }
    public function inventoryUpdateInDB($inventoryData)
    {

    	$invalidUser = []; $invalidData = []; $existingData = [] ; $newData = [];

        foreach ($inventoryData as $key => $importData) {
            if(!empty($importData)) {
                $customerNumber = $importData[0];
                $userInfo = User::whereJsonContains('encompass_user->customerNumber', $customerNumber)->get();
				if(count($userInfo)){
					//\DB::enableQueryLog(); // Enable query log
					$truckInfo = Encompasses::where('user_id', $userInfo[0]->id)->where('truckName', $importData[1])->whereJsonContains('binName', ['bin_name'=>$importData[2]])->get();
					//dd(\DB::getQueryLog()); // Show results of log
					if(count($truckInfo)){
						$binsInfo = json_decode($truckInfo[0]->binName,true);
						$index  = array_search($importData[2], array_column($binsInfo, 'bin_name'));
						$binID  = $binsInfo[$index]['id'];
						$inventry = Inventries::where('truck_id'  , $truckInfo[0]->id)
			             ->where('bin_id'    , $binID)
			             ->where('item_code' , $importData[3])
			             ->where('basePN' , $importData[6])
			             ->get();
				        if(count($inventry)!= 0){
				        	$existingData[] = $truckInfo[0]->id;
				            //$inventry      = Inventries::findOrFail($inventry[0]->id);
				        }else{
				        	$newData[] = $truckInfo[0]->id;
				            //$inventry = new Inventries();
				        }
				        	/*$inventry->truck_id = $truckInfo[0]->id;
				            $inventry->bin_id   = $binID;
				            $inventry->user_id  = $userInfo[0]->id;
				            $inventry->item_code = $importData[3];
				            $inventry->item_name = $importData[4];	
				            $inventry->quantity = $importData[5];			            
				            $inventry->basePN = $importData[6];
				            $inventry->brand_name = $importData[7];
				            $inventry->description = $importData[8];
				            $inventry->item_price = $importData[9];
				            $inventry->setting = $importData[10];
				            $inventry->fix_quantity = $importData[11];
				            $inventry->item_image = $importData[12];
				            $inventry->save();*/
					}else{
						$invalidData[] = $importData[1];
					}
				}else{
					$invalidUser[] = $customerNumber;
				}
            }
        }
        return $data = ['getDataCount'=> 'true', 'invalidUser' => count($invalidUser), 'existingData' => count($existingData), 'newData' => count($newData), 'invalidData' => count($invalidData)];

    }
}