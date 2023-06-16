<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alerts;
use App\Models\AlertComment;
use Intervention\Image\Facades\Image;

class AlertController extends Controller
{
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

                $alertExists->addComment($this->loggedUser['id'], $addText, $city, $road, $photo_alert, $district, $date_found, $latitude, $longitude);
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
        $alertComments = AlertComment::getPositions($id, $id_pet);

        if ($alertComments) {
            foreach ($alertComments as $key => $comment) {
                if ($alertComments[$key]->photo != '' && $alertComments[$key]->photo != NULL) {
                    $alertComments[$key]->photo = url('media/image_comment_alerts/' . $alertComments[$key]->photo);
                }
            }
        }

        $array['positionsAlert'] = $alertComments;
        return $array;
    }

    public function delete_comment(Request $request)
    {
        $array = ['error' => ''];
        $id_delete = intval($request->input('id_delete'));
        $id_user = intval($request->input('id_user'));

        if ($id_user == $this->loggedUser['id']) {
            $comment = AlertComment::getComment($id_user, $id_delete);

            if ($comment) {
                $comment->delete();
            }
        } else {
            $array['error'] = "Autor do comentário incompatível com autor da requisição";
        }

        return $array;
    }
}
