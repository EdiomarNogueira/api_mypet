<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerts extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = "alerts";

    public function comments()
    {
        return $this->hasMany(AlertComment::class, 'id_alert');
    }

    public static function createAlert($arquive_photo, $id_pet, $id_user, $name, $addText, $situation, $date_occurrence, $road, $city, $district, $email, $phone, $lat, $lon, $date)
    {
        try {
            $newAlert = new Alerts();
            $newAlert->photo = $arquive_photo;
            $newAlert->id_pet = $id_pet;
            $newAlert->id_user = $id_user;
            // $newAlert->marked_users = $recipient->id;
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
            // $newAlert->distance = $recipient->distance;
            $newAlert->date_register = $date;
            $newAlert->save();
            $pet = Pet::find($id_pet);
            $pet->situation = $situation;
            $pet->date_change = date('Y-m-d H:i:s');
            $pet->save();
            return true; // Usuário salvo com sucesso
        } catch (\Exception $e) {
            return false; // Erro ao salvar o usuário
        }
    }
    public function addComment($userId, $body, $city, $road, $photo, $district, $date_found, $latitude, $longitude)
    {
        $newCommentAlert = new AlertComment();
        $newCommentAlert->id_alert = $this->id;
        $newCommentAlert->id_user = $userId;
        $newCommentAlert->date_register = date('Y-m-d H:i:s');
        $newCommentAlert->city = $city;
        $newCommentAlert->road = $road;
        $newCommentAlert->photo = $photo;
        $newCommentAlert->district = $district;
        $newCommentAlert->body = $body;
        $newCommentAlert->date_found = $date_found;
        $newCommentAlert->latitude = $latitude;
        $newCommentAlert->longitude = $longitude;
        $newCommentAlert->id_pet = $this->id_pet;
        $newCommentAlert->save();
    }

    public static function selectAlerts($id_user, $id_pet, $situation)
    {
        $alerts = Alerts::select('*')
            ->where('id_user', $id_user)
            ->where('id_pet', $id_pet)
            ->where('situation', $situation)
            ->where('status', 1)
            ->get();
        return $alerts;
    }

    public static function selectListAlerts($filtro, $perPage)
    {
        $Alerts = Alerts::selectRaw('*')
            // ->where('marked_users', $id_user)
            ->whereIn('situation', $filtro)
            ->limit($perPage)
            ->where('status', 1)
            ->orderBy('date_occurrence', 'desc')
            ->get();
        return $Alerts;
    }
}
