<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->scopes('https://www.googleapis.com/auth/calendar')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->user();
        $user = User::where('google_id', $googleUser->id)->first();

        if ($user) {
            $this->saveGoogleCredentials($googleUser, $user);
            Auth::login($user);
            return redirect()->intended('calendar');
        }

        $user = User::where('email', $googleUser->email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => encrypt( uniqid())
            ]);
        }

        $this->saveGoogleCredentials($googleUser, $user);

        Auth::login($user);

        return redirect()->intended('calendar');
    }
    /**
     * @param \Laravel\Socialite\Contracts\User $googleUser
     * @param $user
     * @return void
     */
    public function saveGoogleCredentials(\Laravel\Socialite\Contracts\User $googleUser, $user): void
    {
        $user->google_id = $googleUser->id;
        $user->google_token = $googleUser->token;
        $user->google_refresh_token = $googleUser->refreshToken;
        $user->google_token_expires_in = $googleUser->expiresIn;
        $user->profile_photo_path = $googleUser->avatar;
        $user->save();
    }
}
