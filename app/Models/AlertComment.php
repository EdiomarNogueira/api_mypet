<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertComment extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = "alert_comments";

    public function alert()
    {
        return $this->belongsTo(Alerts::class, 'id_alert');
    }

    public static function getPositions($id, $id_pet)
    {
        return self::select('latitude', 'longitude', 'photo', 'date_found', 'body')
            ->where('id_alert', $id)
            ->where('id_pet', $id_pet)
            ->where('status', 1)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('date_found', 'desc')
            ->get();
    }

    public static function getComment($id_user, $id_delete)
    {
        return self::where('id_user', $id_user)
            ->where('id', $id_delete)
            ->where('status', 1)
            ->first();
    }
}
