<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller{

    public function reels(){
        $reels = Reel::orderByDesc('id')->get();
        return response()->json(['reels' => $reels],200);
    }

    public function uploadVideo(Request $request){
        $file = $request->file('video');
        $fileName = $file->getClientOriginalName();
        $destinationPath = 'reels';
        $file->move($destinationPath , $fileName);

        $created = Reel::create([
            'title' => $request->title,
            'description' => $request->description,
            'video' => $fileName,
            'user_id' => auth()->id()
        ]);

        $uploadStatus = $created ? 1 : 0;
        return response()->json(['status' => $uploadStatus]);
    }

    public function postReel($id){
        $pageAccessToken = Auth::user()->access_token;
        $videoData = $this->initialUpload($pageAccessToken);

        if($videoData){
            $reel = Reel::find($id);
            $videoPath = asset('reels/'.$reel->video);
            $fileSize = filesize($videoPath);
            $uploadVideo = $this->uploadReel($videoData['video_url'],$pageAccessToken,$fileSize,$videoPath);

            if($uploadVideo){
                return $this->publish($pageAccessToken, $reel->video, $reel->description);
            }
        }
    }

    private function initialUpload($pageAccessToken){

        $url = "https://graph.facebook.com/v18.0/mmsoftwaretechnologies/video_reels";

        $data = json_encode([
            "upload_phase" => "start",
            "access_token" => $pageAccessToken
        ]);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return false;
        } else {
            return $response;
        }
    }

    private function uploadReel($uploadUrl, $pageAccessToken, $fileSize, $videoFilePath){

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

        $url = "https://graph.facebook.com/v18.0/mmsoftwaretechnologies/video_reels";

        $postData = [
            'access_token' => $pageAccessToken,
            'video_id' => $videoId,
            'upload_phase' => 'finish',
            'video_state' => 'PUBLISHED',
            'description' => $description,
        ];

        $ch = curl_init();
        $urlWithParams = $url . '?' . http_build_query($postData);

        curl_setopt($ch, CURLOPT_URL, $urlWithParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return false;
        } else {
            return true;
        }


    }

}
