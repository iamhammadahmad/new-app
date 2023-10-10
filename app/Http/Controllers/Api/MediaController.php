<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
        $pageId = Auth::user()->facebook_page_id;
        $pageAccessToken = $this->getPageAccessToken();
        $videoData = $this->initialUpload($pageId, $pageAccessToken);

        if($videoData){
            $upload_url = $videoData['upload_url'];
            $video_id = $videoData['video_id'];


            $reel = Reel::find($id);
            $videoPath = url(asset('reels/'.$reel->video));
            $description = $reel->descrription;
            $uploadVideo = $this->uploadReel($upload_url,$pageAccessToken,$videoPath);

            if($uploadVideo){
                $published = $this->publish($pageId, $pageAccessToken, $video_id, $description);
                return response()->json(['status' => $published ? 1:0]);
            }
        }
    }

    private function getPageAccessToken(){
        $user_id = Auth::user()->facebook_id;
        $access_token = Auth::user()->access_token;
        $facebook_page_id = Auth::user()->facebook_page_id;
        $api_url = "https://graph.facebook.com/$user_id/accounts?access_token=$access_token";

        $response = Http::get($api_url);
        $responseBody = json_decode($response->getBody(), true);
        $pages = $responseBody['data'];

        foreach ($pages as $page){
            if($facebook_page_id == $page['id']){
                $pages_access_token = $page['access_token'];
                break;
            }
        }
        return $pages_access_token;
    }

    private function initialUpload($pageId , $pageAccessToken){

        $url = "https://graph.facebook.com/v18.0/{$pageId}/video_reels";

        $data = [
            "upload_phase" => "start",
            "access_token" => $pageAccessToken
        ];

        $config = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        return $response = Http::post($url, $data, $config);

    }

    private function uploadReel($upload_url,$pageAccessToken,$videoPath){

        $authorization = 'OAuth '.$pageAccessToken;
        $response = Http::withHeaders([
            'Authorization' => $authorization,
            'file_url' => $videoPath,
        ])->post($upload_url);

        if(isset($response['success']) && $response['success']){
          return true;
        }

        return false;
    }

    private function publish($pageId, $pageAccessToken, $video_id, $description){

        $url = "https://graph.facebook.com/v18.0/{$pageId}/video_reels";
        $postData = [
            'access_token' => $pageAccessToken,
            'video_id' => $video_id,
            'upload_phase' => 'finish',
            'video_state' => 'PUBLISHED',
            'description' => $description,
        ];

        return Http::post($url, $postData);

    }

}
