<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

use App\User;
use GuzzleHttp\Client;

class LoginController extends Controller
{
    public function login(Request $request)
    {
      $data = $request->all();
        $validator = \Validator::make($data, [
            'customerNumber' => 'required|max:255',
            'customerPassword' => 'required'
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error'], 400);
        }
       // if($request->customerNumber == 7461 && $request->customerPassword == "ROBERTC"){
            $endPoint = config('custom.encompass_login_url');
            $params = [
                "settings" => [
                     "jsonUser" => $request->jsonUser,//"SIMPLYPARTS",

                    "jsonPassword" => $request->jsonPassword,//"YU4ZGF47GND8",

                    "programName" => $request->programName//"JSON.WEB.CUSTOMER.INFORMATION"

                ],
                "data"=>[
                    "customerNumber"=> $request->customerNumber,
                    "customerPassword" => $request->customerPassword
                ]
            ];
            $header = [
                "Content-Type" => "application/json"
            ];
            $response = Http::post($endPoint, $params
            );
            $responseData = $response->object()->data;
            if($response->status() != 400){
              
                //$user = User::where('email', '=', responseData->customerEmailAddress)->whereJsonContains('encompass_user->customerNumber', $request->customerNumber)->first();
                $user = User::whereJsonContains('encompass_user->customerNumber', $request->customerNumber)->first();
                if (is_null($user)) {
                    $user = User::create(
                        [
                            'name' => $responseData->customerName,
                            'email' => $responseData->customerEmailAddress,
                            'address' => "",
                            'phone'   => "",
                            'password' => Hash::make($responseData->customerPassword),
                            'customer_number' => $responseData->customerNumber
                        ]
                    );
                }elseif($responseData->customerEmailAddress != $user->email){
                    $user->update(['email' => $responseData->customerEmailAddress]);
                }
                    $responseArry = json_decode(json_encode($responseData), true);
                    if(array_key_exists('customerPassword', $responseArry)) {
                        if (empty($responseArry['customerPassword'])) {
                            $responseArry['customerPassword'] = $request->customerPassword;
                            $user->encompass_user = json_encode($responseArry, true);
                            $responseData = $responseArry;
                        }
                    }else{
                        $user->encompass_user = json_encode($responseData);
                    }
                    $user->save();
                    $status = $response->object()->status;
                    $code = 200;//$response->status();
                    $user_id = $user->id;
                    $address = $user->address;
                    $phone   = $user->phone;
            }else{
                    $status = "Failed";
                    $code = 400;
                    $responseData = "";
                    $user_id = "";
                    $address = "";
                    $phone = "";
            }

        
            $jsonData = [
            'status'  => $status,
            'code'    => $code,
            'data'    => $responseData,
            'user_id' => $user_id,
            "address" => $address,
            "phone"  => $phone
            ];
      /*$credentials = $request->only(['email', 'password']);

      if (!$token = auth()->attempt($credentials)) {
        return response()->json(['error' => 'Unauthorized'], 401);
      }
      */
      //return $this->respondWithToken($token);
      return response()->json($jsonData);
    }
}
