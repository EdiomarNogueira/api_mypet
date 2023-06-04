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
        $ids_pets = Pet::selectRaw('pets.id')
        ->join('users', 'pets.id_user', '=', 'users.id')
        ->where('pets.situation', $situation)
        ->where('pets.status', 1)
        ->where('users.category', 2)
        // ->where('users.confirmed_ong', 1)
        ->distinct()
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

                $string_ano = '';
                $string_mes = '';
                $string_dia = '';
                if($intervalo->y > 1 && $intervalo->y !=0) {
                    $string_ano = $intervalo->y . " anos e ";
                } else {
                    $string_ano = $intervalo->y . " ano e ";
                }
                if($intervalo->m > 1 && $intervalo->m !=0 ) {
                    $string_mes = $intervalo->m . " meses";
                } else {
                    $string_mes = $intervalo->m . " mês";
                }

                if($intervalo->y ==0 && $intervalo->m ==0) {
                    $string_dia = $intervalo->d . " dias";
                }
                $dados[0][$key]->age = $string_ano .  $string_mes . $string_dia;
                // if ($intervalo->y > 0 && $intervalo->m > 0) {
                //     if($intervalo->y >1) {
                //         if( $intervalo->m > 1) {
                //             $dados[0][$key]->age = $intervalo->y . " anos e " . $intervalo->m . " meses ";
                //         } else {
                //             $dados[0][$key]->age = $intervalo->y . " ano e " . $intervalo->m . " mês ";
                //         }
                //     } else {
                //         if( $intervalo->m > 1) {
                //             $dados[0][$key]->age = $intervalo->y . " ano e " . $intervalo->m . " meses ";
                //         } else {
                //             $dados[0][$key]->age = $intervalo->y . " ano e um" . $intervalo->m . " mês ";
                //         }
                //     }
                // } else if ($intervalo->y > 0) {
                //     $dados[0][$key]->age = $intervalo->y . " anos ";
                // } else if ($intervalo->m > 0) {
                //     $dados[0][$key]->age = $intervalo->m . " meses ";
                // } else {
                //     $dados[0][$key]->age =  $intervalo->d . " dias";
                // }

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
