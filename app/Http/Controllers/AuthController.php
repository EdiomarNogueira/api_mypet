<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'login',
                'create',
                'unauthorized',
            ]
        ]);
    }

    public function create(Request $request)
    {

        //POST *api/user (nome, email, senha, data_nascimento, categoria)
        $array = ['error' => ''];

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|string|max:255',
            'password' => 'required',
            'phone' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $array['error'] = $validator->messages();
            return $array;
        }


        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $birthdate = $request->input('birthdate');
        $category = $request->input('category');
        $phone = $request->input('phone');
        $newUser = User::createUser($name, $email, $password, $birthdate, $category, $phone);
        //CRIANDO NOVO USUÁRIO


        //LOGAR USUÁRIO RECEM CRIADO
        $token = Auth::login($newUser);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $newUser,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function login(Request $request)
    {
        //$array = ['error' => ''];

        $creds = $request->only('email', 'password');

        $token = Auth::attempt($creds);
        $user = Auth::user();

        if ($token && $user) {
            $array['token'] = $token;
            $array['user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ];
        } else {
            $array['error'] = 'E-mail e/ou senha incorretos';
        }

        return $array;
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function me()
    {
        $array = ['error' => ''];
        $user = Auth::user();

        $array['id'] = $user->id;
        $array['name'] = $user->name;
        $array['phone'] = $user->phone;
        $array['email'] = $user->email;
        $array['latitude'] = $user->latitude;
        $array['longitude'] = $user->longitude;
        $array['category'] = $user->category;
        $array['avatar'] = url('media/avatars_users/' . $user->avatar);
        $array['cover'] = url('media/covers_users/' . $user->cover);
        return $array;
    }

    public function refresh()
    {
        $user = Auth::user();
        $token = Auth::refresh();

        $array['data'] = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
            'token' => $token,
        ];

        return $array['data'];
        //
    }

    public function validate_token()
    {
        $user = Auth::user();
        $array['data'] = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ],
        ];
        return $array['data'];
    }
}
