<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class MockJwtAuth
{
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->getPayload(); // แค่ validate token เท่านั้น ไม่เรียก user
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
