<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\User_Relation;
use App\Models\Post;

use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    //
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function location()
    {
        //https://emersonbroga.com/pegar-coodenadas-geograficas-a-partir-de-um-endereco/
        $address = 'Praça Sete de Setembro - Belo Horizonte Minas Gerais ';
        $lat = null;
        $lng = null;
        $stream_opts = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];
        $json = file_get_contents("https://maps.google.com/map...®ion=$address&key=XXXXXXXXXXXXXXXX", false, stream_context_create($stream_opts));
        $xml = simplexml_load_file($json) or die("url not loading");
        $status = $xml->Response->Status->code;
        if (strcmp($status, "200") == 0) {
            // Successful geocode
            $coordinates = $xml->Response->Placemark->Point->coordinates;
            $coordinatesSplit = preg_split(",", $coordinates);
            // Format: Longitude, Latitude, Altitude
            $lat = $coordinatesSplit[1];
            $lng = $coordinatesSplit[0];
        }
        $array['latitude'] = $lat;
        $array['longitude'] = $lng;
        return $array;
    }


    public function update(Request $request)
    {
        $array = ['error' => ''];
        $latitude = $this->location();
        var_dump($latitude);
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $password_confirm = $request->input('password_confirm');
        $birthdate = $request->input('birthdate');
        $category = $request->input('category');
        $phone = $request->input('phone');
        $biography = $request->input('biography');
        $cep = $request->input('cep');
        $city = $request->input('city');
        // $latitude = $request->input('latitude');

        $longitude = $request->input('longitude');
        $facebook = $request->input('facebook');
        $instagram = $request->input('instagram');
        $genre = $request->input('genre');
        $status = $request->input('status');
        $work = $request->input('work');
        $date_change = date('Y-m-d H:i:s');

        $user = User::find($this->loggedUser['id']);

        //Name
        if ($name) {
            $user->name = $name;
        }
        //E-mail
        if ($email) {
            if ($email != $user->email) {
                $emailExists = User::where('email', $email)->count();
                if ($emailExists === 0) {
                    $user->email = $email;
                } else {
                    $array['error'] = 'Este E-mail já está em uso!';
                    return $array;
                }
            }
        }
        //BIRTHDATE
        if ($birthdate) {
            if (strtotime($birthdate) === false) {
                $array['error'] = 'Data de nascimento inválida';
                return $array;
            }
            $user->birthdate = $birthdate;
        }
        //CITY
        if ($city) {
            $user->city = $city;
        }
        //WORK
        if ($work) {
            $user->work = $work;
        }
        //BIOGRAPHY
        if ($biography) {
            $user->biography = $biography;
        }
        //PASSWORD
        if ($password && $password_confirm) {
            if ($password === $password_confirm) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $user->password = $hash;
            } else {
                $array['error'] = 'As senhas não coincidem';
                return $array;
            }
        }

        //DATE CHANGE
        if ($date_change) {
            $user->date_change = $date_change;
        }
        //PHONE
        if ($phone) {

            $rule = [
                'phone' => 'celular_com_ddd',
            ];

            $validator = Validator::make($request->all(), $rule);

            if ($validator->fails()) {
                $array['error'] = $validator->messages();
                return $array;
            }
            $user->phone = $phone;
        }
        //GENRE
        if ($genre) {
            $user->genre = $genre;
        }
        //CEP
        if ($cep) {
            $rule = [
                'cep' => 'formato_cep',
            ];

            $validator = Validator::make($request->all(), $rule);

            if ($validator->fails()) {
                $array['error'] = $validator->messages();
                return $array;
            }

            $user->cep = $cep;
        }

        if ($latitude && $longitude) {
            $user->latitude = $latitude;
            $user->longitude = $longitude;
        } else if (($latitude == null && $longitude) || ($latitude && $longitude == null)) {
            $array['error'] = 'É necessário cadastrar a latitude e a longitude!';
            return $array;
        }

        //PERFIL DO INSTAGRAM
        if ($instagram) {
            $user->instagram = $instagram;
        }
        //PERFIL DO FACEBOOK
        if ($facebook) {
            $user->facebook = $facebook;
        }

        $user->save();
        return $array;
    }

    public function updateCover(Request $request)
    {
        $array = ['error' => ''];
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $image = $request->file('cover');

        if ($image) {
            if (in_array($image->getClientMimeType(), $allowedTypes)) {
                $filename = md5(time() . rand(0, 9999)) . '.jpg';
                $destPath = public_path('/media/covers_users');

                $img = Image::make($image->path())
                    ->fit(850, 310)
                    ->save($destPath . '/' . $filename);

                $user = User::find($this->loggedUser['id']);
                $user->cover = $filename;
                $user->save();

                $array['url'] = url('/media/covers_users/' . $filename);
            } else {
                $array['error'] = 'Arquivo não suportado!';
                return $array;
            }
        } else {
            $array['error'] = 'Arquivo não enviado!';
            return $array;
        }
        return $array;
    }
    public function updateAvatar(Request $request)
    {
        $array = ['error' => ''];
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $image = $request->file('avatar');

        if ($image) {
            if (in_array($image->getClientMimeType(), $allowedTypes)) {
                $filename = md5(time() . rand(0, 9999)) . '.jpg';
                $destPath = public_path('/media/avatars_users');

                $img = Image::make($image->path())
                    ->fit(200, 200)
                    ->save($destPath . '/' . $filename);

                $user = User::find($this->loggedUser['id']);
                $user->avatar = $filename;
                $user->save();

                $array['url'] = url('/media/avatars_users/' . $filename);
            } else {
                $array['error'] = 'Arquivo não suportado!';
                return $array;
            }
        } else {
            $array['error'] = 'Arquivo não enviado!';
            return $array;
        }
        return $array;
    }

    public function read($id = false)
    {
        // GET api/user
        // GET api/user/123
        $array = ['error' => ''];

        if ($id) {
            $info = User::find($id);
            if (!$info) {
                $array['error'] = 'Usuário inexistente';
                return $array;
            }
        } else {
            $info = $this->loggedUser;
        }

        $info['avatar'] = url('media/avatars_users/' . $info['avatar']);
        $info['cover'] = url('media/covers_users/' . $info['cover']);

        $info['me'] = ($info['id'] == $this->loggedUser['id']) ? true : false;
        $dateFrom = new \DateTime($info['birthdate']);
        $dateTo = new \DateTime('today');
        $info['age'] = $dateFrom->diff($dateTo)->y;

        $info['followers'] = User_Relation::where('user_to', $info['id'])->count();
        $info['following'] = User_Relation::where('user_from', $info['id'])->count();
        $info['photoCount'] = Post::where('id_user', $info['id'])
            ->where('status', 1)
            ->where('type', 'photo')
            ->count();

        $hasRelation = User_Relation::where('user_from', $this->loggedUser['id'])
            ->where('user_to', $info['id'])
            ->count();

        $info['isFollowing'] = ($hasRelation > 0) ? true : false;

        $array['user'] = $info;
        return $array;
    }

    public function follow($id)
    {
        $array = ['error' => ''];

        if ($id == $this->loggedUser['id']) {
            $array['error'] = 'Você não pode seguir a si mesmo.';
            return $array;
        }

        $userExists = User::find($id);
        if ($userExists) {
            $relation = User_Relation::where('user_from', $this->loggedUser['id'])
                ->where('user_to', $id)
                ->first();

            if ($relation) {
                $relation->delete();
            } else {
                $newRelation = new User_Relation();
                $newRelation->user_from = $this->loggedUser['id'];
                $newRelation->user_to = $id;
                $newRelation->date_register = date('Y-m-d H:i:s');
                $newRelation->save();
            }
        } else {
            $array['error'] = 'Usuário inexistente!';
            return $array;
        }
        return $array;
    }

    public function followers($id)
    {
        $array = ['error' => '', 'users' => []];
        $userExists = User::find($id);
        if ($userExists) {

            $followers = User_Relation::where('user_to', $id)->get();
            $following = User_Relation::where('user_from', $id)->get();

            $array['followers'] = [];
            $array['following'] = [];

            foreach ($followers as $item) {
                $user = User::find($item['user_from']);
                $array['followers'][] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'avatar' => url('media/avatars_users/' . $user['avatar'])
                ];
            }

            foreach ($following as $item) {
                $user = User::find($item['user_from']);
                $array['following'][] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'avatar' => url('media/avatars_users/' . $user['avatar'])
                ];
            }
        } else {
            $array['error'] = 'Usuário inexistente!';
            return $array;
        }
        return $array;
    }
}
