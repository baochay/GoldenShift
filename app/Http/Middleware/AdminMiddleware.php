<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Kiểm tra người dùng đã đăng nhập và có vai trò 'quan_tri_vien'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('user') ?? auth()->user();

        if (! $user) {
            return redirect()->route('login')
                ->withInput(['email' => $request->input('email')])
                ->withErrors(['email' => 'Vui lòng đăng nhập với tài khoản Quản Trị Viên để tiếp tục.'])
                ->with('error', 'Vui lòng đăng nhập với tài khoản Quản Trị Viên để tiếp tục.');
        }

        $role = is_array($user) ? ($user['vai_tro'] ?? '') : ($user->vai_tro ?? '');

        if ($role !== 'quan_tri_vien') {
            return redirect('/')
                ->with('error', 'Bạn không có quyền truy cập vào phân hệ Quản trị viên!');
        }

        return $next($request);
    }
}
