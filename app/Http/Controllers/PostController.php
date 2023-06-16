<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostComment;

class PostController extends Controller
{
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function like($id)
    {
        $array = ['error' => ''];
        $postExists = Post::find($id);

        if ($postExists) {
            $like = new PostLike();
            $isMeLiked = $like->meLike($id, $this->loggedUser['id']);

            if ($isMeLiked > 0) {
                // Se já dei like, remove
                $verificLike = $like->verificLike($id, $this->loggedUser['id']);
                $like->del($verificLike);
                $array['isLiked'] = false;
            } else {
                // Se não dei like, adiciona
                if ($like->newLike($id,  $this->loggedUser['id'], date('Y-m-d H:i:s'))) {
                    $array['isLiked'] = true;
                } else {
                    $array = ['error' => 'Erro ao atribuir like'];
                }
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
                $post = new Post();
                $newComment = $post->createComment($id, $this->loggedUser['id'], $txt, $parentId);

                if ($newComment) {
                    $array['comment'] = $newComment;
                } else {
                    $array['error'] = 'Erro ao adicionar comentário.';
                }
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
            $post = new Post();
            $result = $post->deleteComment($id_delete, $id_user);
            if ($result) {
                $array['message'] = 'Comentário removido com sucesso.';
            } else {
                $array['error'] = 'Erro ao remover comentário.';
            }
        } else {
            $array['error'] = 'Autor do comentário incompatível com autor da requisição.';
        }

        return $array;
    }
}
