<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\GalleryController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    function addUser(Request $req){
        $user_det = new User;
        $user_det->fname    = $req->input('fname');
        $user_det->lname    = $req->input('lname');
        $user_det->username = $req->input('username');
        $user_det->email    = $req->input('email');
        $user_det->phone    = $req->input('phone');
        $user_det->password = Hash::make($req->input('password'));
        $user_det->display = $req->file('display') ? $req->file('display')->store('public') : "";
        $user_det->agent    = $req->input('agent') ? $req->input('agent')  : "N";
        if ($user_det) {
            $exist = User::where('email', $req->email)->first();
            if ($exist) {
                return ["error" => "Email exist please login instead", "value" => "1"];
            } else {
                $user_det->save();
            }
        }
        $user = User::where('email', $req->email)->first();
        $res_array = json_decode($user, true);
        foreach ($res_array as $res_key => $res_val) {

            if ($res_key == "banner" || $res_key == "display") {
                //$res[$reskey][$key] ="http://localhost:8000/".$value;
                $res_val = str_ireplace("public", "storage", $res_val);
                $res_array[$res_key] = "http://localhost:8000/" . $res_val;
            }

        }
        $result = json_encode($res_array, true);
        return $result;
    }

    function getUser(Request $req){
         $users = User::all();
         return $users;
    }
    function getUser_upd(Request $req){
        $user_det = User::where('username', $req->username)
            ->first();
        if (!$user_det) {
            return ["error" => "User not found"];
        }else{
            return $user_det;
        }
    }

    function checkUser_exist(Request $req){
        $user_det = User::where('username', $req->username)
            ->first();
        if ($user_det) {
            return ["message" => "Username already exist"];
        }else{
            return [];
        }
    }

    function signIn(Request $req){
        $user = User::where('email', $req->userid)
            ->orWhere('username', $req->userid)
            ->first();
        if (!$user || !Hash::check($req->password, $user->password)) {
            return ["error" => "Email or password is not matched"];
        }
        $res_array = json_decode($user, true);
        foreach ($res_array as $res_key => $res_val) {

            if ($res_key == "banner" || $res_key == "display") {
                //$res[$reskey][$key] ="http://localhost:8000/".$value;
                $res_val = str_ireplace("public", "storage", $res_val);
                $res_array[$res_key] = "http://localhost:8000/" . $res_val;
            }

        }
        $result = json_encode($res_array, true);
        return $result;
    }

    function updateUser(Request $req){
        $user_det = User::where('username', $req->session_username)
            ->first();

        if (!$user_det) {
            return ["error" => "User not found"];
        }

        $user_det->fname    = $req->input('fname');
        $user_det->lname    = $req->input('lname');
        $user_det->username = $req->input('username');
        $user_det->email    = $req->input('email');
        $user_det->phone    = $req->input('phone');

        if ($req->has('password')) {
            $user_det->password = Hash::make($req->input('password'));
        }

        if ($req->hasFile('display')) {
            // Update display image if a new one is provided
            $user_det->display = $req->file('display')->store('public');
        }

        $user_det->agent    = $req->input('agent') ? $req->input('agent')  : "N";
        ///update gallery is an agent
        if($user_det->agent == "Y") {
            $gallery = new GalleryController();
            $response = $gallery->updateUserGallery($req->session_username, $user_det->username );


            if (!$response) {
                return ["error"=>"Gallery userid was not updated successfully"];
            }
        }


        $user_det->save();


        // Return the updated user data
        $res_array = json_decode($user_det, true);
        foreach ($res_array as $res_key => $res_val) {

            if ($res_key == "banner" || $res_key == "display") {
                //$res[$reskey][$key] ="http://localhost:8000/".$value;
                $res_val = str_ireplace("public", "storage", $res_val);
                $res_array[$res_key] = "http://localhost:8000/" . $res_val;
            }

        }
        $result = json_encode($res_array, true);
        return $result;
       // return $user_det;
    }
    function forgotpass(Request $req)
    {
        $user = new User;
        $user = User::where('username', $req->username)->first();
        if (!$user) {
            $user['message'] = "User does not exist";
            return $user;
        }
        $updatefield = ([
            'token' => Str::random(60)
        ]);
        $user::where([
            'username' => $req->username
        ])->update($updatefield);
        if ($user) {
            $tokenData = User::where('username', $req->username)->first();
            if ($this->sendResetEmail($user->email, $tokenData->token)) {
                $user['message'] = "A reset link has been sent to your email address.";
                return $user;
            } else {
                $user['message'] = "A Network Error occurred. Please try again.";
                return $user;
            }
        }
    }

    function sendResetEmail($email, $token)
    {
//Retrieve the user from the database
        $user = new User;
        $user_info = $user->where('email', $email)->select('fname', 'lname', 'email')->first();
//Generate, the password reset link. The token generated is embedded in the link
        $link = "http://localhost:3000/forgotpass?token=" . $token . "&email=" . $user_info->email;

        try {
            $to_name = $user_info->fname." ".$user_info->lname;
            $to_email = $user_info->email;
            $data = [
                'name' => $to_name,
                'body' => 'Password Reset for ArtFinder',
                'link' => $link
            ];
            Mail::send('emails.password_reset', $data, function ($message) use ($to_name, $to_email) {
                $message->to($to_email, $to_name)
                    ->subject("Password Reset");
                $message->from("headartfinder@gmail.com", "ArtFinder");
            });
            return true;
        } catch (\Exception $e) {
            //echo "e == $e <br/>";
            return false;
        }
    }
}
