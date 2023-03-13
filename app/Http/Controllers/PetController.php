<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pet;
use App\Models\Alerts;
use Illuminate\Support\Arr;
use DateTime;
use Intervention\Image\Facades\Image;

class PetController extends Controller
{
    //
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function create_alert(Request $request)
    {
        $array = ['error' => ''];

        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];
        $photo = $request->file('photo');
        $id_pet = $request->input('id_pet');
        $id_user = $request->input('id_user');
        $name = $request->input('name');
        $addText = $request->input('addText');
        $situation = $request->input('situation');
        $date_occurrence = $request->input('date_occurrence');
        $road = $request->input('road');
        $district = $request->input('district');
        $city = $request->input('city');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        $lat = (float)($latitude);
        $lon = (float)($longitude);

        if ($photo) {
            if (in_array($photo->getClientMimeType(), $allowedTypes)) {

                $filename = md5(time() . rand(0, 9999)) . '.jpg';
                switch ($situation) {
                    case '2':
                        $destPath = public_path('/media/image_alerts/adoption');
                        break;
                    case '3':
                        $destPath = public_path('/media/image_alerts/lost');
                        break;
                    case '4':
                        $destPath = public_path('/media/image_alerts/found');
                        break;
                    case '5':
                        $destPath = public_path('/media/image_alerts/treatment');
                        break;
                    default:
                        $array['error'] = 'Situação do pet não informada.';
                        return $array;
                        break;
                }

                $img = Image::make($photo->path())
                    ->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })

                    ->save($destPath . '/' . $filename);

                $arquive_photo = $filename;
            } else {
                $array['error'] = 'Arquivo não suportado.';
                return $array;
            }
        } else {
            $array['error'] = 'Arquivo não enviado!';
            return $array;
        }

        $list_recipients = User::select(User::raw('id, SQRT(
            POW(69.1 * (latitude - ' . $lat . '), 2) +
            POW(69.1 * (' . $lon . ' - longitude) * COS(latitude / 57.3), 2))*1.609344 AS distance'))
            ->havingRaw('distance < ?', [10000])
            ->orderBy('distance', 'ASC')
            ->get();

        $array['var'] =  $list_recipients;
        if (count($list_recipients) == 0) {
            $array['error'] = 'Não há usuários em um raio de 10km!';
        }

        foreach ($list_recipients as $key => $recipient) {
            $newAlert = new Alerts();
            $newAlert->photo = $arquive_photo;
            $newAlert->id_pet = $id_pet;
            $newAlert->id_user = $id_user;
            $newAlert->marked_users = $recipient->id;
            $newAlert->tutor_name = $name;
            $newAlert->description = $addText;
            $newAlert->situation = $situation;
            $newAlert->date_occurrence = $date_occurrence;
            $newAlert->road = $road;
            $newAlert->city = $city;
            $newAlert->district = $district;
            $newAlert->email = $email;
            $newAlert->phone = $phone;
            $newAlert->latitude = $lat;
            $newAlert->longitude = $lon;
            $newAlert->distance = $recipient->distance;
            $newAlert->date_register = date('Y-m-d H:i:s');
            $newAlert->save();

            $pet = Pet::find($id_pet);
            $pet->situation = $situation;
            $pet->date_change = date('Y-m-d H:i:s');
            $pet->save();
            $array['success'] = "Alerta gerado!";
        }


        return $array;
    }

    public function create(Request $request)
    {

        //POST *api/user (nome, email, senha, data_nascimento, categoria)
        $array = ['error' => ''];

        $name = $request->input('name');
        $species = $request->input('species');
        $birthdate = $request->input('birthdate');
        $biography = $request->input('biography');
        $genre = $request->input('genre');
        $size = $request->input('size');
        $fur = $request->input('fur');
        $castrated = $request->input('castrated');
        $breed = $request->input('breed');
        $situation = $request->input('situation');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if ($species == 1) { //1 - cachorro, 2- gato
            $avatar = "dog_avatar_default.jpg";
        } else if ($species == 2) {
            $avatar = "cat_avatar_default.jpg";
        }
        // CRIANDO NOVO PET
        $newPet = new Pet();
        $newPet->name = $name;
        $newPet->id_user = $this->loggedUser['id'];
        $newPet->species = $species;
        $newPet->birthdate = $birthdate;
        $newPet->biography = $biography;
        $newPet->genre = $genre;
        $newPet->avatar = $avatar;
        $newPet->size = $size;
        $newPet->breed = $breed;
        $newPet->fur = $fur;
        $newPet->castrated = $castrated;
        $newPet->situation = $situation;
        $newPet->latitude = $latitude;
        $newPet->longitude = $longitude;
        $newPet->date_register = date('Y-m-d H:i:s');
        $newPet->save();
        $array['success'] = 'Pet cadastrado com sucesso!';
        return $array;
    }

    //CRIAR FUNÇÃO QUE ATUALIZA LATITUDE E LONGITUDE DOS PETS



    public function update(Request $request, $id_pet)
    {
        $array = ['error' => ''];
        //VERIFICAR SE EXISTE PET DO USUÁRIO
        $id = $this->loggedUser['id'];

        $petUser = Pet::where('id_user', $id)
            ->where('id', $id_pet)
            ->count();

        if ($petUser > 0) {
            $name = $request->input('name');
            $species = $request->input('species');
            $breed = $request->input('breed');
            $birthdate = $request->input('birthdate');
            $biography = $request->input('biography');
            $genre = $request->input('genre');
            $size = $request->input('size');
            $castrated = $request->input('castrated');
            $fur = $request->input('fur');
            $situation = $request->input('situation');
            $date_change = date('Y-m-d H:i:s');


            $pet = Pet::find($id_pet);

            if ($name) {
                $pet->name = $name;
            }

            if ($species) {
                $pet->species = $species;
            }

            if ($birthdate) {
                if (strtotime($birthdate) === false) {
                    $array['error'] = 'Data de nascimento inválida';
                    return $array;
                }
                $pet->birthdate = $birthdate;
            }

            if ($biography) {
                $pet->biography = $biography;
            }

            if ($breed) {
                $pet->breed = $breed;
            }

            if ($genre) {
                $pet->genre = $genre;
            }

            if ($size) {
                $pet->size = $size;
            }

            if ($fur) {
                $pet->fur = $fur;
            }

            if ($situation) {
                $pet->situation = $situation;
            }

            if ($castrated) {
                $pet->castrated = $castrated;
            }
            $pet->date_change = date('Y-m-d H:i:s');
            $pet->save();
            $array['success'] = 'Alteração no Pet efetuada com sucesso!';
        } else {
            $array['error'] = 'O usuário não possui este pet';
        }

        return $array;
    }

    public function update_avatar(Request $request, $id_pet)
    {
        $id = $this->loggedUser['id'];

        $array = ['error' => ''];
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];
        //VERIFICAR SE EXISTE PET DO USUÁRIO

        $petUser = Pet::where('id_user', $id)
            ->where('id', $id_pet)
            ->count();

        if ($petUser > 0) {
            $image = $request->file('avatar');
            if ($image) {
                if (in_array($image->getClientMimeType(), $allowedTypes)) {
                    $filename = md5(time() . rand(0, 9999)) . '.jpg';
                    $destPath = public_path('/media/avatars_pets');

                    $img = Image::make($image->path())
                        ->fit(200, 200)
                        ->save($destPath . '/' . $filename);

                    $pet = Pet::find($id_pet);


                    //APAGA O ARQUIVO DE AVATAR USER ANTERIOR CASO NÃO SEJA O DEFAULT
                    if (($pet->avatar != 'dog_avatar_default.jpg') && ($pet->avatar != 'cat_avatar_default.jpg')) {
                        $destPath = public_path('/media/avatars_pets');
                        if (file_exists($destPath . '/' . $pet->avatar)) {
                            unlink($destPath . '/' . $pet->avatar);
                        }
                    }

                    $pet->avatar = $filename;
                    $pet->save();

                    $array['url'] = url('/media/avatars_pets/' . $filename);
                    $array['success'] = "Avatar atualizado com sucesso!";
                } else {
                    $array['error'] = 'Arquivo não suportado!';
                    return $array;
                }
            } else {
                $array['error'] = 'Arquivo não enviado!';
                return $array;
            }
        }
        return $array;
    }

    public function update_cover(Request $request, $id_pet)
    {
        $id = $this->loggedUser['id'];

        $array = ['error' => ''];
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];
        //VERIFICAR SE EXISTE PET DO USUÁRIO

        $petUser = Pet::where('id_user', $id)
            ->where('id', $id_pet)
            ->count();
        if ($petUser > 0) {
            $image = $request->file('cover');

            if ($image) {
                if (in_array($image->getClientMimeType(), $allowedTypes)) {
                    $filename = md5(time() . rand(0, 9999)) . '.jpg';
                    $destPath = public_path('/media/covers_pets');

                    $img = Image::make($image->path())
                        ->fit(850, 310)
                        ->save($destPath . '/' . $filename);

                    $pet = Pet::find($id_pet);

                    if (($pet->cover != 'default_cover_pet.jpg')) {
                        $destPath = public_path('/media/covers_pets');
                        if (file_exists($destPath . '/' . $pet->cover)) {
                            unlink($destPath . '/' . $pet->cover);
                        }
                    }

                    $pet->cover = $filename;
                    $pet->save();

                    $array['url'] = url('/media/covers_pets/' . $filename);
                    $array['success'] = "Cover atualizado com sucesso!";
                } else {
                    $array['error'] = 'Arquivo não suportado!';
                    return $array;
                }
            } else {
                $array['error'] = 'Arquivo não enviado!';
                return $array;
            }
        }
        return $array;
    }

    public function read_alert(Request $request, $encontrado, $perdido, $adocao, $tratamento)
    {
        $array = ['error' => ''];
        $page = 5;
        $perPage = intval($request->input('perPage'));

        $array['encontrado'] = $encontrado;
        $array['perdido'] = $perdido;
        $array['adocao'] = $adocao;
        $array['tratamento'] = $tratamento;

        $filtro = [];

        if (($encontrado == 'true') || ($perdido == 'true') || ($adocao == 'true') || ($tratamento == 'true')) {
            if ($encontrado == 'true') {
                $filtro[] = 4;
            }

            if ($perdido == 'true') {
                $filtro[] = 3;
            }

            if ($adocao == 'true') {
                $filtro[] = 2;
            }

            if ($tratamento == 'true') {
                $filtro[] = 6;
            }
        } else {
            $filtro =  [2, 3, 4, 5];
        }

        $array['filtro'] = $filtro;

        $id_user =  $this->loggedUser['id'];
        $Alerts = Alerts::selectRaw('*')
            ->where('marked_users', $id_user)
            ->whereIn('situation', $filtro)
            ->limit($perPage)
            ->where('status', 1)
            ->orderBy('date_occurrence', 'desc')
            ->get();



        foreach ($Alerts as $key => $alert) {
            switch ($Alerts[$key]->situation) {
                case 2:
                    $Alerts[$key]->photo = url('media/image_alerts/adoption/' . $Alerts[$key]->photo);
                    break;
                case 3:
                    $Alerts[$key]->photo = url('media/image_alerts/lost/' . $Alerts[$key]->photo);
                    break;
                case 4:
                    $Alerts[$key]->photo = url('media/image_alerts/found/' . $Alerts[$key]->photo);
                    break;
                case 5:
                    $Alerts[$key]->photo = url('media/image_alerts/treatment/' . $Alerts[$key]->photo);
                    break;
            }

            $dados_tutor = User::selectRaw('avatar, name')
                ->where('id', $Alerts[$key]->id_user)
                ->where('status', 1)
                ->first();

            $Alerts[$key]->avatar_tutor = url('media/avatars_users/' . $dados_tutor->avatar);
            $Alerts[$key]->name_tutor = $dados_tutor->name;
            $Alerts[$key]->distance = number_format($Alerts[$key]->distance, 2, '.', '');
            $dados_pet = Pet::selectRaw('*')
                ->where('id', $Alerts[$key]->id_pet)
                ->where('status', 1)
                ->first();
            $Alerts[$key]->name_pet = $dados_pet->name;
            $Alerts[$key]->breed = $dados_pet->breed;
            $Alerts[$key]->species = $dados_pet->species;
            $Alerts[$key]->genre = $dados_pet->genre;
            $Alerts[$key]->size = $dados_pet->size;
            $Alerts[$key]->fur = $dados_pet->fur;
            $data_atual = new DateTime();
            $nascimento = new DateTime($dados_pet->birthdate);
            $intervalo = $nascimento->diff($data_atual);
            if ($dados_pet->birthdate) {
                if ($intervalo->y > 0 && $intervalo->m > 0) {
                    $Alerts[$key]->age = $intervalo->y . " anos " . $intervalo->m . " meses ";
                } else if ($intervalo->y > 0) {
                    $Alerts[$key]->age = $intervalo->y . " anos ";
                } else if ($intervalo->m > 0) {
                    $Alerts[$key]->age = $intervalo->m . " meses ";
                } else {
                    $Alerts[$key]->age =  $intervalo->d . " dias";
                }
            } else {
                $Alerts[$key]->age = 'Não estimado';
            }
        }
        $array['alerts'] = $Alerts;
        $array['countAlerts'] = count($Alerts);
        return $array;
    }

    public function read_user_pet(Request $request, $id_user, $ids_pets = null)
    {
        $array = ['error' => ''];

        if ($ids_pets == null) {
            $ids_pets = Pet::selectRaw('id')
                ->where('id_user', $id_user)
                ->where('status', 1)
                ->get();
            if (!isset($ids_pets[0])) {
                $array['error'] = 'Este usuário não possui pets cadastrados';
                return $array;
            }

            foreach ($ids_pets as $key => $id_pet) {
                $pets[$key] = $id_pet->id;
            }
        } else {
            $pets[] = $ids_pets;
        }

        $page = intval($request->input('page'));
        $perPage = 40;

        // foreach ($pets as $key => $pet) {
        $dados[] = Pet::selectRaw('*')
            ->where('id_user', $id_user)
            ->whereIn('id', $pets)
            // ->where('id', $pet)
            ->offset($page * $perPage)
            ->limit($perPage)
            ->where('status', 1)
            ->get();
        if (count($dados[0]) == 0) {
            $array['error'] = 'Pet não encontrado';
            return $array;
        }

        $total = count($dados[0]);
        $pageCount = ceil($total / $perPage);

        foreach ($dados[0] as $key => $dado) {

            $dados[0][$key]->avatar = url('media/avatars_pets/' .  $dados[0][$key]->avatar);
            $dados[0][$key]->cover = url('media/covers_pets/' .   $dados[0][$key]->cover);

            if ($dados[0][$key]->birthdate != null) {

                $data_atual = new DateTime();
                $nascimento = new DateTime($dados[0][$key]->birthdate);
                $intervalo = $nascimento->diff($data_atual);
                if ($intervalo->y > 0 && $intervalo->m > 0) {
                    $dados[0][$key]->age = $intervalo->y . " anos " . $intervalo->m . " meses ";
                } else if ($intervalo->y > 0) {
                    $dados[0][$key]->age = $intervalo->y . " anos ";
                } else if ($intervalo->m > 0) {
                    $dados[0][$key]->age = $intervalo->m . " meses ";
                } else {
                    $dados[0][$key]->age =  $intervalo->d . " dias";
                }

                $name_tutor =  User::select('name')
                    ->where('id', $dados[0][$key]->id_user)
                    // ->offset($page * $perPage)
                    // ->limit($perPage)
                    ->where('status', 1)
                    ->first();
                $dados[0][$key]->tutor_name = $name_tutor->name;
            }
        }

        $array['total'] = $total;
        $array['currentPet'] = $dados[0];
        $array['pageCount'] = $pageCount;

        return $array;
    }

    public function read_me_pet(Request $request, $id_pet = null)
    {
        $array = ['error' => ''];
        $id_user = $this->loggedUser['id'];
        if ($id_pet == null) {
            $id_pet = Pet::selectRaw('id')
                ->where('id_user', $id_user)
                ->where('status', 1)
                ->get();

            if (!isset($id_pet[0])) {
                $array['error'] = 'Este usuário não possui pets cadastrados';
                return $array;
            }
            foreach ($id_pet as $key => $pet) {
                $pets[$key] = $pet->id;
            }
        } else {
            $pets[] = $id_pet;
        }
        $perPage = intval($request->input('perPage'));

        $page = intval($request->input('page'));
        $perPage = 4;

        $dados[] = Pet::select('*')
            ->whereIn('id', $pets)
            ->where('id_user', $id_user)
            // ->offset($page * $perPage)
            // ->limit($perPage)
            ->where('status', 1)
            ->get();
        if (count($dados) == 0) {
            $array['error'] = 'Pet não encontrado';
            return $array;
        }

        $total = count($dados);
        $pageCount = ceil($total / $perPage);


        foreach ($dados[0] as $key => $dado) {

            $dados[0][$key]->avatar = url('media/avatars_pets/' .  $dados[0][$key]->avatar);
            $dados[0][$key]->cover = url('media/covers_pets/' .   $dados[0][$key]->cover);

            if ($dados[0][$key]->birthdate != null) {

                $data_atual = new DateTime();
                $nascimento = new DateTime($dados[0][$key]->birthdate);
                $intervalo = $nascimento->diff($data_atual);
                if ($intervalo->y > 0 && $intervalo->m > 0) {
                    $dados[0][$key]->age = $intervalo->y . " anos " . $intervalo->m . " meses ";
                } else if ($intervalo->y > 0) {
                    $dados[0][$key]->age = $intervalo->y . " anos ";
                } else if ($intervalo->m > 0) {
                    $dados[0][$key]->age = $intervalo->m . " meses ";
                } else {
                    $dados[0][$key]->age =  $intervalo->d . " dias";
                }

                $name_tutor =  User::select('name')
                    ->where('id', $dados[0][$key]->id_user)
                    // ->offset($page * $perPage)
                    // ->limit($perPage)
                    ->where('status', 1)
                    ->first();
                $dados[0][$key]->tutor_name = $name_tutor->name;
            }
        }

        $array['total'] = $total;
        $array['currentPet'] = $dados[0];
        $array['perPage'] = $perPage;
        $array['pageCount'] = $pageCount;

        return $array;
    }
}
