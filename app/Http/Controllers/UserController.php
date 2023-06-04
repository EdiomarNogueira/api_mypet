<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserRelation;
use App\Models\Post;
use App\Models\Pet;

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



    public function updatePassword(Request $request)
    {
        $array = ['error' => ''];
        $password = $request->input('password');
        $password_confirm = $request->input('confirmPassword');
        $date_change = date('Y-m-d H:i:s');
        $user = User::find($this->loggedUser['id']);
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

        $array['success'] = "Senha atualizada com sucesso!";
        $user->save();

        return $array;
    }
    public function update(Request $request)
    {
        $array = ['error' => ''];
        $name = $request->input('name');
        $email = $request->input('email');
        $birthdate = $request->input('birthdate');
        $category = $request->input('category');
        $phone = $request->input('phone');
        $biography = $request->input('biography');
        $cep = $request->input('cep');
        $city = $request->input('city');
        $road = $request->input('road');
        $district = $request->input('district');
        $latitude = $request->input('latitude');
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

        if ($road) {
            $user->road = $road;
        }

        if ($district) {
            $user->district = $district;
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
            //CHAMAR FUNÇÃO DO PETCONTROLLER PASSANDO LATITUDE E LONGITUDE PARA ATUALIZAR O PET
            $this->update_location_pet($user->id, $latitude, $longitude);
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
        $array['success'] = "Usuário atualizado com sucesso!";
        $user->save();
        return $array;
    }

    public function update_location_pet($id_user, $latitude, $longitude)
    {
        $petsUser = Pet::select('id')
            ->where('id_user', $id_user)
            ->get();

        foreach ($petsUser as $petUser) {
            $pet = Pet::find($petUser->id);
            $pet->latitude = $latitude;
            $pet->longitude = $longitude;
            $pet->save();
        }
    }

    public function users_relations(Request $request, $id_user, $latitude, $longitude)
    {

        $me = User::find($this->loggedUser['id']);
        $array = ['error' => ''];
        $user = User::find($id_user);
        $lat = (float)($latitude);
        $lon = (float)($longitude);
        $page = 5;
        $perPage = intval($request->input('perPage'));

        $list_following = [];
        $list_followers = [];
        $following = UserRelation::where('user_from', $user->id)->get();
        $followers = UserRelation::where('user_to', $user->id)->get();

        if ($following) {
            foreach ($following as $key => $item) {
                $list_following[$key] = $item->user_to;
            }
        }

        if ($followers) {
            foreach ($followers as $key => $item) {
                $list_followers[$key] = $item->user_from;
            }
        }

        $list_friends = User::select('id')
            ->where('id', '!=', $user->id)
            ->whereIn('id', $list_following)
            ->whereIn('id', $list_followers)
            ->orderBy('id', 'ASC')
            ->get();

        $friends = User::select(User::raw('*, SQRT(
            POW(69.1 * (latitude - ' . $lat . '), 2) +
            POW(69.1 * (' . $lon . ' - longitude) * COS(latitude / 57.3), 2))*1.6 AS distance'))
            ->where('id', '!=', $user->id)
            ->whereIn('id', $list_following)
            ->whereIn('id', $list_followers)
            // ->havingRaw('distance < ?', [5])
            ->orderBy('distance', 'ASC')
            ->limit($perPage)
            ->get();

        $following = User::select(User::raw('*, SQRT(
                POW(69.1 * (latitude - ' . $lat . '), 2) +
                POW(69.1 * (' . $lon . ' - longitude) * COS(latitude / 57.3), 2))*1.6 AS distance'))
            ->where('id', '!=', $user->id)
            ->whereIn('id', $list_following)
            // ->havingRaw('distance < ?', [5])
            ->orderBy('distance', 'ASC')
            ->limit($perPage)
            ->get();


        $followers = User::select(User::raw('*, SQRT(
                POW(69.1 * (latitude - ' . $lat . '), 2) +
                POW(69.1 * (' . $lon . ' - longitude) * COS(latitude / 57.3), 2))*1.6 AS distance'))
            ->where('id', '!=', $user->id)
            ->whereIn('id', $list_followers)
            // ->havingRaw('distance < ?', [5])
            ->orderBy('distance', 'ASC')
            ->limit($perPage)
            ->get();


        if ($friends) {
            foreach ($friends as $key => $item) {
                $friends[$key]->avatar = url('media/avatars_users/' . $friends[$key]->avatar);
                $friends[$key]->isFollowing = true;
            }
        }

        if ($following) {
            foreach ($following as $key => $item) {
                $following[$key]->avatar = url('media/avatars_users/' . $following[$key]->avatar);
                $following[$key]->isFollowing = true;
            }
        }
        if ($followers) {
            foreach ($followers as $key => $item) {
                $followers[$key]->avatar = url('media/avatars_users/' . $followers[$key]->avatar);
                $verific_follow = $this->verific_follow($followers[$key]->id);
                $followers[$key]->isFollowing = $verific_follow['isFollower'];
                // if (in_array($followers[$key]->id, $list_following)) {
                //     $followers[$key]->isFollowing = true;
                // } else {
                //     $followers[$key]->isFollowing = false;
                // }
            }
        }

        $array['qtnFriends'] =  count($list_friends);
        $array['qtnFollowing'] =  count($list_following);
        $array['qtnFollowers'] =  count($list_followers);
        $array['following'] = $following;
        $array['followers'] = $followers;
        $array['friends'] = $friends;
        $array['currentPage'] = $page;

        return $array;
    }

    public function users_recommended($latitude, $longitude)
    {
        $array = ['error' => ''];
        $user = User::find($this->loggedUser['id']);
        $lat = (float)($latitude);
        $lon = (float)($longitude);
        $list_seguidos = [];
        $seguidos = UserRelation::where('user_from', $user->id)->get();
        if ($seguidos) {
            foreach ($seguidos as $key => $item) {
                $list_seguidos[$key] = $item->user_to;
            }
        }
        $array['seguidos'] = $list_seguidos;

        $recommended = User::select(User::raw('*, SQRT(
            POW(69.1 * (latitude - ' . $lat . '), 2) +
            POW(69.1 * (' . $lon . ' - longitude) * COS(latitude / 57.3), 2))*1.6 AS distance'))
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $list_seguidos)
            //->havingRaw('distance < ?', [5])
            ->orderBy('distance', 'ASC')
            ->get();

        if ($recommended) {
            foreach ($recommended as $key => $item) {
                $recommended[$key]->avatar = url('media/avatars_users/' . $recommended[$key]->avatar);
            }
        }
        $array['recommended'] = $recommended;
        return $array;
    }


    public function update_cover(Request $request)
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

                //APAGA O ARQUIVO DE AVATAR USER ANTERIOR CASO NÃO SEJA O DEFAULT
                if ($user->cover != 'default_cover_user.jpg') {
                    $destPath = public_path('/media/covers_users');
                    if (file_exists($destPath . '/' . $user->cover)) {
                        unlink($destPath . '/' . $user->cover);
                    }
                }

                $user->cover = $filename;
                $user->save();

                $array['url'] = url('/media/covers_users/' . $filename);
                $array['success'] = "Cover atualizado com sucesso!";
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
    public function update_avatar(Request $request)
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

                //APAGA O ARQUIVO DE AVATAR USER ANTERIOR CASO NÃO SEJA O DEFAULT
                if ($user->avatar != 'default_avatar_user.jpg') {
                    $destPath = public_path('/media/avatars_users');
                    if (file_exists($destPath . '/' . $user->avatar)) {
                        unlink($destPath . '/' . $user->avatar);
                    }
                }

                $user->avatar = $filename;
                $user->save();

                $array['url'] = url('/media/avatars_users/' . $filename);
                $array['success'] = "Avatar atualizado com sucesso!";
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
                $array['error'] = 'Usuário inexistente!';
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

        $info['followers'] = UserRelation::where('user_to', $info['id'])->count();
        $info['following'] = UserRelation::where('user_from', $info['id'])->count();


        //

        $list_following = [];
        $list_followers = [];
        $following = UserRelation::where('user_from', $id)->get();
        $followers = UserRelation::where('user_to', $id)->get();

        if ($following) {
            foreach ($following as $key => $item) {
                $list_following[$key] = $item->user_to;
            }
        }

        if ($followers) {
            foreach ($followers as $key => $item) {
                $list_followers[$key] = $item->user_from;
            }
        }
        $friends = User::where('id', '!=', $id)
            ->whereIn('id', $list_following)
            ->whereIn('id', $list_followers)

            ->count();
        $info['friends'] = $friends;

        $info['photoCount'] = Post::where('id_user', $info['id'])
            ->where('status', 1)
            ->where('type', 'photo')
            ->count();

        $hasRelation = UserRelation::where('user_from', $this->loggedUser['id'])
            ->where('user_to', $info['id'])
            ->count();

        $info['isFollowing'] = ($hasRelation > 0) ? true : false;

        $array['user'] = $info;
        return $array;
    }

    public function verific_follow($id)
    {
        $array = ['error' => ''];
        $user = User::find($this->loggedUser['id']);

        $isFollower = UserRelation::where('user_from', $user->id)->where('user_to', $id)->get();
        if (count($isFollower) > 0) {
            $array['isFollower'] = true;
        } else {
            $array['isFollower'] = false;
        }

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
            $relation = UserRelation::where('user_from', $this->loggedUser['id'])
                ->where('user_to', $id)
                ->first();

            if ($relation) {
                $relation->delete();
                $array['relation'] = false;
            } else {
                $newRelation = new UserRelation();
                $newRelation->user_from = $this->loggedUser['id'];
                $newRelation->user_to = $id;
                $newRelation->date_register = date('Y-m-d H:i:s');
                $newRelation->save();
                $array['relation'] = true;
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

            $followers = UserRelation::where('user_to', $id)->get();
            $following = UserRelation::where('user_from', $id)->get();

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
