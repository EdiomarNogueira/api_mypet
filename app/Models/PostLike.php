<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    public $timestamps = false;
    protected $tables = "post_likes";

    public function meLike($id, $id_user)
    {
        $isLiked = PostLike::where('id_post', $id)
            ->where('id_user', $id_user)
            ->count();
        return $isLiked;
    }

    public function countLike($id)
    {
        $countLiked = PostLike::where('id_post', $id)
            ->count();
        return $countLiked;
    }

    public function verificLike($id, $id_user)
    {
        $verificLike = PostLike::where('id_post', $id)
            ->where('id_user', $id_user)
            ->first();
        return $verificLike;
    }

    public function del($verificLike)
    {
        $verificLike->delete();
    }

    public function newLike($id, $id_user, $date)
    {
        $newPostLike = new PostLike();
        $newPostLike->id_post = $id;
        $newPostLike->id_user = $id_user;
        $newPostLike->date_register = $date;
        if($newPostLike->save()){
            return true;
        } else {
            return false;
        };

    }
}
