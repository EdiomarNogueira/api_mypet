<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineCard extends Model
{
    public $timestamps = false;
    protected $tables = "vaccine_cards";

    public static function createRegister($id_pet, $id_user, $name, $application_date, $type, $recommendation, $date_register)
    {
        $newVaccineMecicament = new VaccineCard();
        $newVaccineMecicament->id_pet = $id_pet;
        $newVaccineMecicament->id_user = $id_user;
        $newVaccineMecicament->name = $name;
        $newVaccineMecicament->application_date = $application_date;
        $newVaccineMecicament->type = $type;
        $newVaccineMecicament->recommendation = $recommendation;
        $newVaccineMecicament->date_register = $date_register;
        if($newVaccineMecicament->save()){
            return true;

        } else {
            return false;
        };

    }

    public static function listVaccine($id_pet)
    {
        $list = VaccineCard::where('id_pet', $id_pet)
            ->where('id_user', 1)
            ->where('status', 1)
            ->orderBy('date_register', 'desc')
            //->offset($page * $perPage)
            ->get();
        return $list;
    }
}
