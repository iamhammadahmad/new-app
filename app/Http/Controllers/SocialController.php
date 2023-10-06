<?php

namespace App\Http\Controllers;

use App\Models\User;
use CURLFile;
use Facebook\Authentication\AccessToken;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function redirect(){
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function callbackUrl(){
        $socialUser = Socialite::driver('facebook')->stateless()->user();
        $user = User::where('email',$socialUser->email)->first();

        if($user){
            User::where('email',$socialUser->email)->update(['token' => $socialUser->token]);
        }else{
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'facebook_id' => $socialUser->getId(),
                'token' => $socialUser->token,
                'password' => encrypt('12345678')
            ]);
        }

        Auth::login($user);
        return redirect()->route('home');
    }

    public function postReel(Request $request){
        $pageAccessToken = Auth::user()->token;

        $videoData = $this->initialUpload($pageAccessToken);
        if($videoData){
            $file = $request->file('video');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $destinationPath = 'reels';
            $file->move($destinationPath , $fileName);
            $videoPath = asset('reels/'.$fileName);
            $description = $request->description;


            $uploadVideo = $this->uploadVideo($videoData['video_url'],$pageAccessToken,$fileSize,$videoPath);

            if($uploadVideo){
                return $this->publish($pageAccessToken, $fileName, $description);
            }
        }
    }



    private function initialUpload($pageAccessToken){

        $url = "https://graph.facebook.com/v18.0/me/video_reels";

        $data = json_encode([
            "upload_phase" => "start",
            "access_token" => $pageAccessToken
        ]);

        $ch = curl_init();

// Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

// Execute the cURL request
        $response = curl_exec($ch);
        curl_close($ch);

// Check for cURL errors and handle the response
        if ($response === false) {
            return false;
        } else {
            return $response;
        }
    }

    private function uploadVideo($uploadUrl, $pageAccessToken, $fileSize, $videoFilePath){

        $ch = curl_init($uploadUrl);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            'Authorization: OAuth ' . $pageAccessToken,
            'offset: 0',
            'file_size: ' . $fileSize,
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $fileHandle = fopen($videoFilePath, 'rb');
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, fread($fileHandle, filesize($videoFilePath)));
        fclose($fileHandle);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return false;
        } else {
            return true;
        }
    }

    private function publish($pageAccessToken , $videoId, $description){

// Define the URL for the request
        $url = "https://graph.facebook.com/v18.0/me/video_reels";

// Create an array of POST data
        $postData = [
            'access_token' => $pageAccessToken,
            'video_id' => $videoId,
            'upload_phase' => 'finish',
            'video_state' => 'PUBLISHED',
            'description' => $description,
        ];

// Initialize cURL session
        $ch = curl_init();

// Build the URL with query parameters
        $urlWithParams = $url . '?' . http_build_query($postData);

// Set cURL options
        curl_setopt($ch, CURLOPT_URL, $urlWithParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

// Execute the cURL request
        $response = curl_exec($ch);
        curl_close($ch);

// Check for cURL errors and handle the response
        if ($response === false) {
            return false;
        } else {
            return true;
        }

// Close the cURL session

    }
}
