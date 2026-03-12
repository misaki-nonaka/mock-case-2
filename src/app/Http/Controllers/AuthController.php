<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {

            throw ValidationException::withMessages([
                'password' => ['ログイン情報が登録されていません'],
            ]);
        }

        $user = auth()->user();
        if(!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return redirect()->route('verification.notice');
        }

        return redirect()->intended('/attendance');
    }
}
