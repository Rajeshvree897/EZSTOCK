<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Inventries;
use App\Notification_histories;
use App\Orders;
use App\User;

class AutoReplanishmentController extends Controller
{
    public function autoReplanishment(){
        $allUsers = User::get();
        foreach ($allUsers as  $user) {
            
            $getAllInventoriesWithUsers = Inventries::where('user_id', $user->id)->groupBy('item_code')
           ->selectRaw('SUM(quantity) as total_quantity, item_code')->get();        
            if(!is_null($getAllInventoriesWithUsers)){
                foreach ($getAllInventoriesWithUsers as $inv => $invetory) {
                   $itemDetails = Inventries::with('order_inventory')->where('user_id', $user->id)->where('item_code', $invetory->item_code)->get();

                   $autoOrders  = Orders::where('user_id', $user->id)
                   ->where('order_basePN', $itemDetails[0]->item_code)
                   ->where('status', 'success')
                   ->where('type', 'auto')->first();
                   $setting         = $itemDetails[0]->setting;
                   $settingQuantity = $itemDetails[0]->fix_quantity;
                   $currenyQuantity = $invetory->total_quantity ;
                   $ordersend = false;
                   if($setting == 'LessThan' && $currenyQuantity < $settingQuantity){
                        $orderQuantity = $settingQuantity - $currenyQuantity;
                        $ordersend = true;
                    }else{
                        if($setting == 'Equal' && $currenyQuantity = $settingQuantity){
                            $orderQuantity = 1;
                            $ordersend = true;

                        }
                    }
                    if(!is_null($autoOrders)){
                        $orderStatusData = $this->getOrderStatus($user , $autoOrders->order_id);
                        $orderStatus = $orderStatusData->object()->data;
                        if($orderStatusData->status() != 400){
                            $status = $orderStatus->records[0]->parts[0]->status;
                            if($status == 'SHIPPED'){
                                $ordersend =   true;
                            }else{
                                $ordersend = false;
                            }
                        }else{
                            $ordersend = false;
                        }
                    }
                    if($ordersend){
                        $getOrderDetails = $this->orderDetails($user, $itemDetails, $orderQuantity);
                        if(count($getOrderDetails) != 0){
                            $createAutoOrder = $this->createOrder($getOrderDetails, $user->id);
                        }
                   }
                }
            }
        }

    }

    public function getOrderStatus($user, $order_id)
    {
        if(!is_null($user->encompass_user) || !empty($user->encompass_user)){
            // $endPoint = config('app.ENCOMPASS_ORDER_STATUS_API');
            $endPoint = config('custom.encompass_order_status_api');                                                                                                                                                                                      
            $userEncompassDetails = json_decode($user->encompass_user, true);
            $params = [
                "settings" => [
                    "jsonUser" => "SIMPLYPARTS",
                    "jsonPassword" => "YU4ZGF47GND8",
                    "customerNumber"=> $userEncompassDetails['customerNumber'],
                    "customerPassword" => $userEncompassDetails['customerPassword'],
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

    public function createOrder($checkoutData, $user_id){
        // $endPoint = config('app.ENCOMPASS_ORDER_CREATE_API');  
        $endPoint = config('custom.encompass_order_create_api');
        $response = Http::post($endPoint, $checkoutData);
        $responsedata = $response->object();
        $res_data = $response->object()->data;
            $jsonData = [
            'status'  => $response->object()->status,
            'code'    => $response->status(),
            'data'    => $res_data,
            ];
            $order = new Orders();
            if($response->object()->status->errorMessage == "SUCCESS"){
                $this->google_notification($checkoutData['data']['referenceNumber1'], $user_id, $checkoutData['data']['parts'][0]['partNumber']);
                $res_status = "success";
            }else{
                $res_status = "failed";
            }

            $order->shipping_method = $checkoutData['data']['shippingMethod'];
            $order->user_id         = $user_id;
            $order->order_id        = $checkoutData['data']['referenceNumber1'];
            $order->address         = json_encode($checkoutData['data']['shipToAddress']);
            $order->total           = $checkoutData['data']['parts'][0]['requestedPrice'];
            $order->item_details    = json_encode($checkoutData['data']);
            $order->status          = $res_status;
            $order->request_parameter = json_encode($checkoutData);
            $order->response = json_encode($responsedata);
            $order->type = 'auto';
            $order->order_basePN =  $checkoutData['data']['parts'][0]['partNumber'];
            $order->save();
             $jsonData = [
                'status'  => $response->object()->status,
                'data'    => $response->object()->data,
                'user_id' => $user_id
                ];
            return response()->json($jsonData);
    }

    public function google_notification($order_id, $user_id, $item_code){
        $user = User::where('id', $user_id)->get();
        $fcm_keys = json_decode($user[0]->fcm_key);
        $endPoint = "https://fcm.googleapis.com/fcm/send";
         //$endPoint = config('app.GOOGLE_NOTOFICATION');
        $auth = [
                "Content-Type" => "application/json",
                "Authorization" => "key=AAAAoy51Ap4:APA91bHbh5FUCCr7p9LMZraNTEqgRienReURQybjL992zEOiG5R27vzwtPDNzXQFwmph9n_ktrUiqLQovSFBGtOLWmODaXxioaup-VQWE1ZebYB2TKxDbHc4-paMFygWhl_6_lBiUd_-"
            ];
        for($count=0; $count <count($fcm_keys); $count++){
            $params = [
                "registration_ids"=>[$fcm_keys[$count]],
                "notification" => [
                        "body"=> "#".$order_id. " Your order for item " .$item_code. " has been received via auto-replenishment" ,
                        "title"=> "Order confirmed."
                    ],
                "data"=> [
                    /*"body"=> "Hi From API Send July 23, 2019",
                    "title"=> "From PostMan Sending API July 23, 2019",
                    "key_3"=> "Hello_value",*/
                    "image"=> ''
                    ]

            ];
            $response = Http::withHeaders($auth)->post($endPoint, $params
            );
            if($response->object()->success){
                $history = Notification_histories::create(
                                [
                                    'user_id' => $user_id,
                                    'notification_details' => json_encode($params)
                                ]
                            );
            }
        }
    }

    public function orderDetails($user, $itemDetails,$orderQuantity){
        $details = [];
        if(!is_null($user->encompass_user) || !empty($user->encompass_user)){
            $userEncompassDetails = json_decode($user->encompass_user, true);
            $address = explode(",", $user->address);
            $orderNumber = "MB".$user->id.strtotime("now");
            $details = [
                "settings" => [
                    "jsonUser" => "SIMPLYPARTS",
                    "jsonPassword" => "YU4ZGF47GND8",
                    "customerNumber"=> $userEncompassDetails['customerNumber'],
                    "customerPassword" => $userEncompassDetails['customerPassword'],
                    "programName" => "JSON.ORDER.CREATE"

                ],
                "data"=>[
                    "customerType"=> null,
                    "useCustomerCross" => null,
                    "referenceNumber1" => $orderNumber,
                    "transactionID"=> null,
                    "referenceNumber2"=> null,
                    "shippingMethod"=> "4",
                    "shippingThirdPartyNumber"=> null,
                    "shippingThirdPartyCarrier"=> null,
                    "blindShip"=> null,
                    "residentialAddress"=> "y",
                    "requireSignature"=> "n",
                    "shipComplete"=> "n",
                    "requestLocationNumber"=> "1",
                    "shipToAddress"=> [
                        "name"=> $userEncompassDetails['customerEmailAddress'],
                        "address1"=> $address[0],
                        "address2"=> $address[0],
                        "city"=> $address[1],
                        "state"=> $address[2],
                        "zipCode"=> $address[3],
                        "countryCode"=> $address[4],
                        "phoneNumber"=> null
                    ],
                    "requestReturnService" => null,
                    "emailAddress"=> $userEncompassDetails['customerEmailAddress'],
                    "parts"=> [
                            [
                                "basePN"            => $itemDetails[0]->basePN,
                                "partNumber"        => $itemDetails[0]->item_code,
                                "partDescription"   => $itemDetails[0]->description,
                                "orderQuantity"     => $orderQuantity,
                                "requestedPrice"    => $itemDetails[0]->item_price*$orderQuantity,
                                "mfgCode"           => null,
                                "authorizationOrReferenceNumber" => null,
                                "claimsProcessorCode" =>null,
                                "claimNumber"      => null,
                                "referenceNumber1" => $orderNumber,
                                "requestLocationNumber" => 4,
                                "pickupLocation"     => "Las Vegas, NV",
                                "itemImage"          => $itemDetails[0]->item_image,
                                "manufacturer"       => $itemDetails[0]->brand_name,
                            ]
                    ]
                ]
            ];
        }
        return $details;

    }
}