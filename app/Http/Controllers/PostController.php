<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostComment;

class PostController extends Controller
{
    //
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function like($id)
    {
        $array = ['error' => ''];
        // SE EXISTE
        $postExists = Post::find($id);
        if ($postExists) {
            $isLiked = PostLike::where('id_post', $id)
                ->where('id_user', $this->loggedUser['id'])
                ->count();
            if ($isLiked > 0) {
                // Se já dei like, remove
                $pl = PostLike::where('id_post', $id)
                    ->where('id_user', $this->loggedUser['id'])
                    ->first();
                $pl->delete();

                $array['isLiked'] = False;
            } else {
                // Se não dei like, adiciona
                $newPostLike = new PostLike();
                $newPostLike->id_post = $id;
                $newPostLike->id_user = $this->loggedUser['id'];
                $newPostLike->date_register = date('Y-m-d H:i:s');
                $newPostLike->save();

                $array['isLiked'] = True;
            }

            $likeCount = PostLike::where('id_post', $id)->count();
            $array['likeCount'] = $likeCount;
        } else {
            $array['error'] = 'Post não existe!';
            return $array;
        }
        return $array;
    }

    public function comment(Request $request, $id)
    {
        $array = ['error' => ''];
        $txt = $request->input('txt');
        $postExists = Post::find($id);

        if ($postExists) {
            if ($txt) {
                $newComment = new PostComment();
                $newComment->id_post = $id;
                $newComment->id_user = $this->loggedUser['id'];
                $newComment->date_register = date('Y-m-d H:i:s');
                $newComment->body = $txt;
                $newComment->save();
            } else {
                $array['error'] = 'Não enviou mensagem.';
                return $array;
            }
        } else {
            $array['error'] = 'Post não existe';
            return $array;
        }

        return $array;
    }

    public function delete_comment(Request $request)
    {
        $array = ['error' => ''];
        $id_delete = intval($request->input('id_delete'));
        $id_user = intval($request->input('id_user'));
        if ($id_user == $this->loggedUser['id']) {
            $comment = PostComment::select('id')
                ->where('id_user', $id_user)
                ->where('id', $id_delete)
                ->where('status', 1)
                ->first();

            if ($comment) {
                $comment->delete();
            }
        } else {
            $array['error'] = "Autor do comentário incompatível com autor da requisição";
        }

        return $array;
    }
}
