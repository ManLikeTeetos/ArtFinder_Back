<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

ini_set("display_errors", "on");

class GalleryController extends Controller
{
    //
    function addGallery(Request $req)
    {

        $gallery = new Gallery;
        $gallery->name = $req->input('name');
        $gallery->about = $req->input('about');
        $gallery->opening = $req->input('opening');
        $gallery->location = $req->input('location');
        $gallery->requirements = $req->input('requirements');
        $gallery->display = $req->file('display') ? $req->file('display')->store('public') : "";
        $gallery->banner = $req->file('banner') ? $req->file('banner')->store('public') : "";

        $images = $req->file('images');
        $imagePaths = [];
        if (!empty($req->file('images'))) {
            foreach ($images as $image) {
                $path = $image->store('public');
                //echo "path == $path <br/>";
                $imagePaths[] = $path;
            }
        }
        $gallery->images = json_encode($imagePaths);
        // var_dump($gallery);
        if (empty($req->input('id'))) {
            $gallery->save();
            if ($gallery) return ["message" => "Successfully Updated ", "value" => "1"];
            else return ["message" => "Could not update gallery information", "value" => "0"];
        } else {
            $galleryToUpdate = Gallery::find($req->input('id'));
            if ($galleryToUpdate) {
                $galleryToUpdate->name = $req->input('name');
                $galleryToUpdate->about = $req->input('about');
                $galleryToUpdate->opening = $req->input('opening');
                $galleryToUpdate->location = $req->input('location');
                $galleryToUpdate->requirements = $req->input('requirements');

                // Update display image if provided
                if ($req->file('display')) {
                    Storage::delete($galleryToUpdate->display); // Delete previous display image
                    $galleryToUpdate->display = $req->file('display')->store('public');
                }

                // Update banner image if provided
                if ($req->file('banner')) {
                    Storage::delete($galleryToUpdate->banner); // Delete previous banner image
                    $galleryToUpdate->banner = $req->file('banner')->store('public');
                }

                // Update images array if provided
                // Update the images if new ones are provided
                if ($req->file('images')) {
                    // Get the paths of the previous images
                    $previousImages = json_decode($galleryToUpdate->images, true);

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
                        $galleryToUpdate->images = json_encode(array_merge($previousImages, $imagePaths));
                    }else{
                        $galleryToUpdate->images = json_encode($imagePaths);
                    }
                }

                $galleryToUpdate->save();

                return ["message" => "Successfully Updated", "value" => "1"];
            } else {
                return ["message" => "Gallery not found", "value" => "0"];
            }
         }
    }

    function getGallery(Request $req)
    {
        $id = $req->query('id');
       if(empty($id)) {
           $result = Gallery::all();
       }else{
           $result = Gallery::where([
               'id'=>$id

           ])->get();
       }

        $res_array = json_decode($result, true);
        foreach ($res_array as $res_key => $res_val) {
            foreach ($res_val as $key => $val) {
                if ($key == "banner" || $key == "display") {
                    //$res[$reskey][$key] ="http://localhost:8000/".$value;
                    $val = str_ireplace("public", "storage", $val);
                    $res_array[$res_key][$key] = "http://localhost:8000/" . $val;
                } elseif ($key == "images") {
                    $images = json_decode($val, true);
                    $publicImageUrls = [];
                    if (!empty($images)) {
                        foreach ($images as $image) {
                            $publicUrl = str_ireplace("public", "storage", $image);
                            $publicImageUrls[] = "http://localhost:8000/" . $publicUrl;
                        }
                    }
                    $res_array[$res_key][$key] = $publicImageUrls;
                }
            }
        }
        $result = json_encode($res_array, true);
        return $result;
    }

    function getGalleryAgent(Request $req)
    {
        $userid = $req->query('username');
        if(empty($userid)) {
           return "";
        }else{
            $result = Gallery::where([
                'userid'=>$userid

            ])->get();
        }

        $res_array = json_decode($result, true);
        foreach ($res_array as $res_key => $res_val) {
            foreach ($res_val as $key => $val) {
                if ($key == "banner" || $key == "display") {
                    //$res[$reskey][$key] ="http://localhost:8000/".$value;
                    $val = str_ireplace("public", "storage", $val);
                    $res_array[$res_key][$key] = "http://localhost:8000/" . $val;
                } elseif ($key == "images") {
                    $images = json_decode($val, true);
                    $publicImageUrls = [];
                    if (!empty($images)) {
                        foreach ($images as $image) {
                            $publicUrl = str_ireplace("public", "storage", $image);
                            $publicImageUrls[] = "http://localhost:8000/" . $publicUrl;
                        }
                    }
                    $res_array[$res_key][$key] = $publicImageUrls;
                }
            }
        }
        $result = json_encode($res_array, true);
        return $result;
    }

    function deleteImage(Request $req){
        $id = $req->input('id');
        $image_url = $req->input('image');
        $image_url = explode("storage/",$image_url);
        $image_del = $image_url[1];
        $images = Gallery::select('images')->where([
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

       // print_r($imagepath);
        $image_updated = Gallery::where('id', $id)->update(['images' => $imagepath]);
        if ($image_updated > 0) {
            // Update operation was successful
            return response()->json(["message" => "Update successful!"]);
        } else {
            // No rows were affected
            return response()->json(["error" => "No records found or no changes made."]);
        }
    }
}
