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

    public static function selectPostLikeId($id)
    {
        $likes = PostLike::where('id_post', $id)
            ->where('status', 1)
            ->get();
        return $likes;
    }

    public static function selectPostLike($id_delete, $id_user)
    {
        $likes = PostLike::select('*')
            ->where('id_post', $id_delete)
            ->where('id_user', $id_user)
            ->get();
        return $likes;
    }

    public static function selectPostLikeUserStatus($id_post, $id_user,$status)
    {
        $isLiked = PostLike::where('id_post', $id_post)
            ->where('id_user', $id_user)
            ->where('status', $status)
            ->count();
        return $isLiked;
    }

    public static function selectPostLikeStatus($id_post, $status)
    {
        $likes = PostLike::where('id_post', $id_post)->where('status', $status)->count();
        return $likes;
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
        if ($newPostLike->save()) {
            return true;
        } else {
            return false;
        };
    }
}
