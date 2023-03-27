<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pet;
use App\Models\Alerts;
use Illuminate\Support\Arr;
use DateTime;
use Intervention\Image\Facades\Image;

class OngController extends Controller
{
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function read_pets_ongs(Request $request, $situation)
    {
        $array = ['error' => ''];
        $ids_pets = Pet::selectRaw('id')
            ->where('situation', $situation)
            ->where('status', 1)
            ->get();
        if (!isset($ids_pets[0])) {
            $array['error'] = 'Não há pets para adoção cadastrados por ongs';
            return $array;
        }

        foreach ($ids_pets as $key => $id_pet) {
            $pets[$key] = $id_pet->id;
        }


        $page = intval($request->input('page'));
        $perPage = 40;

        // foreach ($pets as $key => $pet) {
        $dados[] = Pet::selectRaw('*')
            ->where('situation', $situation)
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

    //FUNÇÕES DE BUSCA DE PETS DE ACORDO A CATEGORIA

}
