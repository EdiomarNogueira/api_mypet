<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    public $timestamps = false;
    protected $tables = "users";


    protected $hidden = [
        'password',
        'token'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function createUser($name, $email, $password, $birthdate, $category, $phone)
    {
        $newUser = new User();
        $newUser->name = $name;
        $newUser->email = $email;
        $newUser->password = password_hash($password, PASSWORD_DEFAULT);
        $newUser->birthdate = date('Y-m-d', strtotime($birthdate));
        $newUser->category = $category;
        $newUser->phone = $phone;
        $newUser->date_register = date('Y-m-d H:i:s');
        //$newUser->token = '';
        $newUser->save();

        return $newUser;
    }
}
