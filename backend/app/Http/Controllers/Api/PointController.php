<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;
use App\Models\MockUser;

class PointController extends Controller
{
  // mock top-up code list
  private array $codeMap = [
    'BONUS50' => 50,
    'WELCOME100' => 100,
    'FREE200' => 200,
    'FREE300' => 300,
    'FREE400' => 400,
    'FREE500' => 500,
  ];

  public function topup(Request $request)
  {
    $token = JWTAuth::getToken();
    if (!$token) {
      return response()->json(['error' => 'Token not found'], 401);
    }

    try {
      $payload = JWTAuth::parseToken()->getPayload();
    } catch (\Exception $e) {
      return response()->json(['error' => 'Invalid token'], 401);
    }

    $email = $payload->get('email');
    $topupCode = $request->input('topup_code');

    if (!isset($this->codeMap[$topupCode])) {
      return response()->json(['error' => 'Invalid topup code'], 400);
    }

    $usedKey = 'topup_used:' . $email;
    $usedCodes = Cache::get($usedKey, []);
    if (in_array($topupCode, $usedCodes)) {
      return response()->json(['error' => 'This code has already been used'], 400);
    }

    $pointsToAdd = $this->codeMap[$topupCode];
    $updatedUser = MockUser::addPoints($email, $pointsToAdd);

    if (!$updatedUser) {
      return response()->json(['error' => 'User not found'], 404);
    }

    $usedCodes[] = $topupCode;
    Cache::put($usedKey, $usedCodes, now()->addDays(7));

    $historyKey = 'topup_history:' . $email;
    $history = Cache::get($historyKey, []);
    $history[] = [
      'code' => $topupCode,
      'points' => $pointsToAdd,
      'timestamp' => now()->toDateTimeString(),
    ];
    Cache::put($historyKey, $history, now()->addDays(7));

    return response()->json([
      'message' => 'Top-up successful!',
      'topup_code' => $topupCode,
      'points_added' => $pointsToAdd,
      'new_points' => $updatedUser['points'],
      'used_codes' => $usedCodes,
      'user' => $updatedUser
    ]);
  }


  public function history()
  {
    try {
      $payload = JWTAuth::parseToken()->getPayload();
    } catch (\Exception $e) {
      return response()->json(['error' => 'Invalid token'], 401);
    }

    $email = $payload->get('email');
    $history = Cache::get('topup_history:' . $email, []);

    return response()->json([
      'email' => $email,
      'history' => $history
    ]);
  }
}
