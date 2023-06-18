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

    public static function selectDadosTutor($id_user)
    {
        $dados_tutor = User::selectRaw('avatar, name')
            ->where('id', $id_user)
            ->where('status', 1)
            ->first();
        return $dados_tutor;
    }

    public static function createUser($name, $email, $password, $birthdate, $category, $phone)
    {
        try {
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

            return $newUser; // Usuário salvo com sucesso
        } catch (\Exception $e) {
            return false; // Erro ao salvar o usuário
        }
    }

    public static function selectName($id_user)
    {
        $name_tutor =  User::select('name')
            ->where('id', $id_user)
            // ->offset($page * $perPage)
            // ->limit($perPage)
            ->where('status', 1)
            ->first();
        return $name_tutor;
    }

    public static function selectCoornadsUser($id_user)
    {
        $coordenadas[] = User::select('latitude', 'longitude')
            ->where('id', $id_user)
            ->where('status', 1)
            ->first();
        return $coordenadas;
    }
}
