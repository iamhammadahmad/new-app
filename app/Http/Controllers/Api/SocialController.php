<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller{

    public function redirect(){
        $url = Socialite::driver('facebook')->stateless()->redirect()->getTargetUrl();
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
                'token' => $socialUser->token,
                'password' => encrypt('12345678')
            ]);
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
}
