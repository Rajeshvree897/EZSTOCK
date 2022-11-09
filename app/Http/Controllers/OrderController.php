<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\User;
use App\Orders;
use App\Inventries;
use App\Notification_histories;


class OrderController extends Controller
{
    public function checkout(Request $request,$user_id){
        $data = $request->all();
        $checkoutData = $data;
        //count total 
        $parts = $data['data']['parts'];

        $total = 0;
        for ($i=0; $i < count($parts); $i++) { 

           $total =  $total+$parts[$i]['requestedPrice'];
           unset($data["data"]["parts"][$i]['itemImage']);
        }
        // $endPoint = "https://encompass.com/restfulservice/createOrder";
        $endPoint = config('custom.encompass_order_create_api');
        $response = Http::post($endPoint, $data);
        $responsedata = $response->object();
        $res_data = $response->object()->data;
            $jsonData = [
            'status'  => $response->object()->status,
            'code'    => $response->status(),
            'data'    => $res_data,
            ];
            $order = new Orders();
            if($response->object()->status->errorMessage == "SUCCESS"){
                $this->google_notification($data['data']['referenceNumber1'], $user_id);
                $res_status = "success";
            }else{
                $res_status = "failed";
            }

            $order->shipping_method = $checkoutData['data']['shippingMethod'];
            $order->user_id         = $user_id;
            $order->order_id        = $checkoutData['data']['referenceNumber1'];
            $order->address         = json_encode($checkoutData['data']['shipToAddress']);
            $order->total           = $total;//$data['checkout_details']['total'];
            $order->item_details    = json_encode($checkoutData['data']);
            $order->status          = $res_status;
            $order->request_parameter = json_encode($checkoutData);
            $order->response = json_encode($responsedata);
            //$response->object()->status->errorMessage;
            $order->save();
             $jsonData = [
                'status'  => $response->object()->status,
                'data'    => $response->object()->data,
                'user_id' => $user_id
                ];
        /*$checkout_data = [
        'status'    => 'success',
        'code'      => 200,
        'item'      => [
            'id'            => $order->id,
            'user_id'       => $order->user_id,
            'order_id'       => $order->order_id,
            'shipping_method'=> $order->shipping_method,
            'address'       => $order->address,
            'total'         => $order->total,
            'item_details'  => json_decode($order->item_details),
            'status'        => $order->status
            ]
        ];*/
        return response()->json($jsonData);
    }
    public function google_notification($order_id, $user_id){
        $user = User::where('id', $user_id)->get();
        $fcm_keys = json_decode($user[0]->fcm_key);
        $endPoint = "https://fcm.googleapis.com/fcm/send";

        $auth = [
                "Content-Type" => "application/json",
                "Authorization" => "key=AAAAoy51Ap4:APA91bHbh5FUCCr7p9LMZraNTEqgRienReURQybjL992zEOiG5R27vzwtPDNzXQFwmph9n_ktrUiqLQovSFBGtOLWmODaXxioaup-VQWE1ZebYB2TKxDbHc4-paMFygWhl_6_lBiUd_-"
            ];
        for($count=0; $count <count($fcm_keys); $count++){
            $params = [
                "registration_ids"=>[$fcm_keys[$count]],
                "notification" => [
                        "body"=> "#".$order_id."your order is confirmed",
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
      
    public function status(Request $request){
        $data = $request->all();
        $endPoint = config('custom.encompass_order_status_api');
        // $endPoint = "https://encompass.com/restfulservice/orderStatus";
        $response = Http::post($endPoint, $data);
        $responsedata = $response->object();
        $res_data = $response->object()->data;
        $jsonData = [
                'status'  => $response->object()->status,
                'data'    => $response->object()->data,
                ];
        return response()->json($jsonData);


    }

    public function orders($user_id){
        $getOrders = Orders::where('user_id', $user_id)->orderBy('id', 'DESC')->get();
        if(count($getOrders) != 0){

            for ($data=0; $data < count($getOrders) ; $data++) { 
                $allData[] = [
               
                    'id'                => $getOrders[$data]->id,
                    'user_id'           => $getOrders[$data]->user_id,
                    'order_id'          => $getOrders[$data]->order_id,
                    'item_details'      => json_decode($getOrders[$data]->item_details),
                    'total'             => $getOrders[$data]->total,
                    'shipping_method'   => $getOrders[$data]->shipping_method,
                    'address'           => $getOrders[$data]->address,
                    'status'            => $getOrders[$data]->status,
                    'created_at'        => $getOrders[$data]->created_at,
                    'updated_at'        => $getOrders[$data]->updated_at
                    
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

    public function weeklyReport(Request $request, $user_id){
        $data = $request->data;
        $weekStart = $data['weekStart'];  //"2022-06-01"; 
        $weekEnd   =  $data['weekEnd']; //"2022-06-30";
        $weeklyOrders = Orders::whereBetween(
                                'created_at', [
                                          $weekStart,
                                          $weekEnd,
                                      ]
                                )->where('status', 'success')->where('user_id', $user_id)->get();
        $weeklyOrdersWithLoc = [] ;
        foreach ($weeklyOrders as $order) {
            $orderData = json_decode($order->request_parameter, true);
            foreach ($orderData['data']['parts'] as $part) {
                $pickUpLoaction = str_replace(' ', '_',str_replace(', ', '-', $part['pickupLocation']));
                if(!is_null($pickUpLoaction)){
                    if(array_key_exists($pickUpLoaction, $weeklyOrdersWithLoc)){
                        $index  = array_search($part['partNumber'], array_column($weeklyOrdersWithLoc[$pickUpLoaction], 'partNumber'));

                        if($index || $index === 0){
                            if($part['mfgCode'] == $weeklyOrdersWithLoc[$pickUpLoaction][$index]['mfgCode'] &&
                                  $part['basePN'] == $weeklyOrdersWithLoc[$pickUpLoaction][$index]['basePN'] &&
                                  $part['partNumber'] == $weeklyOrdersWithLoc[$pickUpLoaction][$index]['partNumber']){
                                    $addQuantity= $part['orderQuantity'] + $weeklyOrdersWithLoc[$pickUpLoaction][$index]['orderQuantity'];
                                    $addItemPrice = $part['requestedPrice'] + $weeklyOrdersWithLoc[$pickUpLoaction][$index]['requestedPrice'];
                                    $weeklyOrdersWithLoc[$pickUpLoaction][$index]['orderQuantity'] = $addQuantity;
                                    $weeklyOrdersWithLoc[$pickUpLoaction][$index]['requestedPrice'] = $addItemPrice;


                            }else{
                                $weeklyOrdersWithLoc[$pickUpLoaction][] = $this->partCallectionAsLocation($part);;
                            }
                        }else{
                            $weeklyOrdersWithLoc[$pickUpLoaction][] = $this->partCallectionAsLocation($part);
                        }
                    }else{
                        $weeklyOrdersWithLoc[$pickUpLoaction][] = $this->partCallectionAsLocation($part);;
                    }
                }

            }
        }
        $jsonData = [
                'status' => 'success',
                'code' => 200,
                'item' => $weeklyOrdersWithLoc,
        ]; 
        return response()->json($jsonData);  

    }
    public function weeklyCheckout(Request $request, $user_id){
        $data = $request->all();
        $checkoutData = $data;
        $Alldata = [];
        foreach($data['data']['parts'] as $partWithLocation){
            $data['data']['parts'] = $partWithLocation[1];
            $LocationCode = explode('-', $partWithLocation[0]);
            $data['data']['referenceNumber1'] =  $checkoutData['data']['referenceNumber1']."-".$LocationCode[1];
            //count total 
            $parts = $data['data']['parts'];
            $total = 0;
            for ($i=0; $i < count($parts); $i++) { 

               $total =  $total+$parts[$i]['requestedPrice'];
               if(isset($data["data"]["parts"][$i]) && $data["data"]["parts"][$i]['orderQuantity'] == 0){
                   unset($data["data"]["parts"][$i]);
               }
               $PartsFilterData = array_values($data["data"]["parts"]);
                unset($data["data"]["parts"][$i]['itemImage']);
            }
                $data["data"]["parts"] = array_values($data["data"]["parts"]);
                if(!empty($data["data"]["parts"])){

                    // $endPoint = "https://encompass.com/restfulservice/createOrder";
                    $endPoint = config('custom.encompass_order_create_api');
                    $response = Http::post($endPoint, $data);
                    $responsedata = $response->object();
                    $res_data = $response->object()->data;
                    $jsonData = [
                    'status'  => $response->object()->status,
                    'code'    => $response->status(),
                    'data'    => $res_data,
                    ];
                    $order = new Orders();
                    if($response->object()->status->errorMessage == "SUCCESS"){
                        $this->google_notification($data['data']['referenceNumber1'], $user_id);
                        $res_status = "success";
                    }else{
                        $res_status = "failed";
                    }

                    $order->shipping_method = $data['data']['shippingMethod'];
                    $order->user_id         = $user_id;
                    $order->order_id        = $data['data']['referenceNumber1'];
                    $order->address         = json_encode($data['data']['shipToAddress']);
                    $order->total           = $total;//$data['checkout_details']['total'];
                    $order->item_details    = json_encode($data['data']);
                    $order->status          = $res_status;
                    $order->request_parameter = json_encode($data);
                    $order->response = json_encode($responsedata);
                    //$response->object()->status->errorMessage;
                    $order->save();
                        $Alldata[] = [
                            "location" => $partWithLocation[0],
                            "parts"   => $PartsFilterData,
                            'orderId' => $data['data']['referenceNumber1'],
                            'status'  => $response->object()->status,
                            'data'    => $response->object()->data,
                            'user_id' => $user_id
                        ];

                }
                
        }
        return response()->json($Alldata);  

    }

    public function partCallectionAsLocation($part){
        $locationOderArr = [
            "basePN"                         => $part['basePN'],
            "mfgCode"                        => $part['mfgCode'],
            'orderQuantity'                  => $part['orderQuantity'],
            "partNumber"                     =>  $part['partNumber'],
            'requestedPrice'                 => $part['requestedPrice'],
            'pickupLocation'                 => $part['pickupLocation'],
            'itemImage'                      => $part['itemImage'],
            "partDescription"                => isset($part['partDescription'])? $part['partDescription'] : "",
            "authorizationOrReferenceNumber" => isset($part['authorizationOrReferenceNumber'])? $part['authorizationOrReferenceNumber'] : "",
            "claimsProcessorCode"            => isset($part['claimsProcessorCode']) ? $part['claimsProcessorCode'] : "",
            "claimNumber"                    => isset($part['claimNumber']) ? $part['claimNumber'] : "" ,
            "referenceNumber1"               => isset($part['referenceNumber1'] ) ? $part['referenceNumber1'] : "",
            "requestLocationNumber"          => isset($part['requestLocationNumber']) ? $part['requestLocationNumber'] : "",
            "manufacturer"                   => isset($part['manufacturer'] ) ? $part['manufacturer'] : ""
        ];
        return $locationOderArr;

    }
    public function orderBrands(){
        $allBrands = [
            [
                "brand_name"=>"All Brands",
                "brand_code"=>""
            ],
            [
            "brand_name"=>"Electrolux",
            "brand_code"=>"FRI"
            ],
            [
            "brand_name"=>"GE",
            "brand_code"=>"HOT"
            ],
            [
            "brand_name"=>"HP",
            "brand_code"=>"HEW"
            ],
            [
            "brand_name"=>"Lenovo",
            "brand_code"=>"LEN"
            ],
            [
            "brand_name"=>"LG",
            "brand_code"=>"ZEN"
            ],
            [
            "brand_name"=>"Midea",
            "brand_code"=>"MID"
            ],
            [
            "brand_name"=>"Norelco",
            "brand_code"=>"NOR"
            ],
            [
            "brand_name"=>"Panasonic",
            "brand_code"=>"MSC"
            ],
            [
            "brand_name"=>"Samsung",
            "brand_code"=>"SMG"
            ],
            [
            "brand_name"=>"Sony",
            "brand_code"=>"SON"
            ],
            [
            "brand_name"=>"Whirlpool",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Element",
            "brand_code"=>"ELE"
            ],
            [
            "brand_name"=>"3MFiltreteProducts",
            "brand_code"=>"MMM"
            ],
            [
            "brand_name"=>"Abbott",
            "brand_code"=>"ABT"
            ],
            [
            "brand_name"=>"Acer",
            "brand_code"=>"ACR"
            ],
            [
            "brand_name"=>"Acp",
            "brand_code"=>"ACP"
            ],
            [
            "brand_name"=>"AeonAir",
            "brand_code"=>"ANR"
            ],
            [
            "brand_name"=>"Aiphone",
            "brand_code"=>"AIP"
            ],
            [
            "brand_name"=>"Airtouch",
            "brand_code"=>"ACE"
            ],
            [
            "brand_name"=>"Fedders",
            "brand_code"=>"AIR"
            ],
            [
            "brand_name"=>"Aiwa",
            "brand_code"=>"AIW"
            ],
            [
            "brand_name"=>"Akai",
            "brand_code"=>"AKI"
            ],
            [
            "brand_name"=>"Alaris",
            "brand_code"=>"ALR"
            ],
            [
            "brand_name"=>"Alpine",
            "brand_code"=>"ALP"
            ],
            [
            "brand_name"=>"AmericanHakko",
            "brand_code"=>"HAK"
            ],
            [
            "brand_name"=>"Anderic",
            "brand_code"=>"NPS"
            ],
            [
            "brand_name"=>"Aoc/epi",
            "brand_code"=>"AOC"
            ],
            [
            "brand_name"=>"Apple",
            "brand_code"=>"APL"
            ],
            [
            "brand_name"=>"AppleReplacement",
            "brand_code"=>"APA"
            ],
            [
            "brand_name"=>"ApplianceParts",
            "brand_code"=>"KIT"
            ],
            [
            "brand_name"=>"ArclyteTechnologies",
            "brand_code"=>"ARC"
            ],
            [
            "brand_name"=>"Asti",
            "brand_code"=>"AST"
            ],
            [
            "brand_name"=>"Asus",
            "brand_code"=>"ASU"
            ],
            [
            "brand_name"=>"Avanti",
            "brand_code"=>"AVA"
            ],
            [
            "brand_name"=>"Avent",
            "brand_code"=>"AVT"
            ],
            [
            "brand_name"=>"BKPrecision",
            "brand_code"=>"BKP"
            ],
            [
            "brand_name"=>"BadPartNumber",
            "brand_code"=>"BAD"
            ],
            [
            "brand_name"=>"BTI",
            "brand_code"=>"BTI"
            ],
            [
            "brand_name"=>"Baxter",
            "brand_code"=>"BAX"
            ],
            [
            "brand_name"=>"BaystatePool",
            "brand_code"=>"SPA"
            ],
            [
            "brand_name"=>"BeckmanIndustrial",
            "brand_code"=>"BEC"
            ],
            [
            "brand_name"=>"Beko",
            "brand_code"=>"BEK"
            ],
            [
            "brand_name"=>"BeltAndVcrAcc.",
            "brand_code"=>"PRB"
            ],
            [
            "brand_name"=>"BenqUsa",
            "brand_code"=>"BEN"
            ],
            [
            "brand_name"=>"Bertazzoni",
            "brand_code"=>"BRT"
            ],
            [
            "brand_name"=>"BinatoneElectronics",
            "brand_code"=>"BQN"
            ],
            [
            "brand_name"=>"Blomberg",
            "brand_code"=>"BLM"
            ],
            [
            "brand_name"=>"BLUProducts",
            "brand_code"=>"BLU"
            ],
            [
            "brand_name"=>"Bosch",
            "brand_code"=>"BCH"
            ],
            [
            "brand_name"=>"BostonAcoustics",
            "brand_code"=>"BSA"
            ],
            [
            "brand_name"=>"Bowers&Wilkins",
            "brand_code"=>"BWL"
            ],
            [
            "brand_name"=>"Boxes",
            "brand_code"=>"BOX"
            ],
            [
            "brand_name"=>"Braun",
            "brand_code"=>"BRA"
            ],
            [
            "brand_name"=>"Braun",
            "brand_code"=>"BBN"
            ],
            [
            "brand_name"=>"BriggsStratton",
            "brand_code"=>"BSP"
            ],
            [
            "brand_name"=>"BriteliteRefurbish",
            "brand_code"=>"BLR"
            ],
            [
            "brand_name"=>"Broan",
            "brand_code"=>"BRN"
            ],
            [
            "brand_name"=>"Broksonic",
            "brand_code"=>"BRK"
            ],
            [
            "brand_name"=>"Brookstone",
            "brand_code"=>"BKS"
            ],
            [
            "brand_name"=>"Brother",
            "brand_code"=>"BRO"
            ],
            [
            "brand_name"=>"Canon",
            "brand_code"=>"CAN"
            ],
            [
            "brand_name"=>"CanonCamera",
            "brand_code"=>"CA2"
            ],
            [
            "brand_name"=>"CanonConsumer",
            "brand_code"=>"CA1"
            ],
            [
            "brand_name"=>"Carrier",
            "brand_code"=>"CRR"
            ],
            [
            "brand_name"=>"CellPhone",
            "brand_code"=>"CEL"
            ],
            [
            "brand_name"=>"CELLPHONEPARTS",
            "brand_code"=>"SAM"
            ],
            [
            "brand_name"=>"Champion",
            "brand_code"=>"CHA"
            ],
            [
            "brand_name"=>"Chemicals",
            "brand_code"=>"RAW"
            ],
            [
            "brand_name"=>"Chemtronics",
            "brand_code"=>"CHE"
            ],
            [
            "brand_name"=>"Chipquik",
            "brand_code"=>"CHI"
            ],
            [
            "brand_name"=>"Cielo",
            "brand_code"=>"CLO"
            ],
            [
            "brand_name"=>"CCS",
            "brand_code"=>"CCS"
            ],
            [
            "brand_name"=>"Clarion",
            "brand_code"=>"CLR"
            ],
            [
            "brand_name"=>"CompatibleAppliance",
            "brand_code"=>"SPC"
            ],
            [
            "brand_name"=>"Compaq",
            "brand_code"=>"CPQ"
            ],
            [
            "brand_name"=>"ComputerandMobile",
            "brand_code"=>"COM"
            ],
            [
            "brand_name"=>"ComputerCables",
            "brand_code"=>"QVS"
            ],
            [
            "brand_name"=>"ComputerParts",
            "brand_code"=>"IMT"
            ],
            [
            "brand_name"=>"ComputerSoftware",
            "brand_code"=>"QUE"
            ],
            [
            "brand_name"=>"Cooper",
            "brand_code"=>"COO"
            ],
            [
            "brand_name"=>"CrownElectronics",
            "brand_code"=>"CRO"
            ],
            [
            "brand_name"=>"Curlin",
            "brand_code"=>"CUR"
            ],
            [
            "brand_name"=>"GreenChoiceParts",
            "brand_code"=>"CKP"
            ],
            [
            "brand_name"=>"D&rAssociatesInc.",
            "brand_code"=>"DRA"
            ],
            [
            "brand_name"=>"Dacor",
            "brand_code"=>"DAC"
            ],
            [
            "brand_name"=>"Danby",
            "brand_code"=>"DBY"
            ],
            [
            "brand_name"=>"DantonaIndustries",
            "brand_code"=>"DAN"
            ],
            [
            "brand_name"=>"Dell",
            "brand_code"=>"DEL"
            ],
            [
            "brand_name"=>"Delonghi",
            "brand_code"=>"DEI"
            ],
            [
            "brand_name"=>"Denon",
            "brand_code"=>"DEN"
            ],
            [
            "brand_name"=>"DexRefurbished",
            "brand_code"=>"DXR"
            ],
            [
            "brand_name"=>"Digitenna",
            "brand_code"=>"DIG"
            ],
            [
            "brand_name"=>"DngoInternational",
            "brand_code"=>"DNG"
            ],
            [
            "brand_name"=>"DRPowerEquipment",
            "brand_code"=>"DRE"
            ],
            [
            "brand_name"=>"DSS",
            "brand_code"=>"DSS"
            ],
            [
            "brand_name"=>"DuracellU.S.A.",
            "brand_code"=>"DUR"
            ],
            [
            "brand_name"=>"E-machine",
            "brand_code"=>"EMA"
            ],
            [
            "brand_name"=>"Eclipse",
            "brand_code"=>"ECL"
            ],
            [
            "brand_name"=>"Frigidaire",
            "brand_code"=>"FRI"
            ],
            [
            "brand_name"=>"Gibson",
            "brand_code"=>"FRI"
            ],
            [
            "brand_name"=>"Kelvinator",
            "brand_code"=>"FRI"
            ],
            [
            "brand_name"=>"Philco",
            "brand_code"=>"FRI"
            ],
            [
            "brand_name"=>"Tappan",
            "brand_code"=>"FRI"
            ],
            [
            "brand_name"=>"White-Westinghouse",
            "brand_code"=>"FRI"
            ],
            [
            "brand_name"=>"ElectronicResources",
            "brand_code"=>"ELR"
            ],
            [
            "brand_name"=>"Elica",
            "brand_code"=>"ELI"
            ],
            [
            "brand_name"=>"EmachinesRefurb",
            "brand_code"=>"EMR"
            ],
            [
            "brand_name"=>"Emerson",
            "brand_code"=>"EME"
            ],
            [
            "brand_name"=>"EmpireVideo",
            "brand_code"=>"EMP"
            ],
            [
            "brand_name"=>"EncompassParts",
            "brand_code"=>"EPD"
            ],
            [
            "brand_name"=>"EncompassService",
            "brand_code"=>"ESS"
            ],
            [
            "brand_name"=>"Envision",
            "brand_code"=>"ENV"
            ],
            [
            "brand_name"=>"Epson",
            "brand_code"=>"EPS"
            ],
            [
            "brand_name"=>"EspressoAccessories",
            "brand_code"=>"ESP"
            ],
            [
            "brand_name"=>"Eureka",
            "brand_code"=>"ERK"
            ],
            [
            "brand_name"=>"EvereadyBatteryCo.",
            "brand_code"=>"EVE"
            ],
            [
            "brand_name"=>"Fantom",
            "brand_code"=>"FAN"
            ],
            [
            "brand_name"=>"Filters",
            "brand_code"=>"ECO"
            ],
            [
            "brand_name"=>"Fisher/Paykel",
            "brand_code"=>"FAP"
            ],
            [
            "brand_name"=>"Fluke",
            "brand_code"=>"FLU"
            ],
            [
            "brand_name"=>"VIZIO-FOXCONN",
            "brand_code"=>"FOX"
            ],
            [
            "brand_name"=>"FrontProjectorLamp",
            "brand_code"=>"FPL"
            ],
            [
            "brand_name"=>"FrsRadio",
            "brand_code"=>"TKK"
            ],
            [
            "brand_name"=>"Fuji-xeroxArtermark",
            "brand_code"=>"FXA"
            ],
            [
            "brand_name"=>"Fujitsu",
            "brand_code"=>"FJT"
            ],
            [
            "brand_name"=>"Funai",
            "brand_code"=>"FUN"
            ],
            [
            "brand_name"=>"GamingParts",
            "brand_code"=>"GAM"
            ],
            [
            "brand_name"=>"Garmin",
            "brand_code"=>"GAR"
            ],
            [
            "brand_name"=>"Gateway",
            "brand_code"=>"GAT"
            ],
            [
            "brand_name"=>"Gc-thorsen",
            "brand_code"=>"GCE"
            ],
            [
            "brand_name"=>"Hotpoint",
            "brand_code"=>"HOT"
            ],
            [
            "brand_name"=>"Monogram",
            "brand_code"=>"HOT"
            ],
            [
            "brand_name"=>"GESmallAppliance",
            "brand_code"=>"GSA"
            ],
            [
            "brand_name"=>"Electrosound",
            "brand_code"=>"ELS"
            ],
            [
            "brand_name"=>"Generic",
            "brand_code"=>"LPU"
            ],
            [
            "brand_name"=>"GenericComputer",
            "brand_code"=>"KAS"
            ],
            [
            "brand_name"=>"Genie",
            "brand_code"=>"GNE"
            ],
            [
            "brand_name"=>"GetItClean",
            "brand_code"=>"GIC"
            ],
            [
            "brand_name"=>"Gillette",
            "brand_code"=>"GIL"
            ],
            [
            "brand_name"=>"GoPlusLighting",
            "brand_code"=>"GOP"
            ],
            [
            "brand_name"=>"GoVideo",
            "brand_code"=>"GOV"
            ],
            [
            "brand_name"=>"GoliathSmartPhones",
            "brand_code"=>"GOL"
            ],
            [
            "brand_name"=>"Goodman",
            "brand_code"=>"GOO"
            ],
            [
            "brand_name"=>"Google",
            "brand_code"=>"GGL"
            ],
            [
            "brand_name"=>"GpsBatteries",
            "brand_code"=>"GPS"
            ],
            [
            "brand_name"=>"GpxRemotes",
            "brand_code"=>"GPX"
            ],
            [
            "brand_name"=>"GreenChoice",
            "brand_code"=>"CGI"
            ],
            [
            "brand_name"=>"GreenChoiceParts",
            "brand_code"=>"GCP"
            ],
            [
            "brand_name"=>"GreenChoice",
            "brand_code"=>"REB"
            ],
            [
            "brand_name"=>"GreenChoice",
            "brand_code"=>"RTL"
            ],
            [
            "brand_name"=>"GreenChoice",
            "brand_code"=>"SJP"
            ],
            [
            "brand_name"=>"GREENCHOICE",
            "brand_code"=>"SOH"
            ],
            [
            "brand_name"=>"Haier",
            "brand_code"=>"HAI"
            ],
            [
            "brand_name"=>"HaierLiterature",
            "brand_code"=>"HSL"
            ],
            [
            "brand_name"=>"Hannspree",
            "brand_code"=>"HAN"
            ],
            [
            "brand_name"=>"HarmonKardon",
            "brand_code"=>"HAR"
            ],
            [
            "brand_name"=>"Hct",
            "brand_code"=>"HCT"
            ],
            [
            "brand_name"=>"HeatTools",
            "brand_code"=>"DRG"
            ],
            [
            "brand_name"=>"HighCapacity",
            "brand_code"=>"BAT"
            ],
            [
            "brand_name"=>"Hisense",
            "brand_code"=>"HIS"
            ],
            [
            "brand_name"=>"Hitachi",
            "brand_code"=>"HIT"
            ],
            [
            "brand_name"=>"Hiteker",
            "brand_code"=>"HTK"
            ],
            [
            "brand_name"=>"HkcDigital",
            "brand_code"=>"HKC"
            ],
            [
            "brand_name"=>"Hoover",
            "brand_code"=>"HOO"
            ],
            [
            "brand_name"=>"Hospira",
            "brand_code"=>"HOS"
            ],
            [
            "brand_name"=>"OemCompatible",
            "brand_code"=>"HPA"
            ],
            [
            "brand_name"=>"HP/Canon",
            "brand_code"=>"HPC"
            ],
            [
            "brand_name"=>"HewlettPackardRefu",
            "brand_code"=>"HPR"
            ],
            [
            "brand_name"=>"NOTFORRESALE",
            "brand_code"=>"HEM"
            ],
            [
            "brand_name"=>"CELLPHONEPARTS",
            "brand_code"=>"HTC"
            ],
            [
            "brand_name"=>"Hughes",
            "brand_code"=>"HUG"
            ],
            [
            "brand_name"=>"HvacParts",
            "brand_code"=>"GEM"
            ],
            [
            "brand_name"=>"HyundaiCorporation",
            "brand_code"=>"HCU"
            ],
            [
            "brand_name"=>"I-symphony",
            "brand_code"=>"ISY"
            ],
            [
            "brand_name"=>"IBM",
            "brand_code"=>"IBM"
            ],
            [
            "brand_name"=>"Ilo",
            "brand_code"=>"ILO"
            ],
            [
            "brand_name"=>"Import",
            "brand_code"=>"IMP"
            ],
            [
            "brand_name"=>"Impression",
            "brand_code"=>"IPR"
            ],
            [
            "brand_name"=>"Infocus",
            "brand_code"=>"INF"
            ],
            [
            "brand_name"=>"Innolux",
            "brand_code"=>"ILX"
            ],
            [
            "brand_name"=>"Insignia",
            "brand_code"=>"ISG"
            ],
            [
            "brand_name"=>"JacquesEbert",
            "brand_code"=>"JAC"
            ],
            [
            "brand_name"=>"JapaneseSemi's",
            "brand_code"=>"JAP"
            ],
            [
            "brand_name"=>"JBL",
            "brand_code"=>"JBL"
            ],
            [
            "brand_name"=>"JVC",
            "brand_code"=>"JVC"
            ],
            [
            "brand_name"=>"Jvc/amtran",
            "brand_code"=>"JVA"
            ],
            [
            "brand_name"=>"KabaIlco",
            "brand_code"=>"KAB"
            ],
            [
            "brand_name"=>"Karcher",
            "brand_code"=>"KAR"
            ],
            [
            "brand_name"=>"KdsMonitors",
            "brand_code"=>"KDS"
            ],
            [
            "brand_name"=>"KefAudio",
            "brand_code"=>"KEF"
            ],
            [
            "brand_name"=>"Kelon",
            "brand_code"=>"KEL"
            ],
            [
            "brand_name"=>"Kendall",
            "brand_code"=>"KNL"
            ],
            [
            "brand_name"=>"Daewoo",
            "brand_code"=>"DAE"
            ],
            [
            "brand_name"=>"Kenmore",
            "brand_code"=>"KMR"
            ],
            [
            "brand_name"=>"Kenwood",
            "brand_code"=>"KEN"
            ],
            [
            "brand_name"=>"Kenwood",
            "brand_code"=>"KEW"
            ],
            [
            "brand_name"=>"Kodak",
            "brand_code"=>"KOD"
            ],
            [
            "brand_name"=>"KonicaMinolta",
            "brand_code"=>"MIN"
            ],
            [
            "brand_name"=>"Konka",
            "brand_code"=>"KON"
            ],
            [
            "brand_name"=>"KOVA",
            "brand_code"=>"KAT"
            ],
            [
            "brand_name"=>"KyoceraMita",
            "brand_code"=>"KYO"
            ],
            [
            "brand_name"=>"LampOnly",
            "brand_code"=>"LMP"
            ],
            [
            "brand_name"=>"Lasko",
            "brand_code"=>"LAS"
            ],
            [
            "brand_name"=>"LenovoRecertified",
            "brand_code"=>"LEH"
            ],
            [
            "brand_name"=>"Lexmark",
            "brand_code"=>"LEX"
            ],
            [
            "brand_name"=>"Liebherr",
            "brand_code"=>"LIE"
            ],
            [
            "brand_name"=>"Linksys",
            "brand_code"=>"LIN"
            ],
            [
            "brand_name"=>"LiquidVideoRefurb",
            "brand_code"=>"LVR"
            ],
            [
            "brand_name"=>"Littlefuse",
            "brand_code"=>"LIT"
            ],
            [
            "brand_name"=>"LmbHeeger",
            "brand_code"=>"LMB"
            ],
            [
            "brand_name"=>"LokringAppliance",
            "brand_code"=>"LOK"
            ],
            [
            "brand_name"=>"Lowell",
            "brand_code"=>"LOW"
            ],
            [
            "brand_name"=>"LPS",
            "brand_code"=>"LPS"
            ],
            [
            "brand_name"=>"MagicChef",
            "brand_code"=>"MAC"
            ],
            [
            "brand_name"=>"Magnavox",
            "brand_code"=>"MAG"
            ],
            [
            "brand_name"=>"Mallory",
            "brand_code"=>"MAL"
            ],
            [
            "brand_name"=>"Marantz",
            "brand_code"=>"MAR"
            ],
            [
            "brand_name"=>"Marquis",
            "brand_code"=>"MRQ"
            ],
            [
            "brand_name"=>"MassIntegrated",
            "brand_code"=>"MIS"
            ],
            [
            "brand_name"=>"Master",
            "brand_code"=>"MAS"
            ],
            [
            "brand_name"=>"Matsushita",
            "brand_code"=>"MSL"
            ],
            [
            "brand_name"=>"Matv/catvAcc",
            "brand_code"=>"PCO"
            ],
            [
            "brand_name"=>"MaxProfessional",
            "brand_code"=>"MXP"
            ],
            [
            "brand_name"=>"Maxell",
            "brand_code"=>"MAX"
            ],
            [
            "brand_name"=>"Maytag",
            "brand_code"=>"MAY"
            ],
            [
            "brand_name"=>"MCM",
            "brand_code"=>"MCM"
            ],
            [
            "brand_name"=>"Medex",
            "brand_code"=>"MDM"
            ],
            [
            "brand_name"=>"MemoryProducts",
            "brand_code"=>"PAR"
            ],
            [
            "brand_name"=>"MiamiParts",
            "brand_code"=>"MIA"
            ],
            [
            "brand_name"=>"Microsoft",
            "brand_code"=>"MCS"
            ],
            [
            "brand_name"=>"MiracleRemote",
            "brand_code"=>"MIR"
            ],
            [
            "brand_name"=>"Misc",
            "brand_code"=>"SRC"
            ],
            [
            "brand_name"=>"Encompass",
            "brand_code"=>"VBI"
            ],
            [
            "brand_name"=>"Mitsubishi",
            "brand_code"=>"MIT"
            ],
            [
            "brand_name"=>"Moen",
            "brand_code"=>"MOE"
            ],
            [
            "brand_name"=>"MotorolaRefurb",
            "brand_code"=>"MRX"
            ],
            [
            "brand_name"=>"MSI",
            "brand_code"=>"MSI"
            ],
            [
            "brand_name"=>"MtxSpeakers",
            "brand_code"=>"MTX"
            ],
            [
            "brand_name"=>"Muratec",
            "brand_code"=>"MUR"
            ],
            [
            "brand_name"=>"Nady",
            "brand_code"=>"NAD"
            ],
            [
            "brand_name"=>"NEC",
            "brand_code"=>"NEC"
            ],
            [
            "brand_name"=>"NewWaveSales",
            "brand_code"=>"NWS"
            ],
            [
            "brand_name"=>"NextbookUsa",
            "brand_code"=>"NEX"
            ],
            [
            "brand_name"=>"Nexxtech",
            "brand_code"=>"NXX"
            ],
            [
            "brand_name"=>"Nikon",
            "brand_code"=>"NKN"
            ],
            [
            "brand_name"=>"Norelco",
            "brand_code"=>"NCR"
            ],
            [
            "brand_name"=>"NteElectronics",
            "brand_code"=>"NTE"
            ],
            [
            "brand_name"=>"Nukote",
            "brand_code"=>"NUK"
            ],
            [
            "brand_name"=>"Numerex",
            "brand_code"=>"NUM"
            ],
            [
            "brand_name"=>"ObservationStsyems",
            "brand_code"=>"LOR"
            ],
            [
            "brand_name"=>"OfficeSupplies",
            "brand_code"=>"OFC"
            ],
            [
            "brand_name"=>"Okidata",
            "brand_code"=>"OKI"
            ],
            [
            "brand_name"=>"Olevia",
            "brand_code"=>"OLE"
            ],
            [
            "brand_name"=>"Olympus",
            "brand_code"=>"OLY"
            ],
            [
            "brand_name"=>"Onkyo",
            "brand_code"=>"ONK"
            ],
            [
            "brand_name"=>"OpalIcemakers",
            "brand_code"=>"OPL"
            ],
            [
            "brand_name"=>"Optoma",
            "brand_code"=>"OPT"
            ],
            [
            "brand_name"=>"Oral-B",
            "brand_code"=>"ORB"
            ],
            [
            "brand_name"=>"Orion",
            "brand_code"=>"ORI"
            ],
            [
            "brand_name"=>"OSRAM",
            "brand_code"=>"OSR"
            ],
            [
            "brand_name"=>"OsramNeolux",
            "brand_code"=>"NEO"
            ],
            [
            "brand_name"=>"Packaging",
            "brand_code"=>"PKG"
            ],
            [
            "brand_name"=>"Panasonic",
            "brand_code"=>"MSC"
            ],
            [
            "brand_name"=>"Panduit",
            "brand_code"=>"PDT"
            ],
            [
            "brand_name"=>"GreenChoiceParts",
            "brand_code"=>"PPP"
            ],
            [
            "brand_name"=>"Petra",
            "brand_code"=>"PTA"
            ],
            [
            "brand_name"=>"GoLite",
            "brand_code"=>"GLT"
            ],
            [
            "brand_name"=>"Philips",
            "brand_code"=>"PNF"
            ],
            [
            "brand_name"=>"Philips",
            "brand_code"=>"NAP"
            ],
            [
            "brand_name"=>"PhilipsHealthSolut",
            "brand_code"=>"PHS"
            ],
            [
            "brand_name"=>"PhilipsHealthyCook",
            "brand_code"=>"PHC"
            ],
            [
            "brand_name"=>"PhilipsHospitality",
            "brand_code"=>"PBS"
            ],
            [
            "brand_name"=>"Philips",
            "brand_code"=>"PCL"
            ],
            [
            "brand_name"=>"PhilipsLighting",
            "brand_code"=>"PHL"
            ],
            [
            "brand_name"=>"PhilipsProduct",
            "brand_code"=>"PAF"
            ],
            [
            "brand_name"=>"PhilipsPurifiers",
            "brand_code"=>"PPH"
            ],
            [
            "brand_name"=>"Philmore",
            "brand_code"=>"PHI"
            ],
            [
            "brand_name"=>"PhoenixLamp",
            "brand_code"=>"PHX"
            ],
            [
            "brand_name"=>"Pioneer",
            "brand_code"=>"PIO"
            ],
            [
            "brand_name"=>"Polaroid",
            "brand_code"=>"PET"
            ],
            [
            "brand_name"=>"Polk",
            "brand_code"=>"PLK"
            ],
            [
            "brand_name"=>"Polti",
            "brand_code"=>"PLT"
            ],
            [
            "brand_name"=>"PowerCords",
            "brand_code"=>"WEB"
            ],
            [
            "brand_name"=>"Powersonic",
            "brand_code"=>"POW"
            ],
            [
            "brand_name"=>"PremiumCompatibles",
            "brand_code"=>"PCI"
            ],
            [
            "brand_name"=>"PrimaWorldwide",
            "brand_code"=>"PWW"
            ],
            [
            "brand_name"=>"Proscan",
            "brand_code"=>"PCN"
            ],
            [
            "brand_name"=>"Proview",
            "brand_code"=>"PRV"
            ],
            [
            "brand_name"=>"PtsModules",
            "brand_code"=>"PTS"
            ],
            [
            "brand_name"=>"QST",
            "brand_code"=>"QST"
            ],
            [
            "brand_name"=>"Quam",
            "brand_code"=>"QUA"
            ],
            [
            "brand_name"=>"QuantumView",
            "brand_code"=>"QTM"
            ],
            [
            "brand_name"=>"RCA",
            "brand_code"=>"RCA"
            ],
            [
            "brand_name"=>"RechargeableBattery",
            "brand_code"=>"USP"
            ],
            [
            "brand_name"=>"CrcAmerica",
            "brand_code"=>"FBT"
            ],
            [
            "brand_name"=>"ReplacementTransist",
            "brand_code"=>"REP"
            ],
            [
            "brand_name"=>"Rheem",
            "brand_code"=>"RHE"
            ],
            [
            "brand_name"=>"Ricoh",
            "brand_code"=>"RIC"
            ],
            [
            "brand_name"=>"RiteOff",
            "brand_code"=>"RIT"
            ],
            [
            "brand_name"=>"Royal",
            "brand_code"=>"ROY"
            ],
            [
            "brand_name"=>"Runco",
            "brand_code"=>"RUN"
            ],
            [
            "brand_name"=>"Saeco",
            "brand_code"=>"SAE"
            ],
            [
            "brand_name"=>"Sanyo",
            "brand_code"=>"SFS"
            ],
            [
            "brand_name"=>"Scopes&PowerSupp.",
            "brand_code"=>"KWD"
            ],
            [
            "brand_name"=>"Senseo",
            "brand_code"=>"SEN"
            ],
            [
            "brand_name"=>"Sharp",
            "brand_code"=>"SHA"
            ],
            [
            "brand_name"=>"SigmacUsa",
            "brand_code"=>"SGM"
            ],
            [
            "brand_name"=>"Silhouette",
            "brand_code"=>"SIL"
            ],
            [
            "brand_name"=>"SMARTTECHNOLOGIES",
            "brand_code"=>"SMT"
            ],
            [
            "brand_name"=>"Sonicare",
            "brand_code"=>"SCR"
            ],
            [
            "brand_name"=>"Soundlier",
            "brand_code"=>"SOU"
            ],
            [
            "brand_name"=>"SoyoRefurbished",
            "brand_code"=>"SYR"
            ],
            [
            "brand_name"=>"Spectrum",
            "brand_code"=>"SPE"
            ],
            [
            "brand_name"=>"SpeedQueen",
            "brand_code"=>"SPQ"
            ],
            [
            "brand_name"=>"StarlogicRefurbish",
            "brand_code"=>"STL"
            ],
            [
            "brand_name"=>"Sub-Zero",
            "brand_code"=>"SUB"
            ],
            [
            "brand_name"=>"Supplies",
            "brand_code"=>"SUP"
            ],
            [
            "brand_name"=>"SVA",
            "brand_code"=>"SVA"
            ],
            [
            "brand_name"=>"Switchcraft",
            "brand_code"=>"SWI"
            ],
            [
            "brand_name"=>"Sylvania",
            "brand_code"=>"SYL"
            ],
            [
            "brand_name"=>"Systemax",
            "brand_code"=>"STM"
            ],
            [
            "brand_name"=>"TceTechnical",
            "brand_code"=>"RCL"
            ],
            [
            "brand_name"=>"TCL",
            "brand_code"=>"TCL"
            ],
            [
            "brand_name"=>"TechSpray",
            "brand_code"=>"TEC"
            ],
            [
            "brand_name"=>"Tektronix",
            "brand_code"=>"TKX"
            ],
            [
            "brand_name"=>"Tempur-pedic",
            "brand_code"=>"TMP"
            ],
            [
            "brand_name"=>"TestEquipment",
            "brand_code"=>"VEL"
            ],
            [
            "brand_name"=>"TestParts",
            "brand_code"=>"TST"
            ],
            [
            "brand_name"=>"Tile",
            "brand_code"=>"ESI"
            ],
            [
            "brand_name"=>"TitanTanklessWater",
            "brand_code"=>"TTW"
            ],
            [
            "brand_name"=>"TNT",
            "brand_code"=>"TNT"
            ],
            [
            "brand_name"=>"TomTom",
            "brand_code"=>"TMT"
            ],
            [
            "brand_name"=>"Tools",
            "brand_code"=>"PAC"
            ],
            [
            "brand_name"=>"Toshiba",
            "brand_code"=>"TBA"
            ],
            [
            "brand_name"=>"ToshibaComputer",
            "brand_code"=>"TOS"
            ],
            [
            "brand_name"=>"VIZIO-TPV",
            "brand_code"=>"TPV"
            ],
            [
            "brand_name"=>"Tradepro",
            "brand_code"=>"TRA"
            ],
            [
            "brand_name"=>"Tri-stateModule",
            "brand_code"=>"TSM"
            ],
            [
            "brand_name"=>"Tripp-lite",
            "brand_code"=>"TRI"
            ],
            [
            "brand_name"=>"TroyBiltYardman",
            "brand_code"=>"MTD"
            ],
            [
            "brand_name"=>"V7eElect.Refurbish",
            "brand_code"=>"VSR"
            ],
            [
            "brand_name"=>"VainSthlm",
            "brand_code"=>"VAI"
            ],
            [
            "brand_name"=>"Vanco",
            "brand_code"=>"VAN"
            ],
            [
            "brand_name"=>"Verge",
            "brand_code"=>"VRG"
            ],
            [
            "brand_name"=>"VIDEOGAMING",
            "brand_code"=>"VGA"
            ],
            [
            "brand_name"=>"VideoMountProducts",
            "brand_code"=>"VMP"
            ],
            [
            "brand_name"=>"ViewsonicRefurb",
            "brand_code"=>"VWS"
            ],
            [
            "brand_name"=>"VikingAppliances",
            "brand_code"=>"VIK"
            ],
            [
            "brand_name"=>"Viore",
            "brand_code"=>"VIO"
            ],
            [
            "brand_name"=>"Vivitek",
            "brand_code"=>"VIV"
            ],
            [
            "brand_name"=>"VIZIO",
            "brand_code"=>"VIZ"
            ],
            [
            "brand_name"=>"Vizparts",
            "brand_code"=>"VZP"
            ],
            [
            "brand_name"=>"Welton",
            "brand_code"=>"WLT"
            ],
            [
            "brand_name"=>"WestPenn",
            "brand_code"=>"WES"
            ],
            [
            "brand_name"=>"WesternDig.Refurb",
            "brand_code"=>"WDR"
            ],
            [
            "brand_name"=>"Westinghouse",
            "brand_code"=>"WDE"
            ],
            [
            "brand_name"=>"Acros",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Affresh",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Amana",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Bauknecht",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Brastemp",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Caloric",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Consul",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Gladiator",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Indesit",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Jennair",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"KitchenAid",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Maytag",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"Yummly",
            "brand_code"=>"WHI"
            ],
            [
            "brand_name"=>"WiiNintendo",
            "brand_code"=>"WII"
            ],
            [
            "brand_name"=>"WikoLamps",
            "brand_code"=>"WIK"
            ],
            [
            "brand_name"=>"Winegard",
            "brand_code"=>"WIN"
            ],
            [
            "brand_name"=>"VIZIO-WISTRON",
            "brand_code"=>"WIS"
            ],
            [
            "brand_name"=>"WoodFinishing",
            "brand_code"=>"WFS"
            ],
            [
            "brand_name"=>"X2genRefurbished",
            "brand_code"=>"XTG"
            ],
            [
            "brand_name"=>"Xerox",
            "brand_code"=>"XER"
            ],
            [
            "brand_name"=>"Xfmrs&Solenoids",
            "brand_code"=>"STN"
            ],
            [
            "brand_name"=>"Yamaha",
            "brand_code"=>"YAM"
            ],
            [
            "brand_name"=>"Yc",
            "brand_code"=>"YCX"
            ],
            [
            "brand_name"=>"Zebra",
            "brand_code"=>"ZEB"
            ],
            [
            "brand_name"=>"Hydro-Gear",
            "brand_code"=>"HYG"
            ],
            [
            "brand_name"=>"InfotainmentReplacementPartsandAccessories",
            "brand_code"=>"IFT"
            ],
            [
            "brand_name"=>"JBRReplacementParts",
            "brand_code"=>"JBR"
            ],
            [
            "brand_name"=>"Jenn-air",
            "brand_code"=>"JEN"
            ],
            [
            "brand_name"=>"RepairJig",
            "brand_code"=>"JIG"
            ],
            [
            "brand_name"=>"KitchenAidAppliance",
            "brand_code"=>"KAD"
            ],
            [
            "brand_name"=>"Kohler",
            "brand_code"=>"KOH"
            ],
            [
            "brand_name"=>"LGCellPhone",
            "brand_code"=>"LGE"
            ],
            [
            "brand_name"=>"LgLiterature",
            "brand_code"=>"LGL"
            ],
            [
            "brand_name"=>"Lennox",
            "brand_code"=>"LNX"
            ],
            [
            "brand_name"=>"LXA",
            "brand_code"=>"LXA"
            ],
            [
            "brand_name"=>"Mcculloch",
            "brand_code"=>"MCC"
            ],
            [
            "brand_name"=>"Motorola",
            "brand_code"=>"MOT"
            ],
            [
            "brand_name"=>"MRCOOL",
            "brand_code"=>"MRC"
            ],
            [
            "brand_name"=>"Sonos",
            "brand_code"=>"SNS"
            ],
            [
            "brand_name"=>"SonyAccessories",
            "brand_code"=>"SNY"
            ],
            [
            "brand_name"=>"LgElectronicsRefurImaging",
            "brand_code"=>"ZER"
            ],
            [
            "brand_name"=>"ZTS",
            "brand_code"=>"ZTS"
            ],
            [
            "brand_name"=>"SonyServiceLit.",
            "brand_code"=>"SSL"
            ],
            [
            "brand_name"=>"SubaruIndustrial",
            "brand_code"=>"SUI"
            ],
            [
            "brand_name"=>"TecumsehPeerless",
            "brand_code"=>"TEP"
            ],
            [
            "brand_name"=>"RyobiElect.ToolsPowerTools",
            "brand_code"=>"TTI"
            ],
            [
            "brand_name"=>"Walbro",
            "brand_code"=>"WAL"
            ],
            [
            "brand_name"=>"WYS",
            "brand_code"=>"WYS"
            ],
            [
            "brand_name"=>"MotorolaAfterMkt",
            "brand_code"=>"MTA"
            ],
            [
            "brand_name"=>"PhilipsManuals",
            "brand_code"=>"NAL"
            ],
            [
            "brand_name"=>"Appl.InstallKits",
            "brand_code"=>"NDA"
            ],
            [
            "brand_name"=>"SparkPlugs",
            "brand_code"=>"NGK"
            ],
            [
            "brand_name"=>"NsaRecovery",
            "brand_code"=>"NSA"
            ],
            [
            "brand_name"=>"GreenChoice",
            "brand_code"=>"NWD"
            ],
            [
            "brand_name"=>"OdysseyToys",
            "brand_code"=>"ODY"
            ],
            [
            "brand_name"=>"PhilipsDirectLife",
            "brand_code"=>"PDL"
            ],
            [
            "brand_name"=>"PhilipsHosp.Canada",
            "brand_code"=>"PFH"
            ],
            [
            "brand_name"=>"GeneracDelco",
            "brand_code"=>"GEN"
            ],
            [
            "brand_name"=>"Guardsman",
            "brand_code"=>"GUA"
            ],
            [
            "brand_name"=>"AypCraftsman",
            "brand_code"=>"HOP"
            ],
            [
            "brand_name"=>"PUMP",
            "brand_code"=>"PUMP"
            ],
            [
            "brand_name"=>"CellPhone",
            "brand_code"=>"QJO"
            ],
            [
            "brand_name"=>"Rotary(Aftermarket)",
            "brand_code"=>"ROT"
            ],
            [
            "brand_name"=>"OpenBoxProduct",
            "brand_code"=>"RTV"
            ],
            [
            "brand_name"=>"Sceptre",
            "brand_code"=>"SCE"
            ],
            [
            "brand_name"=>"Seiki",
            "brand_code"=>"SEI"
            ],
            [
            "brand_name"=>"SanyoManufacturing",
            "brand_code"=>"SMC"
            ],
            [
            "brand_name"=>"AllFilters",
            "brand_code"=>"ACF"
            ],
            [
            "brand_name"=>"Bose",
            "brand_code"=>"BOS"
            ],
            [
            "brand_name"=>"Casio",
            "brand_code"=>"CAS"
            ],
            [
            "brand_name"=>"Delonghi",
            "brand_code"=>"DEG"
            ],
            [
            "brand_name"=>"GreenChoice",
            "brand_code"=>"DTP"
            ],
            [
            "brand_name"=>"CompatibleAppliance",
            "brand_code"=>"ERP"
            ],
            [
            "brand_name"=>"GreenChoice",
            "brand_code"=>"EGR"
            ],
            [
            "brand_name"=>"BEL",
            "brand_code"=>"BEL"
            ],
            [
            "brand_name"=>"Carrier",
            "brand_code"=>"CAR"
            ],
            [
            "brand_name"=>"Carrier",
            "brand_code"=>"CAR"
            ],
            [
            "brand_name"=>"Coby",
            "brand_code"=>"CBY"
            ],
            [
            "brand_name"=>"AmericanLawn",
            "brand_code"=>"AME"
            ],
            [
            "brand_name"=>"DamagedPanels",
            "brand_code"=>"DAM"
            ],
            [
            "brand_name"=>"SimpsonDelco",
            "brand_code"=>"FNA"
            ],
            [
            "brand_name"=>"Ardisam",
            "brand_code"=>"ARD"
            ],
            [
            "brand_name"=>"BriggsPower",
            "brand_code"=>"BSG"
            ],
            [
            "brand_name"=>"HairRemoval/Skincare",
            "brand_code"=>"HRS"
            ],
            [
            "brand_name"=>"Airfryer",
            "brand_code"=>"Afyr"
            ],
            [
            "brand_name"=>"AirPurifiersandHumidifiers",
            "brand_code"=>"APHU"
            ],
            [
            "brand_name"=>"KitchenAppliances",
            "brand_code"=>"PKAP"
            ]
        ];
        $jsonData = [
                'status' => 'success',
                'code' => 200,
                'data' => $allBrands,
            ];                            

        return response()->json($jsonData);

    }
    
}
