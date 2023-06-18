<?php

namespace App\Http\Controllers;

use App\Models\VaccineCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicamentPetController extends Controller
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
        $array = ['error' => ''];
        $id_user = $this->loggedUser['id'];
        $id_pet = $request->input('id_pet');
        $name = $request->input('name');
        $application_date = $request->input('application_date');
        $type = $request->input('tipo');
        $recommendation = $request->input('recommendation');
        // CRIANDO NOVO PET
        if(VaccineCard::createRegister($id_pet,$id_user,$name,$application_date, $type,$recommendation,date('Y-m-d H:i:s'))){
            $array['success'] = 'Vacina/Medicamento cadastrado com sucesso!';
        } else {
            $array['error'] = 'Falha no cadastro de Vacina/Medicamento!';
        };

        return $array;
    }

    public function read($id_pet)
    {
        //GET api/feed (page)
        $array = ['error' => ''];
        //1 - Pegar lista de usuários que EU sigo (incluindo eu mesmo)

        $user = $this->loggedUser['id'];

        $array['teste1'] = $user;
        $array['teste2'] = $id_pet;
        //2 - Pegar os posts ordenado pela data
        $List = VaccineCard::listVaccine($id_pet);



        $array['listMedicaments'] = $List;
        return $array;
    }
}
