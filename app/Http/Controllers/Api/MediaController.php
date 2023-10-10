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

    public function postVideo($id){

        $pageId = Auth::user()->facebook_page_id;
        $pageAccessToken = $this->getPageAccessToken($pageId);
        $reel = Reel::find($id);
        $videoPath = public_path('reels/'.$reel->video);
        $file = $reel->video;
        $fileSize = filesize($videoPath);

        $initializeUpload = $this->initializeUpload($pageId, $pageAccessToken, $fileSize);


        if(!empty($initializeUpload['video_id'])){
            $video_id = $initializeUpload['video_id'];
            $start_offset = $initializeUpload['start_offset'];
            $end_offset = $initializeUpload['end_offset'];
            $upload_session_id = $initializeUpload['upload_session_id'];

            while ($start_offset < $end_offset){
                $offsets = $this->uploadChunks($pageId, $pageAccessToken, $upload_session_id, $start_offset, $file, $videoPath);
                $start_offset = $offsets['start_offset'];
                $end_offset = $offsets['end_offset'];
            }

            $end_upload_session = $this->endUploadSession($pageId, $pageAccessToken, $upload_session_id);
            return response()->json(['message' => 'Video uploaded'],200);
        }

    }

    public function postReel($id){
        $pageId = Auth::user()->facebook_page_id;
        $pageAccessToken = $this->getPageAccessToken($pageId);
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

    private function getPageAccessToken($facebook_page_id){
        $user_id = Auth::user()->facebook_id;
        $access_token = Auth::user()->access_token;
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

    private function initializeUpload($pageId, $pageAccessToken, $fileSize){
        $response = Http::post("https://graph-video.facebook.com/v18.0/{$pageId}/videos", [
            'upload_phase' => 'start',
            'access_token' => $pageAccessToken,
            'file_size' => $fileSize,
        ]);

        return $response->json();

    }

    private function uploadChunks($page_id, $page_access_token, $upload_session_id, $start_offset, $file, $video_path){
        $response = Http::post("https://graph-video.facebook.com/v18.0/{$page_id}/videos", [
            'upload_phase' => 'transfer',
            'upload_session_id' => $upload_session_id,
            'access_token' => $page_access_token,
            'start_offset' => $start_offset,
            'video_file_chunk' => $video_path,
        ]);

        return $response->json();
    }

    public function endUploadSession($page_id, $page_access_token, $upload_session_id){
        $response = Http::post("https://graph-video.facebook.com/v18.0/{$page_id}/videos", [
            'upload_phase' => 'finish',
            'access_token' => $page_access_token,
            'upload_session_id' => $upload_session_id,
        ]);

        return $response->json();

    }

}
