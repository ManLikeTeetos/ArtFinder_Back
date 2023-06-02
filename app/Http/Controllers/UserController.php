<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    function getUser(Request $req){
         $users = User::all();
         return $users;
    }
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
}
