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
        $tipo = $request->input('tipo');
        $recommendation = $request->input('recommendation');
        // CRIANDO NOVO PET
        $newVaccineMecicament = new VaccineCard();
        $newVaccineMecicament->id_pet = $id_pet;
        $newVaccineMecicament->id_user = $id_user;
        $newVaccineMecicament->name = $name;
        $newVaccineMecicament->application_date = $application_date;
        $newVaccineMecicament->type = $tipo;
        $newVaccineMecicament->recommendation = $recommendation;
        $newVaccineMecicament->date_register = date('Y-m-d H:i:s');
        $newVaccineMecicament->save();
        $array['success'] = 'Vacina/Medicamento cadastrado com sucesso!';
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
        $List = VaccineCard::where('id_pet', $id_pet)
            ->where('id_user', 1)
            ->where('status', 1)
            ->orderBy('date_register', 'desc')
            //->offset($page * $perPage)
            ->get();


        $array['listMedicaments'] = $List;
        return $array;
    }
}
