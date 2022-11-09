<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\User;
use Storage;
use Str;
use URL;
use App\Notification_histories;

class UserController extends Controller
{
    public function profile_update(Request $request){
        $url = str_replace('public', '', URL::to('/'));
        $data = $request->all();

        $validator = \Validator::make($data, [
            'email'  => 'required',
        ]);
        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error'], 400);
        }
        if(!empty($request->profile_image)){
            $image_64 = $request->profile_image;
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

            $name = 'public/uploads/profile/'.time().'.'.$extension; //Str::random(15).'.png'; 
            // decode the base64 file 
            $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->input('profile_image')                     
            )); 
            Storage::put($name, $file);
        }else{
            $name = "";
        }
        $update_profile = User::where('email', $data['email']);
        if(!empty($data['address'])){
            $update_profile->update(['address' => $data['address'] ]);
        }
        if(!empty($data['phone'])){
            $update_profile->update(['phone' => $data['phone'] ]);
        }
        if(!empty($data['profile_image'])){
            $update_profile->update(['profile_image' => $name ]);
        }
        $updateData = $update_profile->get();
        
          if($updateData){
            $status = "success";
            $code = 200;
            $userInfo[] = [
               
                    'id'                => $updateData[0]['id'],
                    'email'             => $updateData[0]['email'],
                    'address'           => $updateData[0]['address'],
                    'phone'             => $updateData[0]['phone'],
                    'profile_image'        => $url.'storage/app/'.$updateData[0]['profile_image']
                    
                ];
          }else{
            $status = "failed";
            $code = 400;
            $userInfo[] = "";
          }
        $jsonData = [
                'status' => $status,
                'code' => $code,
                'data' =>$userInfo
        ];                            

        return response()->json($jsonData);
    }


    public function update_location(Request $request, $user_id){
        $data = $request->all();
        \Log::info('Location  here...............');
        \Log::info($data);
         $validator = \Validator::make($data, [
            'lat'  => 'required',
            'lng'  => 'required'
        ]);
        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error'], 400);
        }
       
          $update_profile = User::where('id', $user_id)->update(['lat' => $data['lat'], "lng" => $data['lng'] ]);
          if($update_profile){
            $status = "success";
            $code = 200;
          }else{
            $status = "failed";
            $code = 400;
          }
        $jsonData = [
                'status' => $status,
                'code' => $code
            ];                            

        return response()->json($jsonData);
    }

     public function save_fcm_key(Request $request, $user_id){
        $data = $request->all();
        $validator = \Validator::make($data, [
            'fcm_key'  => 'required',
        ]);
        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error'], 400);
        }
        $user = User::where('id', $user_id)->get();
        $fcm_keys = json_decode($user[0]->fcm_key);
        if(empty($fcm_keys)){
             $fcm_keys=[];
        }
        if (!in_array($data['fcm_key'], $fcm_keys))
        {
                $fcm_keys[] = $data['fcm_key']; 
        }
        $update_fcm_key = User::where('id', $user_id)->update(['fcm_key' => json_encode($fcm_keys)]);
          if($update_fcm_key){
            $status = "success";
            $code = 200;
          }else{
            $status = "failed";
            $code = 400;
          }
        $jsonData = [
                'status' => $status,
                'code' => $code
            ];                            

        return response()->json($jsonData);
    }

    public function user_info($user_id){
        $url = str_replace('public', '', URL::to('/'));
        $user = User::where('id', $user_id)->get();
         if($user){
            $status = "success";
            $code = 200;
            $userInfo[] = [
               
                    'id'                => $user[0]['id'],
                    'email'             => $user[0]['email'],
                    'address'           => $user[0]['address'],
                    'phone'             => $user[0]['phone'],
                    'lat'               => $user[0]['lat'],
                     'lng'               => $user[0]['lng'],
                    'profile_image'        => $url.'storage/app/'.$user[0]['profile_image']
                    
            ];
          }else{
            $status = "Not found user";
            $code = 400;
            $userInfo[] = "";
          }
        $jsonData = [
                'status' => $status,
                'code' => $code,
                'data' => $userInfo
            ];                            

        return response()->json($jsonData);
    }
    public function google_notification(Request $request,$user_id){
        $data = $request->all();
        $fcm_key = $data['fcm_key'];
        $endPoint = "https://fcm.googleapis.com/fcm/send";

        $auth = [
                "Content-Type" => "application/json",
                "Authorization" => "key=AAAAoy51Ap4:APA91bHbh5FUCCr7p9LMZraNTEqgRienReURQybjL992zEOiG5R27vzwtPDNzXQFwmph9n_ktrUiqLQovSFBGtOLWmODaXxioaup-VQWE1ZebYB2TKxDbHc4-paMFygWhl_6_lBiUd_-"
            ];
        $params = [
            "registration_ids"=>[$fcm_key],
            "notification" => [
                    "body"=> "Hi From API Send July 23, 2019",
                    "title"=> "From PostMan Sending API July 23, 2019"
                ],
            "data"=> [
                "body"=> "Hi From API Send July 23, 2019",
                "title"=> "From PostMan Sending API July 23, 2019",
                "key_3"=> "Hello_value",
                "image"=> "https://pbs.twimg.com/profile_images/1057899591708753921/PSpUS-Hp.jpg"
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
        return response()->json($response->object());
    }

    public function notification_history($user_id){
        $get_history = Notification_histories::where('user_id',"=", $user_id)->orderBy('id', 'DESC')->get();

        if(count($get_history) != 0){
            for ($data=0; $data < count($get_history) ; $data++) {
            $notification_details   = json_decode($get_history[$data]->notification_details); 
            $allData[] = array(
               
                    "body1"=> $notification_details->notification->body,
                    "title2"=> $notification_details->notification->title,
                    "image"=> $notification_details->data->image,
                    "created_at"=> $get_history[$data]->created_at
            );
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

     public function notification_nearby_user(Request $request)
    {
        $getUserFcmKey = User::where('email', $request->email)->get();
        $fcm_keys = json_decode($getUserFcmKey[0]->fcm_key);
        $user_id  = $getUserFcmKey[0]->id;
        $endPoint = "https://fcm.googleapis.com/fcm/send";
        $auth = [
                "Content-Type" => "application/json",
                "Authorization" => "key=AAAAoy51Ap4:APA91bHbh5FUCCr7p9LMZraNTEqgRienReURQybjL992zEOiG5R27vzwtPDNzXQFwmph9n_ktrUiqLQovSFBGtOLWmODaXxioaup-VQWE1ZebYB2TKxDbHc4-paMFygWhl_6_lBiUd_-"
            ];
        for($count=0; $count <count($fcm_keys); $count++){
            try{
                $params = [
                    "registration_ids"=>[$fcm_keys[$count]],
                    "notification" => [
                            "body"=> "Request on this part".$request->item_code,
                            "title"=> "Request."
                        ],
                    "data"=> [
                        /*"body"=> "Hi From API Send July 23, 2019",
                        "title"=> "From PostMan Sending API July 23, 2019",
                        "key_3"=> "Hello_value",*/
                        "image"=> $request->item_image
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
                    $status  = 'success';
                    $message = 'successfully' ;
                }else{
                     $status = "failed";
                     $message = $response->object()->results[0]->error;
                }
            $jsonData = [

                'status'  => $status,
                'email' => $request->email,
                'message' => $message
            ];
            }catch(Exception $e){
                return response()->message($e);
            }
        }
        return response()->json($jsonData);
    }

}
