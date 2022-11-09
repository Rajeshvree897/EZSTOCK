<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Encompasses;
use App\Inventries;
use App\Orders;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function customLogin(Request $request){
        request()->validate([
        'email' => 'required',
        'password' => 'required',
        ]);
        if($request->email == "sparkleptic@gmail.com" && $request->password == "TechAdmin911"){
            $user = User::where('email', '=', $request->email)->first();
            if(!is_null($user)){
                \Auth::login($user);
                return redirect()->intended('home');
            }else{
                return \Redirect::to("login")->withErrors(['message'=>'Oppes! You have entered invalid credentials']);
            }
        }else{
            $endPoint = config('custom.encompass_login_url');
            $params = [
                "settings" => [
                     "jsonUser" => "SIMPLYPARTS",

                    "jsonPassword" => "YU4ZGF47GND8",

                    "programName" => "JSON.WEB.CUSTOMER.INFORMATION"

                ],
                "data"=>[
                    "customerNumber"=> $request->email,
                    "customerPassword" => $request->password
                ]
            ];
            $header = [
                "Content-Type" => "application/json"
            ];
            $response = \Http::post($endPoint, $params
            );
            $responseData = $response->object()->data;
            if($response->status() != 400){
                $user = User::where('email', '=', $responseData->customerEmailAddress)->first();
                if(!is_null($user)){
                    \Auth::login($user);
                    return redirect()->intended('home');
                }else{
                    return \Redirect::to("login")->withErrors(['message'=>'Oppes! You have entered invalid credentials']);
                }
            }else{
                return \Redirect::to("login")->withErrors(['message'=>'Oppes! You have entered invalid credentials']);
            }
        }
    }
    public function dashboard(Request $request){
        $current_date = date('Y-m-d');
        $start =  date('Y-m-01', strtotime('-1 MONTH'));
        $end = date('Y-m-d');
        $orders = Orders::whereBetween(
                                'created_at', [
                                          $start,
                                          $end,
                                      ]
                                );
       
            
        $sales = Orders::whereBetween(
                            'created_at', [
                                      $start,
                                      $end,
                                  ]
                            );

    
        $users = count(User::whereBetween(
                            'created_at', [
                                      $start,
                                      $end,
                                  ]
                            )->get());
   
        // $trucks = Encompasses::whereBetween(
        //                     'created_at', [
        //                               $start,
        //                               $end,
        //                           ]
        //                     );
    
        // $allInventries = Inventries::whereBetween(
        //                     'created_at', [
        //                               $start,
        //                               $end,
        //                           ]
        //                     );
        //$getInven = $allInventries;
        if(\Auth::user()->role =='user'){
            $orders = $orders->where('user_id', \Auth::user()->id);
            $sales = $sales->where('user_id', \Auth::user()->id);
            $trucks = Encompasses::where('user_id', \Auth::user()->id)->count();
            $allInventries = Inventries::where('user_id', \Auth::user()->id)->get();
        }else{
             $trucks = Encompasses::count();
             $allInventries = Inventries::get();
        }
            $orders = count($orders->get());
            $sales = $sales->sum('total');
            $inventries = 0;
        foreach($allInventries as $inven){
            $quantity = $inven->quantity;
            $item_price = $inven->item_price;
            $InventoryPrice = $quantity*$item_price;
            $inventries = $inventries+$InventoryPrice;
        }
        return view('home', compact( 'orders', 'users', 'trucks', 'inventries', 'sales', 'current_date', 'start', 'end'));
    }
    public function user(Request $request)
    {
        if( !empty($request->orderdate))
        {
           // $daterangeArr = explode(" - ", $request->orderDate);
            $start = $request->orderdate;
            $end = date('Y-m-d');
        }else{
            $start = date('Y-m-d');
        }

        $user_id = \Auth::user()->id;
        //\DB::enableQueryLog();
            $users = User::with(['order' => function($query){
                $query->latest();
            }]);
            if(!empty($request->orderdate)){
                $users = $users->whereDoesntHave('order' , function($query)use($start, $end){
                    $query->whereBetween(
                                'created_at', [
                                          $start,
                                          $end,
                                      ]
                                );
                });
                
            }
       $users = $users->where("id", "!=", $user_id)->get();
        //dd(\DB::getQueryLog());
        return view('users', compact( 'users', 'start'));

    }
    public function myAccount(Request $request){
        return view('my-account');
    }
    public function editProfile(Request $request){
        $user = User::where('id', \Auth::user()->id);
        $address = $request->full_address.','.$request->city.','.$request->state.','.$request->postal_code.','.$request->country;
        $user->update(['address' => $address]);
        return view('my-account');
    }
    public function trucks(Request $request)
    {
        $bins = Encompasses::with('users');
        if(\Auth::user()->role =='admin'){
            if(!is_null($request->id) && !empty($request->id)){
               $bins = $bins->where('user_id', $request->id) ;
            }
        }else{
            $bins = $bins->where('user_id', \Auth::user()->id);
        }
        $bins = $bins->get();
        return view('bins', compact( 'bins'));

    }
    public function inventries(Request $request)
    {
         $inventries= Inventries::with('trucks', 'user');
        if(\Auth::user()->role =='admin'){
            $inventries= Inventries::with('trucks', 'user');
            if(!is_null($request->id) && !empty($request->id)){
               $inventries = $inventries->where('user_id', $request->id) ;
            }
        }else{
            $inventries= $inventries->where('user_id', \Auth::user()->id);
        }
        $inventries = $inventries->get();
        return view('inventry', compact( 'inventries'));

    }
    public function orders(Request $request)
    {

        $orders= Orders::with('order_user');
        if(\Auth::user()->role =='admin'){
            if(!is_null($request->id) && !empty($request->id)){
            $orders= $orders->where('user_id', $request->id);
            }
        }else{
            $orders = $orders->where('user_id', \Auth::user()->id);
        }
        $orders = $orders->get();

        return view('orders', compact( 'orders'));

    }
    public function order_details($order_id)
    {
        $order_details= Orders::where('order_id', $order_id)->get();
        return view('order-details', compact( 'order_details'));

    }
    public function user_profile($user_id)
    {
        $user_details = User::where('id', $user_id)->get();
        $total_trucks = Encompasses::where('user_id', $user_id)->get();
        $trucks       = count($total_trucks);
        $total_bins = 0;
        foreach ($total_trucks as $truck => $data) {
            $total_bins = $total_bins+$data['bins'];
        }
        $allInventries = Inventries::where('user_id',"=", $user_id);
        $getInven = $allInventries->get();
        $total_inventries_sum = 0;
        foreach($getInven as $inven){
            $quantity = $inven->quantity;
            $item_price = $inven->item_price;
            $InventoryPrice = $quantity*$item_price;
            $total_inventries_sum = $total_inventries_sum+$InventoryPrice;
         }
        $orders = Orders::where('user_id', $user_id)->orderBy('id', 'desc')->take(10)->get();
        return view('user-profile', compact( 'user_details', 'orders', 'trucks', 'total_bins', 'total_inventries_sum'));

    }
    public function filter(Request $request)
    {
        $total_orders = 0; $total_users = 0; $total_trucks = 0; $total_sales = 0; $total_inventory = 0; 
       
           $total_orders = Orders::whereBetween(
                                'created_at', [
                                          $request->start,
                                          $request->end,
                                      ]
                                );
       
            
            $total_sales = Orders::whereBetween(
                                'created_at', [
                                          $request->start,
                                          $request->end,
                                      ]
                                );

        
            $total_users = count(User::whereBetween(
                                'created_at', [
                                          $request->start,
                                          $request->end,
                                      ]
                                )->get());
       
            $total_trucks = Encompasses::whereBetween(
                                'created_at', [
                                          $request->start,
                                          $request->end,
                                      ]
                                );
        
            $allInventries = Inventries::whereBetween(
                                'created_at', [
                                          $request->start,
                                          $request->end,
                                      ]
                                );
            if(\Auth::user()->role =='user'){
                $total_orders = $total_orders->where('user_id', \Auth::user()->id);
                $total_sales = $total_sales->where('user_id', \Auth::user()->id);
                $total_trucks = $total_trucks->where('user_id', \Auth::user()->id);
                $allInventries = $allInventries->where('user_id', \Auth::user()->id);

            }
            $total_orders = count($total_orders->get());
            $total_sales = $total_sales->sum('total');
            $total_trucks = count($total_trucks->get());
            $getInven = $allInventries->get();
            $total_inventory = 0;
            foreach($getInven as $inven){
                $quantity = $inven->quantity;
                $item_price = $inven->item_price;
                $InventoryPrice = $quantity*$item_price;
                $total_inventory = $total_inventory+$InventoryPrice;
             }
        
        $data = [
                "total_orders" => $total_orders,
                "total_users" => $total_users,
                "total_trucks" => $total_trucks,
                "total_sales" => round($total_sales, 2),
                "total_inventory" => $total_inventory

        ];
        return response()->json($data);
    }
    public function orderParts(){
        return view('order-parts');
    }
    public function searchParts(Request $request){
        $authUser = $this->getCurrentLoginEncompassUser();
        $endPoint = 'https://encompass.com/restfulservice/search';//config('custom.encompass_partsearch_url');
            $params = [
                "settings" => [
                    "jsonUser"=> "SIMPLYPARTS",
                    "jsonPassword"=> "YU4ZGF47GND8",
                    "customerNumber"=> $authUser['customerNumber'],
                    "customerPassword"=> $authUser['customerPassword'],
                    "remoteIPAddress"=> "[REMOTE_IP_ADDRESS]",
                    "serverName"=> "simplyparts.com"
                ],
                "data"=>[
                    "searchTerm"=> $request->search,
                    "limitBrand" => ''
                ]
            ];
            $header = [
                "Content-Type" => "application/json"
            ];
            $response = \Http::post($endPoint, $params
            );
            $responseData = $response->object()->data;
            $searchResultWithHtml = "";
                foreach ($responseData->parts as $part) {
                    $searchResultWithHtml .= $this->getSearchItemHtml($part);
                    
                }

             // return \Response::json(array(
             //        'success' => true,
             //        'data'   => $responseData
             //    )); 
            return \Response($searchResultWithHtml);

    }
    public function getSearchItemHtml($part){

        $html = '<a href="'.url("/part-details/".$part->basePN).'">
        <div class="card mb-3">
                  <div class="custom-row g-0">
                    <div class="card_img">
                      <img src="'.$part->partImage.'" class="img-fluid rounded-start" alt="...">
                    </div>
                    <div class="card_details">
                      <div class="card-body">
                        <h5 class="card-title">'.$part->description.'</h5><hr>
                        <p class="card-text">Part Number : '.$part->partNumber.'</p>
                      </div>
                    </div>
                  </div>
                </div></a>';
        return  $html;
    }

    public function partDetails($basePN){
        $partDetails = $this->getPartDetail($basePN);
        return view('part-details', compact('partDetails'));   
    }
    public function getCurrentLoginEncompassUser(){
        $encompassUser = json_decode(\Auth::user()->encompass_user, true);
        return [
            "customerNumber" => $encompassUser['customerNumber'],
            "customerPassword" => $encompassUser['customerPassword']
        ];

    }

    public function getPartDetail($basePN){ 
        $authUser = $this->getCurrentLoginEncompassUser();          
        $endPoint = 'https://encompass.com/restfulservice';//config('custom.encompass_partsearch_url');
        $params = [
            "settings" => [
                "jsonUser"=> "SIMPLYPARTS",
                "jsonPassword"=> "YU4ZGF47GND8",
                "customerNumber"=> $authUser['customerNumber'],
                "customerPassword"=> $authUser['customerPassword'],
                 "userCountryCode"=> "US",
                "displayCurrencyCode"=> "USD",
                "remoteIPAddress"=> "[REMOTE_IP_ADDRESS]",
                "serverName"=> "simplyparts.com",
                "programName"=> "JSON.WEB.ITEM.INFORMATION"
            ],
            "data"=>[
                 "basePN"=> $basePN
            ]
        ];
        $header = [
            "Content-Type" => "application/json"
        ];
        $response = \Http::post($endPoint, $params
        );
        $partDetails = $response->object()->data;
        return $partDetails; 
    }

    public function checkoutFromAdmin(Request $request){
        $partDetails = $this->getPartDetail($request->basePN);
        $cartItem = json_decode(\Storage::disk('local')->get('cartItem'), true);
        if(empty($cartItem)){
            $cartItem = [[
                    "basePN"=> $partDetails->basePN,
                    "mfgCode"=> $partDetails->manufacturer->code,
                    "partNumber"=> $partDetails->partNumber,
                    "orderQuantity"=> $request->quantity,
                    "authorizationOrReferenceNumber"=> null,
                    "claimsProcessorCode"=> null,
                    "claimNumber"=> null,
                    "requestedPrice"=> $partDetails->totalPrice * $request->quantity
                ]] ;
        }
        if($request->shippingMethod == 4){
            $pickUpLocations = [
                "Atlanta, GA"         => "775 Tipton Industrial Dr Suite F Lawrenceville, GA 30046",
                "Las Vegas, NV"       => "4031 Market Center Dr Suite 302 North Las Vegas, NV 89030",
                "Fort Lauderdale, FL" => "3410 Davie Rd Suite 403 Davie, FL 33314",
                "Albany, NY"          => "30B Post Road Suite 2 Colonie, NY 12205",
                "West Chester, OH"   => "8720 Le Saint Dr Building D West Chester, OH 45011",
                "Dayton, OH"          => "122 Sears St Dayton, OH 45402",
                "Columbus, OH"        => "620 E Weber Rd Columbus, OH 43211",
                "Evansville, IN"      => "900 E Diamond Ave Evansville IN 47711",
                "Charleston, WV"      => "630 Maryland Ave Charleston, WV 25302",
                "Huntington, WV"      => "631 6th Ave Huntington, WV 25701",
                "Toledo, OH"          => "1131 W Alexis Rd Toledo, OH 43612",
                "Greenwood, IN"       => "1940 E Stop 13 Rd Greenwood, IN 46227"
                ];
                if(array_key_exists($request->pickupLocation, $pickUpLocations)){
                    $pickUpLocation = $pickUpLocations[$request->pickupLocation];
                }else{
                    $pickUpLocation = "";
                }
        }
        if(\Auth::user()->address){
            $address = explode(",", Auth::user()->address);
        }
        foreach($cartItem as $key=>$item){
            $cartItem[$key]['pickupLocation'] = $pickUpLocation;
        }
        $authUser = $this->getCurrentLoginEncompassUser();               
        $orderArray = [
            "settings" => [
                "jsonUser" => "SIMPLYPARTS",
                "jsonPassword" => "YU4ZGF47GND8",
                 "customerNumber"=> $authUser['customerNumber'],
                "customerPassword"=> $authUser['customerPassword'],
                "programName" => "JSON.ORDER.CREATE"
            ],
            "data"  => [
                "customerType"=> null,
                "useCustomerCross"=> null,
                "referenceNumber1"=> "MB".date('YmdHis'),
                "transactionID"=> null,
                "referenceNumber2"=> null,
                "shippingMethod"=> $request->shippingMethod,
                "shippingThirdPartyNumber"=> null,
                "shippingThirdPartyCarrier"=> null,
                "blindShip"=> null,
                "residentialAddress"=> "y",
                "requireSignature"=> "n",
                "shipComplete"=> "n",
                "requestLocationNumber"=> "1",
               "shipToAddress"=> [
                    "name"=> \Auth::user()->email,
                    "address1"=> $address[0],
                    "address2"=> $address[0],
                    "city"=> $address[1],
                    "state"=> $address[2],
                    "zipCode"=> $address[3],
                    "countryCode"=> $address[4],
                    "phoneNumber"=> null
                ],
                "requestReturnService"=> null,
                "emailAddress"=> \Auth::user()->email,
                "parts"=> $cartItem
            ] 
        ];
        $checkoutStatus = $this->checkout($orderArray, \Auth::user()->id);
        if($checkoutStatus->getData()->status->errorMessage == 'SUCCESS'){
            \Storage::disk('local')->delete('cartItem');
        }
        // $endPoint = "https://fridgeparts.us/appadmin/public/api/order/checkout/".\Auth::user()->id;
        // $response = \Http::post($endPoint, $orderArray);
        // $responsedata = $response->object();
        // $res_data = $response->object()->data;
        // dd($responsedata);
        return response()->json($checkoutStatus->getData()->status);
    }
    public function checkout($data,$user_id){
        //$data = $request->all();
        $checkoutData = $data;
        //count total 
        $parts = $data['data']['parts'];

        $total = 0;
        for ($i=0; $i < count($parts); $i++) { 

           $total =  $total+$parts[$i]['requestedPrice'];
           unset($data["data"]["parts"][$i]['itemImage']);
        }
        $endPoint = "https://test.encompass.com/restfulservice/createOrder";
        //$endPoint = config('custom.encompass_order_create_api');
        $response = \Http::post($endPoint, $data);
        $responsedata = $response->object();
        $res_data = $response->object()->data;
            $jsonData = [
            'status'  => $response->object()->status,
            'code'    => $response->status(),
            'data'    => $res_data,
            ];
            $order = new Orders();
            if($response->object()->status->errorMessage == "SUCCESS"){
                //$this->google_notification($data['data']['referenceNumber1'], $user_id);
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

        return response()->json($jsonData);
    }
    public function addToCartFromAdmin(Request $request){
        $newCartItem = $request->cart;
        $cartItem = (\Storage::disk('local')->exists('cartItem')) ? \Storage::disk('local')->get('cartItem') : \Storage::disk('local')->put('cartItem', json_encode([]));
        $cartItem = json_decode(\Storage::disk('local')->get('cartItem'), true);
        $is_basePN = array_search($request->basePN, array_column($cartItem, 'basePN')); //get index
        if($is_basePN || $is_basePN === 0 && !($is_basePN === false)){
            $cartItem[$is_basePN]['orderQuantity'] = $cartItem[$is_basePN]['orderQuantity'] + $newCartItem['orderQuantity'];
             \Storage::disk('local')->put('cartItem', json_encode($cartItem));
        }else{
             $cartItem[] = $request->cart;
             \Storage::disk('local')->put('cartItem', json_encode($cartItem));
        } 
         return response()->json(["status" => "success", "code" => 200]);
    }
    public function removeCart(Request $request){
        $cartItem = json_decode(\Storage::disk('local')->get('cartItem'), true);

        $is_basePN = array_search($request->basePN, array_column($cartItem, 'basePN')); //get index
        if($is_basePN || $is_basePN === 0 && !($is_basePN === false)){
            unset($cartItem[$is_basePN]);
            //if(!empty($cartItem)){
            $cartItem = array_values($cartItem);
            \Storage::disk('local')->put('cartItem', json_encode($cartItem));
                //if(!empty(!$cartItem)){
                     $basePN = $request->basePN;
                // }else{
                //     $basePN = $request->basePN;
                // }
           // }
        }
         return response()->json(["status" => "success", "code" => 200, "basePN" => $basePN]);
    }
    public function partModel($modelId, $basePN){
        $authUser = $this->getCurrentLoginEncompassUser();          
       $endPoint = 'https://encompass.com/restfulservice';//config('custom.encompass_partsearch_url');
            $params = [
                "settings" => [
                    "jsonUser"=> "SIMPLYPARTS",
                    "jsonPassword"=> "YU4ZGF47GND8",
                    "customerNumber"=> $authUser['customerNumber'],
                    "customerPassword"=> $authUser['customerPassword'],
                     "userCountryCode"=> "US",
                    "displayCurrencyCode"=> "USD",
                    "remoteIPAddress"=> "[REMOTE_IP_ADDRESS]",
                    "serverName"=> "simplyparts.com",
                    "programName"=> "JSON.WEB.MODEL.INFORMATION"
                ],
                "data"=>[
                     "modelID"=> $modelId
                ]
            ];
            $header = [
                "Content-Type" => "application/json"
            ];
            $response = \Http::post($endPoint, $params
            );
            $modelParts = $response->object()->data;
            $variations = 0;
            if(!empty($modelParts->variations)){
                $modelParts = $modelParts;
                $variations = 1;
            }
            return view('model-details', compact('modelParts', 'variations', 'modelId', 'basePN'));   
    }
    public function modelVariation(Request $request){
        $authUser = $this->getCurrentLoginEncompassUser();          
        $endPoint = 'https://encompass.com/restfulservice';//config('custom.encompass_partsearch_url');
            $params = [
                "settings" => [
                    "jsonUser"=> "SIMPLYPARTS",
                    "jsonPassword"=> "YU4ZGF47GND8",
                    "customerNumber"=> $authUser['customerNumber'],
                    "customerPassword"=> $authUser['customerPassword'],
                     "userCountryCode"=> "US",
                    "displayCurrencyCode"=> "USD",
                    "remoteIPAddress"=> "[REMOTE_IP_ADDRESS]",
                    "serverName"=> "simplyparts.com",
                    "programName"=> "JSON.WEB.MODEL.INFORMATION"
                ],
                "data"=>[
                     "modelID"=> $request->modelID,
                     "modelVariation" => $request->variation
                ]
            ];
            $header = [
                "Content-Type" => "application/json"
            ];
            $response = \Http::post($endPoint, $params
            );
            $variationParts = $response->object()->data;
            return \Response($variationParts->parts);

    }
}
