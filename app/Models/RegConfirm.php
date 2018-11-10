<?php

namespace App\Models;

use App\Config\db;
use App\Sql\SqlRequest;
use App\Models\User;

class RegConfirm
{
    public static function checkNewUser($request, $response, $conn)
    {
        $email = $request->getParams()['email'];
        $hash = $request->getParams()['hash'];

        $sql_request = "SELECT * FROM users WHERE email = :email AND hash = :hash";
        $arguments = array("email" => $email, "hash" => $hash);
        if ($user = SqlRequest::readFromDb($sql_request, $arguments, $conn)[0]) {
            $_SESSION['user'] = $user['id'];
            $hash = bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
            User::updateUserDataByOneParam("confirmed", '1', "id", $user[id], $conn);
            User::updateUserDataByOneParam("hash", $hash, "id", $user[id], $conn);
            return true;
        }
        return false;
    }
}