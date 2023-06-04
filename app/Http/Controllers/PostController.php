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
            $like = new PostLike();
            $isMeLiked = $like->meLike($id, $this->loggedUser['id']);
            if ($isMeLiked > 0) {
                // Se já dei like, remove
                $verificLike = $like->verificLike($id, $this->loggedUser['id']);
                $like->del($verificLike);
                $array['isLiked'] = False;
            } else {
                // Se não dei like, adiciona
                if ($like->newLike($id,  $this->loggedUser['id'], date('Y-m-d H:i:s'))) {
                    $array['isLiked'] = True;
                } else {
                    $array = ['error' => 'Erro ao atribuir like'];
                };
            }
            $countLiked = $like->countLike($id);
            $array['likeCount'] = $countLiked;
        } else {
            $array['error'] = 'Post não existe!';
            return $array;
        }
        return $array;
    }

    public function comment(Request $request, $id, $parentId = null)
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

            if ($parentId) {
                $newComment->parent_id = $parentId;
            }

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
