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
        Auth::guard('admin')->logout();

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

    public function adminLogin(LoginRequest $request){
        Auth::guard('web')->logout();

        $credentials = $request->only('email', 'password');

        if (!Auth::guard('admin')->attempt($credentials)){
            throw ValidationException::withMessages([
                'password' => ['ログイン情報が登録されていません'],
            ]);
        }

        return redirect('/admin/attendance/list');

    }
}
