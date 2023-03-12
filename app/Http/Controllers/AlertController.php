<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alerts;
use App\Models\AlertComment;
use Illuminate\Console\View\Components\Alert;
use Intervention\Image\Facades\Image;

class AlertController extends Controller
{
    //
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function comment(Request $request, $id)
    {
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];

        $array = ['error' => ''];
        $addText = $request->input('addText');
        $date_found = $request->input('date_found');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $city = $request->input('city');
        $road = $request->input('road');
        $district = $request->input('district');
        $photo = $request->file('photo');
        $photo_alert = '';
        $alertExists = Alerts::find($id);
        if ($alertExists) {
            if ($addText) {

                if ($photo) {
                    if (in_array($photo->getClientMimeType(), $allowedTypes)) {

                        $filename = md5(time() . rand(0, 9999)) . '.jpg';
                        $destPath = public_path('/media/image_comment_alerts');

                        $img = Image::make($photo->path())
                            ->resize(800, null, function ($constraint) {
                                $constraint->aspectRatio();
                            })

                            ->save($destPath . '/' . $filename);

                        $photo_alert = $filename;
                    }
                }

                $newCommentAlert = new AlertComment();
                $newCommentAlert->id_alert = $id;
                $newCommentAlert->id_user = $this->loggedUser['id'];
                $newCommentAlert->date_register = date('Y-m-d H:i:s');
                $newCommentAlert->city = $city;
                $newCommentAlert->road = $road;
                $newCommentAlert->photo = $photo_alert;
                $newCommentAlert->district = $district;
                $newCommentAlert->body = $addText;
                $newCommentAlert->date_found = $date_found;
                $newCommentAlert->latitude = $latitude;
                $newCommentAlert->longitude = $longitude;
                $newCommentAlert->id_pet = $alertExists->id_pet;
                $newCommentAlert->save();
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

    public function positions_alert($id, $id_pet)
    {
        $array = ['error' => ''];


        $positionsAlert = AlertComment::select('latitude', 'longitude', 'photo', 'date_found', 'body')
            ->where('id_alert', $id)
            ->where('id_pet', $id_pet)
            ->where('status', 1)
            ->where('latitude', '!=', NULL)
            ->where('longitude', '!=', NULL)
            ->orderby('date_found', 'desc')
            ->get();

        if ($positionsAlert) {
            foreach ($positionsAlert as $key => $position) {
                if ($positionsAlert[$key]->photo != '' && $positionsAlert[$key]->photo != NULL) {
                    $positionsAlert[$key]->photo = url('media/image_comment_alerts/' . $positionsAlert[$key]->photo);
                }
            }
        }
        $array['positionsAlert'] = $positionsAlert;
        return $array;
    }

    public function delete_comment(Request $request)
    {
        $array = ['error' => ''];
        $id_delete = intval($request->input('id_delete'));
        $id_user = intval($request->input('id_user'));
        if ($id_user == $this->loggedUser['id']) {
            $comment = AlertComment::select('*')
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
