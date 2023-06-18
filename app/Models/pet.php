<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    public $timestamps = false;
    protected $tables = "pets";

    public static function createPet($name, $id_user, $species, $birthdate, $biography, $genre, $avatar, $size, $breed, $fur, $castrated, $situation, $latitude, $longitude, $date)
    {
        try {
            $newPet = new Pet(); // Assuming "Pet" is the class name for the pet model
            $newPet->name = $name;
            $newPet->id_user = $id_user;
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
            $newPet->date_register = $date;
            $newPet->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function selectIdPet($id_user)
    {
        $id_pet = Pet::selectRaw('id')
            ->where('id_user', $id_user)
            ->where('status', 1)
            ->get();
        return $id_pet;
    }

    public static function selectDadosUserPet($pets, $id_user)
    {
        $dados[] = Pet::select('*')
            ->whereIn('id', $pets)
            ->where('id_user', $id_user)
            // ->offset($page * $perPage)
            // ->limit($perPage)
            ->where('status', 1)
            ->get();
        return $dados;
    }
    public static function selectDadosPet($id_pet)
    {
        $dados_pet = Pet::selectRaw('*')
            ->where('id', $id_pet)
            ->where('status', 1)
            ->first();
        return $dados_pet;
    }

    public static function selectCountPetUser($id, $id_pet)
    {
        $petUser = Pet::where('id_user', $id)
            ->where('id', $id_pet)
            ->count();
        return $petUser;
    }

    public static function updatePet($id_pet, $name = null, $species = null, $breed = null, $birthdate = null, $biography = null, $genre = null, $size = null, $castrated = null, $fur = null, $situation = null, $date_change)
    {
        try {
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
            $pet->date_change = $date_change;
            $pet->save();

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public static function selectPetMarked($pets_id)
    {
        $pet_marked = Pet::select('name')
            ->where('id', $pets_id)
            ->where('status', 1)
            ->get();
        return $pet_marked;
    }

    public static function selectIdsPets($situation)
    {
        $ids_pets = Pet::selectRaw('pets.id')
            ->join('users', 'pets.id_user', '=', 'users.id')
            ->where('pets.situation', $situation)
            ->where('pets.status', 1)
            ->where('users.category', 2)
            // ->where('users.confirmed_ong', 1)
            ->distinct()
            ->get();
        return $ids_pets;
    }

    public static function selectDadosPetsOngs($situation, $pets, $page, $perPage)
    {
        $dados[] = Pet::selectRaw('*')
            ->where('situation', $situation)
            ->whereIn('id', $pets)
            // ->where('id', $pet)
            ->offset($page * $perPage)
            ->limit($perPage)
            ->where('status', 1)
            ->get();
        return $dados;
    }
}
