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
}
