<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (!Auth::attempt($credentials)) {
            return $this->responseError('Sai tài khoản hoặc mật khẩu', 401);
        }
        $request->session()->regenerate();
        return $this->responseSuccess(null, 'Đăng nhập thành công');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return $this->responseSuccess(null, 'Đăng xuất thành công');
    }
}
