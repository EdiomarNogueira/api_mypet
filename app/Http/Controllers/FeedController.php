<?php

namespace App\Http\Controllers;

// use App\Events\Post\NewPost;
use App\Models\Alerts;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserRelation;
use App\Models\Post;
use App\Models\Pet;
use App\Models\PostLike;
use App\Models\PostComment;
// use Illuminate\Broadcasting\Channel;
// use Illuminate\Console\View\Components\Alert;
use Illuminate\Support\Arr;
// use Illuminate\Support\Facades\Event;
use Intervention\Image\Facades\Image;
// use Illuminate\Support\Facades\Storage;

class FeedController extends Controller
{
    //
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function create(Request $request)
    {
        $array = ['error' => ''];
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $type = $request->input('type');
        $body = $request->input('body');
        $photo = $request->file('photo');
        $subtitle = $request->input('subtitle');
        $pets = json_encode($request->input('pets'));

        if ($type) {
            switch ($type) {
                case 'text':
                    if (!$body) {
                        $array['error'] = 'Texto não enviado!';
                        return $array;
                    }
                    break;

                case 'photo':
                    if ($photo) {
                        if (in_array($photo->getClientMimeType(), $allowedTypes)) {

                            $filename = md5(time() . rand(0, 9999)) . '.jpg';
                            $destPath = public_path('/media/uploads');

                            $img = Image::make($photo->path())
                                ->resize(800, null, function ($constraint) {
                                    $constraint->aspectRatio();
                                })

                                ->save($destPath . '/' . $filename);

                            $body = $filename;
                        } else {
                            $array['error'] = 'Arquivo não suportado.';
                            return $array;
                        }
                    } else {
                        $array['error'] = 'Arquivo não enviado!';
                        return $array;
                    }
                    break;

                default:
                    $array['error'] = 'Tipo de postagem inexistente';
                    return $array;
                    break;
            }

            if ($body) {
                Post::createPost($this->loggedUser['id'], $type, date('Y-m-d H:i:s'), $body, $pets, $subtitle);

                //Event::dispatch(new NewPost($newPost, 'user'.$this->loggedUser['id']));
            }
        } else {
            $array['error'] = 'Dados não enviados';
            return $array;
        }
        return $array;
    }

    public function read_updates()
    {
        //count em posts
        //count em mensagens em posts
        //count em likes em posts
        $array = ['error' => ''];
        $users = [];
        $userList = UserRelation::Where('user_from', $this->loggedUser['id'])->get();
        foreach ($userList as $userItem) {
            $users[] = $userItem['user_to'];
        }

        $users[] = $this->loggedUser['id'];
        $count_posts = Post::countPosts($users);

        $array['count'] = $count_posts;
        $autor_post = Post::autorPost($users);
        $array['autor_post'] = $autor_post;

        return $array;
    }

    public function read(Request $request)
    {
        //GET api/feed (page)
        $array = ['error' => ''];
        $page = 6;
        $perPage = intval($request->input('perPage'));
        //1 - Pegar lista de usuários que EU sigo (incluindo eu mesmo)
        $users = [];
        $userList = UserRelation::Where('user_from', $this->loggedUser['id'])->get();
        foreach ($userList as $userItem) {
            $users[] = $userItem['user_to'];
        }

        $users[] = $this->loggedUser['id'];
        //2 - Pegar os posts ordenado pela data
        $postList = Post::postList($users, $perPage);


        // $total = Post::whereIn('id_user', $users)->count();
        $pageCount = ceil($perPage);

        //3 - Preencher as informações adicionais
        $posts = $this->_postListToObject($postList, $this->loggedUser['id']);

        $array['count_posts'] = $this->read_updates();
        $array['posts'] = $posts;
        $array['pageCount'] = $pageCount;
        $array['currentPage'] = $page;
        return $array;
    }

    public function delete_alert(Request $request)
    {
        $array = ['error' => ''];
        $id_alert = intval($request->input('id_alert'));
        $id_user = intval($request->input('id_user'));
        $situation = intval($request->input('situation'));
        $id_pet = intval($request->input('id_pet'));
        $filename = null;

        $alerts = Alerts::selectAlerts($id_user, $id_pet, $situation);
        //BUSCAR TODOS COMENTÁRIOS DO ALERTA E DELETAR
        //BUSCAR IMAGENS DE COMENTÁRIOS DE ALERTA E APAGAR
        foreach ($alerts as $alert) {
            if ($alert) {
                $filename = $alert->photo;
                $destPath = '';
                switch ($alert->situation) {
                    case '2':
                        $destPath = public_path('/media/image_alerts/adoption');
                        break;
                    case '3':
                        $destPath = public_path('/media/image_alerts/lost');
                        break;
                    case '4':
                        $destPath = public_path('/media/image_alerts/found');
                        break;
                    case '5':
                        $destPath = public_path('/media/image_alerts/treatment');
                        break;
                    default:
                        $array['error'] = 'Situação do pet não informada.';
                        return $array;
                        break;
                }

                if (file_exists($destPath . '/' . $filename)) {
                    unlink($destPath . '/' . $filename);
                }

                $alert->delete();
            }
        }

        $array['success'] = "Alerta Deletado.";
        return $array;
    }

    public function delete_post(Request $request)
    {
        $array = ['error' => ''];

        $id_delete = intval($request->input('id_delete'));
        $id_user = intval($request->input('id_user'));
        $filename = null;

        if ($id_user == $this->loggedUser['id']) {
            $post = Post::selectPost($id_user, $id_delete);
        } else {
            $array['error'] = "Autor do post incompatível com autor da requisição";
        }

        if ($post) {
            // APAGA FOTO DO POST
            if ($post->type == 'photo') {
                $filename = $post->body;
            }
            //APAGA LIKES DO POST
            $likes = PostLike::selectPostLike($id_delete, $id_user);
            //APAGA COMENTÁRIO DO POST
            $comments = PostComment::selectPostComment($id_delete, $id_user);

            if ($comments) {
                foreach ($comments as $item) {
                    $item->delete();
                }
            }
            if ($likes) {
                foreach ($likes as $item) {
                    $item->delete();
                }
            }

            if ($filename) {
                $destPath = public_path('/media/uploads');
                if (file_exists($destPath . '/' . $filename)) {
                    unlink($destPath . '/' . $filename);
                }
            }

            $post->delete();
            $array['success'] = "Post Deletado.";
        }

        return $array;
    }

    public function read_likes($id)
    {
        $array = ['error' => ''];
        $likes = PostLike::selectPostLikeId($id);
        $array['liked'] = false;
        foreach ($likes as $key => $like) {
            if ($like->id_user == $this->loggedUser['id']) {
                $array['liked'] = true;
                break;
            }
        }

        $array['likeCount'] = count($likes);
        // $array['likes'] = $likes;
        return $array;
    }

    private function _postListToObject($postList, $loggedId)
    {
        foreach ($postList as $postKey => $postItem) {

            //VERIFICA SE O POST É MEU
            if ($postItem['id_user'] == $loggedId) {
                $postList[$postKey]['mine'] = true;
            } else {
                $postList[$postKey]['mine'] = false;
            }

            // Preencher informações de usuário
            $userInfo = User::find($postItem['id_user']);
            $userInfo['avatar'] = url('media/avatars_users/' . $userInfo['avatar']);
            $userInfo['cover'] = url('media/covers_users/' . $userInfo['cover']);
            $postList[$postKey]['user'] = $userInfo;
            // Preencher informações de LIKE
            $likes = PostLike::selectPostLikeStatus($postItem['id'], 1);
            $postList[$postKey]['likeCount'] = $likes;
            $isLiked = PostLike::selectPostLikeUserStatus($postItem['id'], $loggedId, 1);
            $postList[$postKey]['liked'] = ($isLiked > 0) ? true : false;
            // Preencher informações de COMMENTS
            $comments = PostComment::selectInforComment($postItem['id']);
            foreach ($comments as $commentsKey => $comment) {
                $user = User::find($comment['id_user']);
                $user['avatar'] = url('media/avatars_users/' . $user['avatar']);
                $user['cover'] = url('media/covers_users/' . $user['cover']);
                $comments[$commentsKey]['user'] = $user;
                // Buscar comentários filhos
                $childComments = PostComment::selectChildComments($postItem['id'], $comment['id']);
                foreach ($childComments as $childKey => $childComment) {
                    $childUser = User::find($childComment['id_user']);
                    $childUser['avatar'] = url('media/avatars_users/' . $childUser['avatar']);
                    $childUser['cover'] = url('media/covers_users/' . $childUser['cover']);
                    $childComments[$childKey]['user'] = $childUser;
                }
                $comments[$commentsKey]['childComments'] = $childComments;
            }

            $postList[$postKey]['comments'] = $comments;

            if ($postItem['type'] == 'photo') {
                $postItem['body'] = url('media/uploads/' . $postItem['body']);
            }

            if ($postItem['marked_pets']) {
                //var_dump('item', $postItem->marked_pets);
                $array_pets = json_decode($postItem->marked_pets);
                $postList[$postKey]['marked_pets'] = [];
                $pets = [];
                if ($array_pets) {
                    foreach ($array_pets as $key => $pets_id) {
                        if ($pets_id != null) {
                            $pet_marked = Pet::selectPetMarked($pets_id);
                            $pets[$key]['name'] = $pet_marked[0]->name;
                            $pets[$key]['id_pet'] = $pets_id;
                        }
                    }
                    $postList[$postKey]['marked_pets'] = $pets;
                }
            }
        }

        return $postList;
    }

    public function user_feed(Request $request, $id = false)
    {

        $array = ['error' => ''];

        if ($id == false) {
            $id = $this->loggedUser['id'];
        }
        $page = intval($request->input('page'));
        $perPage = 4;
        // Pegar os posts do usuário ordenado pela data
        $postList = Post::selectPostList($id, $page, $perPage);
        $total = Post::countTotalPosts($id);
        $pageCount = ceil($total / $perPage);
        // Preencher as informações adicionais
        $posts = $this->_postListToObject($postList, $this->loggedUser['id']);
        $array['posts'] = $posts;
        $array['pageCount'] = $pageCount;
        $array['currentPage'] = $page;
        return $array;
    }


    public function user_photos_pet(Request $request, $id = false, $id_pet = false)
    {
        $array = ['error' => ''];

        if ($id == false) {
            $id = $this->loggedUser['id'];
        }

        $page = 1;
        $perPage = intval($request->input('perPage'));

        // Pegar as fotos do usuário ordenado pela data
        if ($id_pet) {
            $postList = Post::postListPhotoMarkedPet($id, $id_pet, $perPage);
        } else {
            $postList = Post::postListPhoto($id, $page, $perPage);
        }

        $posts = $this->_postListToObject($postList, $this->loggedUser['id']);

        // foreach ($posts as $pkey => $post) {
        //     $posts[$pkey]['body'] = url('media/uploads/' . $posts[$pkey]['body']);
        // }

        $array['posts'] = $posts;
        //$array['pageCount'] = $pageCount;
        $array['currentPage'] = $page;

        return $array;
    }

    public function user_photos(Request $request, $id = false)
    {
        $array = ['error' => ''];

        if ($id == false) {
            $id = $this->loggedUser['id'];
        }

        $page = 1;
        $perPage = intval($request->input('perPage'));
        // Pegar as fotos do usuário ordenado pela data
        $postList = Post::postListPhotoLimit($id, $perPage);
        $total = Post::totalPostPhoto($id);
        $pageCount = ceil($total / $perPage);
        // Preencher as informações adicionais
        $posts = $this->_postListToObject($postList, $id);

        // foreach ($posts as $pkey => $post) {
        //     $posts[$pkey]['body'] = url('media/uploads/' . $posts[$pkey]['body']);
        // }

        $array['posts'] = $posts;
        $array['pageCount'] = $pageCount;
        $array['currentPage'] = $page;

        return $array;
    }
}
