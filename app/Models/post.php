<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public $timestamps = false;
    protected $table = "posts";

    public static function createPost($id_user, $type, $date, $body, $pets, $subtitle = null)
    {
        $newPost = new Post();
        $newPost->id_user = $id_user;
        $newPost->type = $type;
        $newPost->date_register = $date;
        $newPost->body = $body;
        $newPost->marked_pets = $pets;
        $newPost->subtitle = $subtitle;
        $newPost->save();
    }

    public static function totalPostPhoto($id_user)
    {
        $total = Post::where('id_user', $id_user)
            ->where('type', 'photo')
            ->count();
        return $total;
    }

    public static function postListPhotoLimit($id, $perPage)
    {
        $postList = Post::where('id_user', $id)
            ->where('type', 'photo')
            ->orderBy('date_register', 'desc')
            //->offset($page * $perPage)
            ->limit($perPage)
            ->get();
        return $postList;
    }
    public static function countTotalPosts($id)
    {
        $total = Post::where('id_user', $id)->count();
        return $total;
    }
    public static function selectPost($id_user, $id_delete)
    {
        $post = Post::select('*')
            ->where('id_user', $id_user)
            ->where('id', $id_delete)
            ->where('status', 1)
            ->first();
        return $post;
    }

    public static function postListPhotoMarkedPet($id, $id_pet, $perPage)
    {
        $postList = Post::where('id_user', $id)
            ->where('type', 'photo')
            ->whereJsonContains('marked_pets', $id_pet)
            ->orderBy('date_register', 'desc')
            //->offset($page * $perPage)
            ->limit($perPage)
            ->get();
        return $postList;
    }

    public static function postListPhoto($id, $page, $perPage)
    {
        $postList = Post::where('id_user', $id)
            ->where('type', 'photo')
            ->orderBy('date_register', 'desc')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();
        return $postList;
    }
    public static function selectPostList($id, $page, $perPage)
    {
        $postList = Post::where('id_user', $id)
            ->orderBy('date_register', 'desc')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        return $postList;
    }

    public static function postList($users, $perPage)
    {
        $postList = Post::whereIn('id_user', $users)
            ->where('status', 1)
            ->where('situation', 0)
            ->orderBy('date_register', 'desc')
            //->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        return $postList;
    }

    public static function countPosts($users)
    {
        $countPosts = Post::whereIn('id_user', $users)
            ->where('status', 1)
            ->where('situation', 0)
            ->get()->count();
        return $countPosts;
    }

    public static function autorPost($users)
    {
        $autor_post = Post::select('id_user')
            ->whereIn('id_user', $users)
            ->where('status', 1)
            ->where('situation', 0)
            ->orderBy('date_register', 'desc')
            ->first();
        return $autor_post;
    }

    public function createComment($postId, $userId, $body, $parentId = null)
    {
        $comment = new PostComment();
        $comment->id_post = $postId;
        $comment->id_user = $userId;
        $comment->date_register = date('Y-m-d H:i:s');
        $comment->body = $body;

        if ($parentId) {
            $comment->parent_id = $parentId;
        }

        $comment->save();

        return $comment;
    }

    public function deleteComment($commentId, $userId)
    {
        $comment = PostComment::where('id', $commentId)
            ->where('id_user', $userId)
            ->where('status', 1)
            ->first();

        if ($comment) {
            return $comment->delete();
        }

        return false;
    }
}
