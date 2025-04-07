<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;

class MockUser implements JWTSubject
{
  public $email;
  public $name;
  public $points;

  public function __construct(array $data = [])
  {
    $this->email = $data['email'] ?? null;
    $this->name = $data['name'] ?? null;
    $this->points = $data['points'] ?? 0;
  }

  public function getJWTIdentifier()
  {
    return $this->email;
  }

  public function getJWTCustomClaims()
  {
    return [
      'name' => $this->name,
      'email' => $this->email,
      'points' => $this->points,
    ];
  }

  public static function getAllUsers()
  {
    $json = Storage::get('mock_users.json');
    return json_decode($json, true);
  }

  public static function saveAllUsers(array $users)
  {
    Storage::put('mock_users.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  }

  public static function findByEmail($email)
  {
    return collect(self::getAllUsers())->firstWhere('email', $email);
  }

  public static function update($oldEmail, $newData)
  {
    $users = self::getAllUsers();

    foreach ($users as &$user) {
      if ($user['email'] === $oldEmail) {
        $user = array_merge($user, $newData);
        self::saveAllUsers($users);
        return $user;
      }
    }

    return null;
  }

  public static function getPasswordByEmail($email)
  {
    $user = self::findByEmail($email);
    if (is_null($user)) {
      return null;
    }
    return $user['password'] ?? null;
  }
  public static function deductPoints($email, $points)
  {
    $users = self::getAllUsers();
    foreach ($users as &$user) {
      if ($user['email'] === $email) {
        $user['points'] -= $points;
        self::saveAllUsers($users);
        self::saveUsers();
        return $user;
      }
    }
    return null;
  }

  public static function saveUsers()
  {
    file_put_contents(storage_path('mock_users.json'), json_encode(self::getAllUsers(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  }
  public static function addPoints($email, $points)
  {
    $users = self::getAllUsers();
    foreach ($users as &$user) {
      if ($user['email'] === $email) {
        $user['points'] += $points;
        self::saveAllUsers($users); 
        return $user;
      }
    }
    return null;
  }

}
