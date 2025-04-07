<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\MockUser;

class RewardController extends Controller
{
  private function getRewards()
  {
    $json = Storage::get('mock_rewards.json');
    return collect(json_decode($json, true));
  }

  public function index(Request $request)
  {
    $page = (int) $request->query('page', 1);
    $perPage = (int) $request->query('per_page', 10);

    $collection = $this->getRewards();
    $pagedData = $collection->slice(($page - 1) * $perPage, $perPage)->values();

    $paginator = new LengthAwarePaginator(
      $pagedData,
      $collection->count(),
      $perPage,
      $page,
      ['path' => url()->current()]
    );

    return response()->json([
      'data' => $paginator->items(),
      'total' => $paginator->total(),
      'current_page' => $paginator->currentPage(),
      'per_page' => $paginator->perPage(),
      'last_page' => $paginator->lastPage(),
    ]);
  }

  public function redeem(Request $request)
  {
    $rewardId = $request->input('reward_id');

    $reward = $this->getRewards()->firstWhere('id', $rewardId);
    if (!$reward) {
      return response()->json(['error' => 'Reward not found'], 404);
    }

    $payload = JWTAuth::parseToken()->getPayload();
    $email = $payload->get('email');

    $userData = MockUser::findByEmail($email);
    if (!$userData) {
      return response()->json(['error' => 'User not found'], 404);
    }

    $userPoints = $userData['points'];

    if ($userPoints < $reward['points']) {
      return response()->json(['error' => 'Insufficient points'], 400);
    }

    $updatedUser = MockUser::deductPoints($email, $reward['points']);

    $historyKey = 'reward_history:' . $email;
    $history = Cache::get($historyKey, []);
    $history[] = [
      'reward_id' => $reward['id'],
      'reward_name' => $reward['name'],
      'points_spent' => $reward['points'],
      'timestamp' => now()->toDateTimeString(),
    ];
    Cache::put($historyKey, $history, now()->addDays(7));

    return response()->json([
      'message' => 'Reward redeemed successfully',
      'reward' => $reward,
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
    $history = Cache::get('reward_history:' . $email, []);

    return response()->json([
      'email' => $email,
      'history' => $history,
    ]);
  }

  public function categories()
  {
    $categories = $this->getRewards()->pluck('category')->unique()->values();
    return response()->json($categories);
  }

  public function byCategory($category, Request $request)
  {
    // Get pagination parameters from request (default to page 1 and 12 items per page)
    $perPage = $request->input('per_page', 12);
    $page = $request->input('page', 1);

    // Validate pagination parameters
    $perPage = max(1, min(100, (int) $perPage)); // Ensure between 1-100
    $page = max(1, (int) $page); // Ensure at least 1

    $category = $this->getRewards()->firstWhere('category', $category)['category'] ?? null;

    if (!$category) {
      return response()->json(['error' => 'Category not found'], 404);
    }

    // Get all rewards in category
    $allRewards = $this->getRewards()->where('category', $category)->values();

    // Implement manual pagination
    $total = $allRewards->count();
    $rewardsInCategory = $allRewards->forPage($page, $perPage);
    $lastPage = ceil($total / $perPage);

    return response()->json([
      'category' => $category,
      'total' => $total,
      'current_page' => $page,
      'per_page' => $perPage,
      'last_page' => $lastPage,
      'data' => $rewardsInCategory,
    ]);
  }

  public function show($id)
  {
    $reward = $this->getRewards()->firstWhere('id', $id);
    if (!$reward) {
      return response()->json(['error' => 'Reward not found'], 404);
    }

    return response()->json($reward);
  }
}
