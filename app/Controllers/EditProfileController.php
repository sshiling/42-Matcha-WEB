<?php

namespace App\Controllers;

// I 'll get information about user for user page from DB (from User Model)
use App\Models\User;
use Slim\Http\UploadedFile;

class EditProfileController extends Controller
{
    // return registration page to user
    public function getEditProfile($request, $response) {
        $user_info['who'] = $_SESSION['user'];
        $user_info['user'] = User::getUserByOneParam('id', $_SESSION['user'], $this->container['conn']);
        $user_info['user']['tags_list'] = explode(',', $user_info['user']['tags']);

        return $this->container->view->render($response, 'edit.twig', $user_info);
    }

    public function setEditProfile($request, $response) {
        $data = User::updateUserInfo($request, $response, $this->container['conn']);
        $response = $response->withJson($data);
        return $response;
    }

    public function setAvatar($request, $response) {
        $pid = $request->getParams()['pid'];
        if (User::setAvatar($_SESSION['user'], $pid, $this->container['conn'])) {
            $response = $response->withJson( array( 'status' => "OK", 'img' => $pid ) );
            return $response;
        }
        $response = $response->withJson( array( 'error' => "Error!!!" ) );
        return $response;
    }


    public function loadPhoto($request, $response) {
        $directory = '../public/uploads';
        $uploadedFiles = $request->getUploadedFiles();

        if (User::countPhotos($_SESSION['user'], $this->container['conn']) >= 5) {
            $response = $response->withJson( array( 'error' => "Error!!! To mach photos." ) );
            return $response;
        }

        $uploadedFile = $uploadedFiles['image'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $basename = bin2hex( random_bytes(8) );
            $filename = sprintf('%s.%0.8s', $basename, $extension);
            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

            if (EditProfileController::resizeImg($directory . DIRECTORY_SEPARATOR . $filename)){
                User::addUserPhotos($_SESSION['user'], ("/uploads/". $filename), $this->container['conn']);
                $response = $response->withJson("/uploads/". $filename);
            }
        }
        return $response;
    }

    public function resizeImg($path) {
        list($width, $height) = getimagesize($path);
        $thumb = imagecreatetruecolor(416, 500);
        if (exif_imagetype($path) == IMAGETYPE_JPEG)
            $source = imagecreatefromjpeg($path);
        else if (exif_imagetype($path) == IMAGETYPE_PNG)
            $source = imagecreatefrompng($path);
        else
            return 0;
        imagecopyresized($thumb, $source, 0, 0, 0, 0, 416, 500, $width, $height);
        imagejpeg($thumb, $path);
        return 1;
    }
}
