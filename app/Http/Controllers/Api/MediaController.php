<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reel;
use Facebook\Facebook;
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
        $reel = Reel::find($id);
        $message = $this->postVideoToFacebookPage($pageId, $reel);
        return response()->json($message);
    }

    private function postVideoToFacebookPage($pageId, $video){
        $fb = new Facebook([
            'app_id' => env('FACEBOOK_CLIENT_ID'),
            'app_secret' => env('FACEBOOK_CLIENT_SECRET'),
            'default_graph_version' => 'v18.0'
        ]);

        $pageAccessToken = $this->getPageAccessToken($pageId);
        $videoPath = public_path('reels/'.$video->video);

        $response = $fb->post($pageId.'/videos', [
            'title' => $video->title,
            'description' => $video->description,
            'source' => $fb->videoToUpload($videoPath)
        ],$pageAccessToken);

        $data = $response->getGraphNode();
        return $data['id'] ? 'Video uploaded to page successfully' : 'Unable to upload video';
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

}
