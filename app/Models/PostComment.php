<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    public $timestamps = false;
    protected $tables = "post_comments";

    public static function selectPostComment($id_post, $id_user)
    {
        $comments = PostComment::select('*')
            ->where('id_post', $id_post)
            ->where('id_user', $id_user)
            ->get();
        return $comments;
    }

    public static function selectInforComment($id_post) {
        $comments = PostComment::where('id_post', $id_post)
                ->whereNull('parent_id')
                ->get();
        return $comments;
    }

    public static function selectChildComments($id_post, $id_comment) {
        $childComments = PostComment::where('id_post', $id_post)
                    ->where('parent_id', $id_comment)
                    ->get();
                return $childComments;
    }
}
