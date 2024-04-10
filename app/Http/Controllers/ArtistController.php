<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtistController extends Controller
{
    //
    function addArtist(Request $req)
    {

        $artist = new Artist;
        $artist->name = $req->input('name');
        $artist->about = $req->input('about');
        $artist->location = $req->input('location');
        $artist->contact = $req->input('contact');
        $artist->display = $req->file('display') ? $req->file('display')->store('public') : "";
        $artist->banner = $req->file('banner') ? $req->file('banner')->store('public') : "";

        $images = $req->file('images');
        $imagePaths = [];
        if (!empty($req->file('images'))) {
            foreach ($images as $image) {
                $path = $image->store('public');
                //echo "path == $path <br/>";
                $imagePaths[] = $path;
            }
        }
        $artist->images = json_encode($imagePaths);
        // var_dump($artist);
        if (empty($req->input('id'))) {
            $artist->save();
            if ($artist) return ["message" => "Successfully Updated ", "value" => "1"];
            else return ["message" => "Could not update artist information", "value" => "0"];
        }
        else {
            $artistToUpdate = Artist::find($req->input('id'));
            if ($artistToUpdate) {
                $artistToUpdate->name = $req->input('name');
                $artistToUpdate->about = $req->input('about');
                $artistToUpdate->location = $req->input('location');
                $artistToUpdate->contact = $req->input('contact');

                // Update display image if provided
                if ($req->file('display')) {
                    Storage::delete($artistToUpdate->display); // Delete previous display image
                    $artistToUpdate->display = $req->file('display')->store('public');
                }

                // Update banner image if provided
                if ($req->file('banner')) {
                    Storage::delete($artistToUpdate->banner); // Delete previous banner image
                    $artistToUpdate->banner = $req->file('banner')->store('public');
                }

                // Update images array if provided
                // Update the images if new ones are provided
                if ($req->file('images')) {
                    // Get the paths of the previous images
                    $previousImages = json_decode($artistToUpdate->images, true);

                    // Store the new images and combine them with the previous images
                    $newImages = $req->file('images');
                    $imagePaths = [];

                    // Store the new images
                    foreach ($newImages as $newImage) {
                        $path = $newImage->store('public');
                        $imagePaths[] = $path;
                    }

                    // Combine the new images with the previous images
                    if(!empty($previousImages)) {
                        $artistToUpdate->images = json_encode(array_merge($previousImages, $imagePaths));
                    }else{
                        $artistToUpdate->images = json_encode($imagePaths);
                    }
                }

                $artistToUpdate->save();

                return ["message" => "Successfully Updated", "value" => "1"];
            } else {
                return ["message" => "Artist not found", "value" => "0"];
            }
        }
    }

    function getArtist(Request $req)
    {
        $id = $req->query('id');
        $location = $req->query('location'); // Get the location from the request query parameters
        if(empty($location)) $location = "Lagos";
        if(empty($id)) {
            if (!empty($location)) {
                $result = Artist::where('location', 'LIKE', "%{$location}%")->get();
            } else {
                $result = Artist::all();
            }
        }else{
            $result = Artist::where([
                'id'=>$id

            ])->get();
        }

        $res_array = json_decode($result, true);
        foreach ($res_array as $res_key => $res_val) {
            foreach ($res_val as $key => $val) {
                if ($key == "banner" || $key == "display") {
                    //$res[$reskey][$key] ="https://api.artpathfinder.com/".$value;
                    $val = str_ireplace("public", "storage", $val);
                    $res_array[$res_key][$key] = "https://api.artpathfinder.com/" . $val;
                } elseif ($key == "images") {
                    $images = json_decode($val, true);
                    $publicImageUrls = [];
                    if (!empty($images)) {
                        foreach ($images as $image) {
                            $publicUrl = str_ireplace("public", "storage", $image);
                            $publicImageUrls[] = "https://api.artpathfinder.com/" . $publicUrl;
                        }
                    }
                    $res_array[$res_key][$key] = $publicImageUrls;
                }
            }
        }
        $result = json_encode($res_array, true);
        return $result;
    }

    function getArtistAgent(Request $req)
    {
        $userid = $req->query('username');
        if(empty($userid)) {
            return "";
        }else{
            $result = Artist::where([
                'userid'=>$userid

            ])->get();
        }

        $res_array = json_decode($result, true);
        foreach ($res_array as $res_key => $res_val) {
            foreach ($res_val as $key => $val) {
                if ($key == "banner" || $key == "display") {
                    //$res[$reskey][$key] ="https://api.artpathfinder.com/".$value;
                    $val = str_ireplace("public", "storage", $val);
                    $res_array[$res_key][$key] = "https://api.artpathfinder.com/" . $val;
                } elseif ($key == "images") {
                    $images = json_decode($val, true);
                    $publicImageUrls = [];
                    if (!empty($images)) {
                        foreach ($images as $image) {
                            $publicUrl = str_ireplace("public", "storage", $image);
                            $publicImageUrls[] = "https://api.artpathfinder.com/" . $publicUrl;
                        }
                    }
                    $res_array[$res_key][$key] = $publicImageUrls;
                }
            }
        }
        $result = json_encode($res_array, true);
        return $result;
    }

    function deleteImageArtist(Request $req){
        $id = $req->input('id');
        $image_url = $req->input('image');
        $image_url = explode("storage/",$image_url);
        $image_del = $image_url[1];
        $images = Artist::select('images')->where([
            'id'=>$id
        ])->get();

        $image_array = json_decode($images,true);
        $image_loop = explode(",",$image_array[0]['images']);
        $xi = 0;
        //print_r($image_loop);
        for($xi=0; $xi < count($image_loop); $xi++){
            $val = $image_loop[$xi];
            $saved_img = substr($val, strpos($val, '/') + 1);
            $saved_img = explode('"',$saved_img);
            $saved_img = $saved_img[0];
            if($image_del == $saved_img){
                unset($image_loop[$xi]);
            }
        }
        // print_r($image_loop);
        $xr = count($image_loop);
        $imagepath = $image_loop[0];
        foreach ($image_loop as $key => $val){
            if($key != 0) {
                $imagepath .= "," . $val;
            }
        }
        if (strpos( $imagepath, ']') === false) {
            $imagepath .= ']';
        }

        // print_r($imagepath);
        $image_updated = Artist::where('id', $id)->update(['images' => $imagepath]);
        if ($image_updated > 0) {
            // Update operation was successful
            return response()->json(["message" => "Update successful!"]);
        } else {
            // No rows were affected
            return response()->json(["error" => "No records found or no changes made."]);
        }
    }

    function updateUserArtist($session_username, $username )
    {
        // Find the user based on the provided username
        $artist_det = Artist::where('userid', $session_username)->first();

        if (!$artist_det) {
            return ["error" => "Artist details not found"];
        }

        // Update the galleries associated with the user's old username to use the new username as userid
        // Update the galleries associated with the user's old username to use the new username as userid
        Artist::where('userid', $session_username)
            ->update(['userid' => $username]);

        return true;
    }
}
