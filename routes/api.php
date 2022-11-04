<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/ping', function(){
    return ['pong' =>true];
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// AuthController
// UserController
// FeedController
// PetController
// VaccinesPetController
// RgaPetController
// PostController
// LocationController
// SearchController
/*


*/

// Route::get('/401', [AuthController::class, 'unauthorized'])->name('login');

Route::get('/unauthenticated', function() {
    return ['error' => 'Usuario nao esta logado.'];
})->name('login');

//AUTENTICAÇÃO
Route::get('/', [AuthController::class, '']); //BUSCAR USUÁRIO LOGADO

Route::post('/user/user_register', [AuthController::class, 'create']); //CRIAR USUÁRIO
Route::post('/auth/login', [AuthController::class, 'login']); //LOGAR USUÁRIO
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::post('/validate', [AuthController::class, 'validatetoken']);
Route::middleware('auth:api')->post('/auth/logout', [AuthController::class, 'logout']); //SAIR DO USUÁRIO
Route::middleware('auth:api')->get('/auth/me', [AuthController::class, 'me']); //BUSCAR USUÁRIO LOGADO
//USUÁRIO
Route::middleware('auth:api')->put('/user', [UserController::class, 'update']); //ATUALIZAR USUÁRIO
Route::middleware('auth:api')->post('/user/avatar', [UserController::class, 'updateAvatar']); //ATUALIZAR AVATAR
Route::middleware('auth:api')->post('/user/cover', [UserController::class, 'updateCover']); //ATUALIZAR BACKGROUND DO PERFIL
Route::middleware('auth:api')->get('/user', [UserController::class, 'read']); //LER DADOS DO USUÁRIO
//OBTER DADOS DE PET CADASTRADO
Route::middleware('auth:api')->get('/user/{id}/pet', [PetController::class, 'readUserPet']); //VER PET ESPECIFICO DE USUÁRIO ESPECIFICO -> adicionar avatar e cover
Route::middleware('auth:api')->get('/user/pet/{id_pet}', [PetController::class, 'readMePet']); //VER PET ESPECIFICO DE USUÁRIO LOGADO -> adicionar avatar e cover
Route::middleware('auth:api')->get('/user/{id}/pet/{id_pet}', [PetController::class, 'readUserPet']); //VER PET ESPECIFICO DE USUÁRIO ESPECIFICO -> adicionar avatar e cover
Route::middleware('auth:api')->get('/user/pet', [PetController::class, 'readMePet']); //VER TODOS PETS DE USUÁRIO LOGADO ->adicionar avatar
//FEED
Route::middleware('auth:api')->get('/user/photos', [FeedController::class, 'userPhotos']); //VER FOGOS DO USUÁRIO
//USUÁRIO
Route::middleware('auth:api')->get('/user/{id}', [UserController::class, 'read']); //VER DADOS DE USUÁRIO ESPECIFICO
Route::middleware('auth:api')->post('/user/{id}/follow', [UserController::class, 'follow']); //SEGUIR OU DEIXAR DE SEGUIR USUÁRIO
Route::middleware('auth:api')->get('/user/{id}/followers', [UserController::class, 'followers']); //LISTA SEGUIDORES
//FEED
Route::middleware('auth:api')->post('/feed', [FeedController::class, 'create']); //CRIAR POST AO FEED
Route::middleware('auth:api')->get('/feed', [FeedController::class, 'read']); //LER POSTS DO FEED
Route::middleware('auth:api')->get('/user/feed', [FeedController::class, 'userFeed']); //VER POSTS DO USUÁRIO LOGADO
Route::middleware('auth:api')->get('/user/{id}/feed', [FeedController::class, 'userFeed']); //VER POSTS DE USUÁRIO ESPECIFICO
Route::middleware('auth:api')->get('/user/{id}/photos', [FeedController::class, 'userPhotos']);  //VER POSTAGENS COM FOTOS
// FALTA SEPARAR O FEED ENTRE POSTS "NORMAIS" E POSTS QUE SERÃO INTERPRETADOS COMO ALERTA, DE ACORDO COM UMA TAG A SER CRIADA
// ASSIM SERÃO DOIS FEEDS PRINCIPAIS, DE POSTS NORMAIS E DE POSTS DE ALERTA
// FALTA MEIO DE MARCAR OS PETS EM FOTOS, GERANDO GALERIA ESPECIFICA PARA CADA PET DE DONOS COM MAIS DE UM  PET

//CADASTRO E ATUALIZAÇÃO DE PET
Route::middleware('auth:api')->post('/user/pet', [PetController::class, 'create']); //CADASTRAR PET AO USUÁRIO LOGADO
Route::middleware('auth:api')->put('/user/pet/{id_pet}', [PetController::class, 'update']); //ATUALIZAR PET ESPECIFICO DO USUÁRIO LOGADO
Route::middleware('auth:api')->post('/user/pet/{id_pet}/avatar', [PetController::class, 'updateAvatar']); //ATUALIZAR AVATAR DO PET
Route::middleware('auth:api')->post('/user/pet/{id_pet}/cover', [PetController::class, 'updateCover']); //ATUALIZAR BACKGROUND DO PERFIL DO PET
//POST
Route::post('/post/{id}/like', [PostController::class, 'like']);
Route::post('/post/{id}/comment', [PostController::class, 'comment']);
//BUSCA
Route::get('/search', [SearchController::class, 'search']);

/*
Route::get('/user/pet/{id_pet}/vaccines', [VaccinesPetController::class, 'read']); // DA PRA USAR SOMENTE UMA RODA COM DADOS DO PET
Route::get('/user/pet/{id_pet}/rga', [RgaPetController::class, 'read']);  // DA PRA USAR SOMENTE UMA RODA COM DADOS DO PET
Route::post('/post/{id}/comment/{id_comment}/comment', [PostController::class, 'comment']); //Comentário em comentário
Route::post('/post/{id}/comment_location', [LocationController::class, 'location']);
*/
