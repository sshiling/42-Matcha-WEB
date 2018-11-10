<?php

namespace App\Models;

use App\Config\db;
use App\Sql\SqlRequest;
use App\Email\Email;

use App\Models\Tags;

class User
{
    // create new user in database
    public static function createUser($request, $response, $conn)
    {
        $login = $request->getParams()['login'];
        $password = hash("whirlpool", $request->getParams()['password']);
        $time_of_creation = time();
        $time_of_last_visit = $time_of_creation;

        $mail['firstname'] = $request->getParams()['firstname'];
        $mail['lastname'] = $request->getParams()['lastname'];
        $mail['email'] = $request->getParams()['email'];
        $mail['hash'] = bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
        $mail['link'] = 'http://'.$_SERVER['HTTP_HOST']."/reg_confirm" . "?email=". $mail['email'] . "&hash=" . $mail['hash'];
        $mail['subject'] = 'Matcha Profile Confirmation';
        $mail['body'] = 'Hi, '. $mail['firstname'] . ' ' . $mail['lastname'] . '! <br> For finishing your registration process follow this link: <a href=' . $mail['link'] . '>Confirm Regitration</a>';

        $sql_request = "INSERT INTO users (firstname, lastname, email, login, password, hash, time_of_creation, time_of_last_visit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $arguments = array($mail['firstname'], $mail['lastname'], $mail['email'], $login, $password, $mail['hash'], $time_of_creation, $time_of_last_visit);

        if ($result = SqlRequest::create_update_delete($sql_request, $arguments, $conn)) {
            Email::send_mail($mail);
            return 1;
        }
        return 0;
    }

    public static function getUserByOneParam($key, $value, $conn) {
        $sql_request = "SELECT 
                              *,
                              (
                               25 * ( SELECT COUNT(likes.id) FROM likes WHERE likes.whom = users.id ) +
                              100 * ( SELECT COUNT(connections.id) FROM connections WHERE connections.one = users.id OR connections.two = users.id ) 
                              ) AS rating, 
                              (SELECT path FROM photos WHERE photos.user_id = users.id AND is_main = 1 LIMIT 1 ) as avatar
                        FROM users
                        WHERE $key = :$key";
        $arguments = array($key => $value);

        $user = SqlRequest::readFromDb($sql_request, $arguments, $conn)[0];
        $user['photos'] = User::getUserPhotos($user['id'] ,$conn);

        return ($user);
    }

    public static function checkIfSuchUserExist($login, $email, $conn) {
        $sql_request = "SELECT * FROM users WHERE email = :email OR login = :login";
        $arguments = array("email" => $email, "login" => $login);
        return (SqlRequest::readFromDb($sql_request, $arguments, $conn)[0]);
    }

    // login user
    public static function login($request, $conn)
    {
        $login = $request->getParams()['login'];
        $password = hash("whirlpool", $request->getParams()['password']);

        $sql_request = "SELECT * FROM users WHERE login = :login AND password = :password LIMIT 1";
        $arguments = array("login" => $login, "password" => $password);

        if ($result = SqlRequest::readFromDb($sql_request, $arguments, $conn)) {
            if ($result[0]['confirmed']) {
                $_SESSION['user'] = User::getUserByOneParam("login", $login, $conn)['id'];
                return array("st" => 0, "msg" => "OK");
            } else {
                return array('st' => 1, 'msg' => "Your email is not confirmed!");
            }
        }
        return array("st" => 1, "msg" => "Login or password error!");
    }

    // get user from db by it's param
    public static function getUserData($key, $value, $conn)
    {
        // I'm not sure if brackets work correct without brackets
        $sql = "SELECT * FROM users WHERE $key = '{$value}'";

        $stm = $conn->prepare($sql);

        $stm->execute();

        if (empty($result = $stm->fetchAll())) {
            return NULL;
        } else {
            return $result[0];
        }
    }

    public static function updateUserDataByOneParam($upd_key, $upd_value, $param_key, $param_value, $conn)
    {
        $sql_request = "UPDATE users SET $upd_key = :$upd_key WHERE $param_key = :$param_key";
        $arguments = array($upd_key => $upd_value, $param_key => $param_value);
        SqlRequest::create_update_delete($sql_request, $arguments, $conn);
    }

    public function delete($request, $response) { }

    public static function getUsersList($user, $conn) {

        $sql_request = "SELECT *,
                            (
                                6371 * ACOS(
                                    COS(RADIANS(latitude)) * COS(RADIANS(".$user['latitude'].")) * COS(
                                        RADIANS(".$user['longitude'].") - RADIANS(longitude)
                                    ) + SIN(RADIANS(latitude)) * SIN(RADIANS(".$user['latitude']."))
                                )
                            ) AS distance,                            
                            (
                               25 * ( SELECT COUNT(likes.id) FROM likes WHERE likes.whom = users.id ) +
                              100 * ( SELECT COUNT(connections.id) FROM connections WHERE connections.one = users.id OR connections.two = users.id ) 
                            ) AS rating,
                            (SELECT path FROM photos WHERE photos.user_id = users.id AND photos.is_main = 1) as avatar
                        FROM
                            users
                        WHERE ";

        if ($user['sexual_preference'] == 2) {
            $sql_request .= "id <> :id";
            $arguments = array('id' => $_SESSION['user']);
        }
        else {
            $sql_request .= "id <> :id AND gender = :pref";
            $arguments = array('id' => $_SESSION['user'], 'pref' => $user['sexual_preference']);
        }
        return (SqlRequest::readFromDb($sql_request, $arguments, $conn));
    }

    public static function updateUserInfo($request, $response, $conn) {

        $arguments = array(
            'firstname' => $request->getParams()['firstname'],
            'lastname' => $request->getParams()['lastname'],
            'email' => $request->getParams()['email'],
            'age' => $request->getParams()['age'],
            'gender' => $request->getParams()['gender'],
            'sexual_preference' => $request->getParams()['sexual_preference'],
            'biography' => $request->getParams()['biography'],
            'tags' => $request->getParams()['tags'],
            'user_id' => $_SESSION['user'],
            'location' => $request->getParams()['location'],
            'longitude' => $request->getParams()['longitude'],
            'latitude' => $request->getParams()['latitude']
        );

        foreach ($arguments as $key => $arg) {
            if (strlen($arg) == 0)
                return array('st' => 1, 'msg' => 'Error! '.$key.' field is empty!');
            if ($key == 'age' && strlen($arg) > 3)
                return array('st' => 1, 'msg' => 'Error! '.$key.' field is not correct!');
            if ($key == 'age' && (intval($arg) < 18 || intval($arg) > 100 || intval($arg) != $arg))
                return array('st' => 1, 'msg' => 'Error! '.$key.' field is invalid!');
        }

        $data = explode(',', $request->getParams()['tags']);
        Tags::checkTags($data, $conn);

        $sql_request = "UPDATE users 
                        SET 
                            firstname = :firstname,
                            lastname = :lastname,
                            email = :email,
                            age = :age,
                            gender = :gender,
                            sexual_preference = :sexual_preference,
                            biography = :biography,
                            tags = :tags,
                            location = :location,
                            longitude = :longitude,
                            latitude = :latitude
                        WHERE id = :user_id";

        if (SqlRequest::create_update_delete($sql_request, $arguments, $conn))
            return array('st' => 0, 'msg' => 'Success');
        return array('st' => 1, 'msg' => 'Nothing to save');
    }

    public static function user_is_authorized() {
        if (isset($_SESSION['user']))
            return true;
        return false;
    }

    public static function checkIfBlocked($who, $whom, $conn) {
        $sql_request = "SELECT * FROM blocks WHERE who = :who AND whom = :whom";
        $arguments = array("who" => $who, "whom" => $whom);
        if ($result = SqlRequest::readFromDb($sql_request, $arguments, $conn)) {
            return true;
        }
        return false;
    }

    public static function restorePasswordEmail($email, $conn) {
        if (($result = self::getUserData("email", $email, $conn))) {
            $hash = bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));

            $mail['firstname'] = $result['firstname'];
            $mail['lastname'] = $result['lastname'];
            $mail['email'] = $email;
            $mail['hash'] = $hash;
            $mail['link'] = 'http://'.$_SERVER['HTTP_HOST']."/new_password" . "?email=". $mail['email'] . "&hash=" . $mail['hash'];
            $mail['subject'] = 'Matcha Password Recovery';
            $mail['body'] = 'Hi, ' . $mail['firstname'] . ' ' . $mail['lastname'] . '! <br> To recover your password please follow this link: <a href=' . $mail['link'] . '>Password Recovery</a> ';

            self::updateUserDataByOneParam('hash', $hash, 'email', $mail['email'], $conn);
            Email::send_mail($mail);
            return array('st' => 0, "msg" => "New password has been sent to your email.");
        }
        return array('st' => 1, "msg" => "Email is not exist.");
    }

    public static function addUserPhotos($uid, $path, $conn) {
        $sql_request = "INSERT INTO `photos`(`user_id`, `path`, `is_main`) VALUES ( ?, ?, 0)";
        $arguments = array($uid, $path);
        return SqlRequest::create_update_delete($sql_request, $arguments, $conn);
    }

    public static function getUserPhotos($uid, $conn) {
        $sql_request = "SELECT * FROM photos WHERE user_id = :uid AND is_main = 0";
        $arguments = array("uid" => $uid);
        return (SqlRequest::readFromDb($sql_request, $arguments, $conn));
    }

    public static function countPhotos($uid, $conn) {
        $sql_request = "SELECT * FROM photos WHERE user_id = :uid";
        $arguments = array("uid" => $uid);
        return (count(SqlRequest::readFromDb($sql_request, $arguments, $conn)));
    }

    public static function setAvatar($uid, $pic, $conn) {

        $sql_request = "UPDATE photos SET is_main = 0 WHERE user_id = ?;";
        $arguments = array( $uid );
        SqlRequest::create_update_delete($sql_request, $arguments, $conn);

        $sql_request = "UPDATE photos SET is_main = 1 WHERE user_id = ? AND path = ?;";
        $arguments = array( $uid, $pic );
        return SqlRequest::create_update_delete($sql_request, $arguments, $conn);
    }

    public static function checkEmailAndHash($email, $hash, $conn) {
        $sql_request = "SELECT * FROM users WHERE email = :email AND hash = :hash LIMIT 1";
        $arguments = array("email" => $email, "hash" => $hash);
        if (($result = SqlRequest::readFromDb($sql_request, $arguments, $conn)[0])) {
            return true;
        }
        return false;
    }

    public static function updatePassword($request, $conn) {
        $email = $request->getParams()['email'];
        $password = hash("whirlpool", $request->getParams()['password']);
        self::updateUserDataByOneParam("password", $password, "email", $email, $conn);
        $hash = bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
        self::updateUserDataByOneParam("hash", $hash, "email", $email, $conn);
    }


    public static function getNotifications($user_id, $conn)
    {
        // Check if report exist. If not - create new one.
        $sql_request = "SELECT * FROM notifications WHERE user_id = :user_id";
        $arguments = array("user_id" => $user_id);
        // Check if report doesn't exist.
        if (!($result = SqlRequest::readFromDb($sql_request, $arguments, $conn))) {
            return null;
        } else {
            return $result;
        }
    }

    public static function hasPhotos($user_id, $conn) {
        $sql_request = "SELECT * FROM photos WHERE user_id = :user_id";
        $arguments = array("user_id" => $user_id);

        if (!($result = SqlRequest::readFromDb($sql_request, $arguments, $conn))) {
            return false;
        } else {
            return true;
        }
    }

    public static function checkIfLiked($who, $whom, $conn) {
        $sql_request = "SELECT * FROM likes WHERE who = :who AND whom = :whom LIMIT 1";
        $arguments = array("who" => $who, "whom" => $whom);
        if (!($result = SqlRequest::readFromDb($sql_request, $arguments, $conn)))
            return 0;
        return 1;

    }

    public static function checkIfConnected($one, $two, $conn) {
        $one2 = $two;
        $two2 = $one;

        $sql_request = "SELECT * FROM connections WHERE (one = :one AND two = :two) OR (one = :one2 AND two = :two2) LIMIT 1";
        $arguments = array("one" => $one, "two" => $two, "one2" => $one2, "two2" => $two2);
        if (!($result = SqlRequest::readFromDb($sql_request, $arguments, $conn)))
            return 0;
        return 1;
    }

    public static function getConnectionList($conn) {

        $sql_request = "SELECT 
                            one AS me, two AS who, 
                            (SELECT CONCAT(firstname, ' ', lastname) FROM users WHERE users.id = who) as who_name,
                            (SELECT path FROM photos WHERE photos.user_id = who AND photos.is_main = 1) as who_pic
                        FROM
                            connections
                        WHERE one  = ?
                        UNION
                        SELECT
                            two AS me, one AS who,
                            (SELECT CONCAT(firstname, ' ', lastname) FROM users WHERE users.id = who) as who_name,
                            (SELECT path FROM photos WHERE photos.user_id = who AND photos.is_main = 1) as who_pic
                        FROM
                            connections
                        WHERE
                            two = ?;";
        $arguments = array($_SESSION['user'], $_SESSION['user']);
        return SqlRequest::readFromDb($sql_request, $arguments, $conn);
    }
    public static function hasEmptyFields($user) {
//        echo ('<pre>');
//        print_r($user);
//        die();

        foreach ($user as $key => $value) {
            if (strlen($value) == 0 && $key != 'photos') {
                return true;
            }
        }
        return false;



    }

    public static function userIsBlockedOrBlockMe($one, $two, $conn) {
        $one2 = $two;
        $two2 = $one;

        $sql_request = "SELECT * FROM blocks WHERE (who = :one AND whom = :two) OR (who = :one2 AND whom = :two2) LIMIT 1";
        $arguments = array("one" => $one, "two" => $two, "one2" => $one2, "two2" => $two2);
        if (!($result = SqlRequest::readFromDb($sql_request, $arguments, $conn)))
            return false;
        return true;
    }

    public static function getUserListForMap($conn) {
        $sql_request = "SELECT
                          (SELECT path FROM photos WHERE photos.user_id = users.id AND is_main = 1 LIMIT 1 ) as pic,
                          CONCAT(firstname, ' ', lastname) as name,
                          longitude,
                          latitude
                        FROM 
                          users 
                        WHERE 1";
        if (!($result = SqlRequest::readFromDb($sql_request, array(), $conn)))
            return 0;
        return $result;
    }

}
