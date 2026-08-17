<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Catches the case where an admin disables an account that's already logged
 * in (Fortify's authenticateUsing check only runs at login time, so a live
 * session for a since-disabled user would otherwise stay valid until it
 * naturally expires).
 */
class EnsureAccountIsActive
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isDisabled()) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', __('تم تعطيل هذا الحساب. الرجاء التواصل مع الإدارة.'));
        }

        return $next($request);
    }
}
