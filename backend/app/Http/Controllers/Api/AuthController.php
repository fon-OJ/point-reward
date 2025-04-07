<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\MockUser;

class AuthController extends Controller
{

  public function login(Request $request)
  {
    $email = $request->input('email');
    $password = $request->input('password');

    $userData = MockUser::findByEmail($email);

    if (!$userData || !Hash::check($password, $userData['password'])) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = new MockUser($userData);
    $token = JWTAuth::fromUser($user);

    return response()->json([
      'access_token' => $token,
      'token_type' => 'bearer',
      'expires_in' => JWTAuth::factory()->getTTL() * 60,
      'user' => [
        'email' => $user->email,
        'name' => $user->name,
        'points' => $user->points
      ]
    ]);
  }


  public function me()
  {
    $payload = JWTAuth::parseToken()->getPayload();
    $email = $payload->get('email');

    $userData = MockUser::findByEmail($email);

    if (!$userData) {
      return response()->json(['error' => 'User not found'], 404);
    }

    return response()->json([
      'email' => $userData['email'],
      'name' => $userData['name'],
      'points' => $userData['points'],
    ]);
  }

  public function logout()
  {
    JWTAuth::invalidate(JWTAuth::getToken());
    return response()->json(['message' => 'Successfully logged out']);
  }

  public function refresh()
  {
    return response()->json([
      'access_token' => JWTAuth::refresh(JWTAuth::getToken()),
      'token_type' => 'bearer',
      'expires_in' => JWTAuth::factory()->getTTL() * 60,
    ]);
  }


  public function topup(Request $request)
  {
    $payload = JWTAuth::parseToken()->getPayload();
    $topupCode = $request->input('topup_code');

    // mock topup codes
    $codeMap = [
      'FREE100' => 100,
      'BONUS50' => 50,
      'TEST10' => 10,
    ];

    if (!isset($codeMap[$topupCode])) {
      return response()->json(['error' => 'Invalid topup code'], 400);
    }

    $pointsToAdd = $codeMap[$topupCode];
    $newPoints = $payload->get('points') + $pointsToAdd;

    return response()->json([
      'message' => "Top-up successful!",
      'topup_code' => $topupCode,
      'points_added' => $pointsToAdd,
      'new_points' => $newPoints
    ]);
  }
  public function updateProfile(Request $request)
  {
    $payload = JWTAuth::parseToken()->getPayload();

    $oldEmail = $payload->get('email');
    $newEmail = $request->input('email');
    $newName = $request->input('name');

    $userData = MockUser::findByEmail($oldEmail);

    if (!$userData) {
      return response()->json(['error' => 'User not found'], 404);
    }
    $updatedUserData = MockUser::update($oldEmail, [
      'email' => $newEmail,
      'name' => $newName,
    ]);

    $hasEmailChanged = $oldEmail !== $newEmail;

    if ($hasEmailChanged) {
      JWTAuth::invalidate(JWTAuth::getToken());

      return response()->json([
        'message' => 'Email updated successfully',
        'require_relogin' => true,
      ]);
    }

    return response()->json([
      'message' => 'Update successful',
      'require_relogin' => false,
      'user' => [
        'email' => $updatedUserData['email'],
        'name' => $updatedUserData['name'],
        'points' => $updatedUserData['points'],
      ]
    ]);
  }
  public function changePassword(Request $request)
  {
    $payload = JWTAuth::parseToken()->getPayload();
    $email = $payload->get('email');
    $oldPassword = $request->input('old_password');
    $newPassword = $request->input('new_password');

    $userData = MockUser::findByEmail($email);

    if (!$userData || !Hash::check($oldPassword, $userData['password'])) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Update password in mock data
    $userData['password'] = Hash::make($newPassword);
    MockUser::update($email, ['password' => $userData['password']]);

    return response()->json(['message' => 'Password changed successfully']);
  }
}
