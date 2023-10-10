<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller{

    public function redirect(){
        $scopes = ['pages_show_list','pages_read_engagement','pages_manage_posts'];
        $url = Socialite::driver('facebook')->scopes($scopes)->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }

    public function callbackUrl(){
        $socialUser = Socialite::driver('facebook')->stateless()->user();
        $user = User::where('email',$socialUser->email)->first();

        if($user){
            User::where('email',$socialUser->email)->update(['access_token' => $socialUser->token]);
        }else{
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'facebook_id' => $socialUser->getId(),
                'access_token' => $socialUser->token,
                'password' => encrypt('12345678')
            ]);

            $facebook_page_id = $this->getFacebookPageId($user);
            $user->update(['facebook_page_id' => $facebook_page_id]);
        }

        Auth::login($user);
        $accessToken = auth()->user()->createToken('authToken')->accessToken;
        return response()->json(['user' => auth()->user(), 'token' => $accessToken]);

    }

    public function logout(){

        $user = Auth::user()->token();
        User::where('id',Auth::id())->update(['access_token'=>null]);
        $user->revoke();
        return response()->json(['status' => 1],200);

    }

    protected function getFacebookPageId($user){
        $user_id = $user->facebook_id;
        $access_token = $user->access_token;
        $api_url = "https://graph.facebook.com/$user_id/accounts?access_token=$access_token";

        $response = Http::get($api_url);
        $responseBody = json_decode($response->getBody(), true);

        return $responseBody['data'][0]['id'];

    }
}
