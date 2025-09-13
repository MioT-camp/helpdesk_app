<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理者専用ミドルウェア
 * 管理者権限を持つユーザーのみアクセス可能
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 認証されていない場合はログインページにリダイレクト
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 管理者権限がない場合は403エラー
        if (!Auth::user()->isAdmin()) {
            abort(403, 'このページにアクセスする権限がありません。');
        }

        return $next($request);
    }
}
