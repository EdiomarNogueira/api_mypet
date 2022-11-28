<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pet;
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

        return $array;
    }

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
            return $array['sucess'] = 'Alteração no Pet efetuada com sucesso!';
        } else {
            $array['error'] = 'O usuário não possui este pet';
        }

        return $array;
    }

    public function updateAvatar(Request $request, $id_pet)
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

    public function updateCover(Request $request, $id_pet)
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

    public function readUserPet(Request $request, $id_user, $ids_pets = null)
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
        $perPage = 2;

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

    public function readMePet(Request $request, $id_pet = null)
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
