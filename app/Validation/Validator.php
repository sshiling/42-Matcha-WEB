<?php

namespace App\Validation;

use App\Models\User;

class Validator
{
    public function validate($request, $response, $conn)
    {
        $firstname = $request->getParams()['firstname'];
        $lastname = $request->getParams()['lastname'];
        $email = $request->getParams()['email'];
        $login = $request->getParams()['login'];
        $password = $request->getParams()['password'];
        $re_password = $request->getParams()['re_password'];

        if (User::checkIfSuchUserExist($login, $email, $conn)) {
            return (array("st" => 1, "msg" => "User already exist!"));
        }
        if ($password !== $re_password) {
            return (array("st" => 1, "msg" => "Password 1 and re-password doesn't match!"));
        }
        if (strlen($password) < 4) {
            return (array("st" => 1, "msg" => "Password is too short! (min 4 chars)"));
        }
        $difficulty = 0;
        if (preg_match("/[a-z]/", $password)) {
            $difficulty++;
        }
        if (preg_match("/[A-Z]/", $password)) {
            $difficulty++;
        }
        if (preg_match("/[0-9]/", $password)) {
            $difficulty++;
        }
        if (preg_match("/[\!\@\#\$\%\^\&\*\(\)\_\-\+\=\\\|\/\.\,\:\;\[\]\{\}]/", $password)) {
            $difficulty++;
        }
        if ($difficulty < 2) {
            return (array("st" => 1, "msg" => "Password is too easy! (difficulty: $difficulty)"));
        }

        // !!!!!!     rewrite it. It's not correct regexp
        if (preg_match("/[^a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\_\-\+\=\\\|\/\.\,\:\;\[\]\{\}]/", $password)) {
            return (array("st" => 1, "msg" => "Forbidden chars in password!"));
        }
        if (strlen($login) < 3)
            return (array("st" => 1, "msg" => "Login too short!"));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return (array("st" => 1, "msg" => "Invalid email!"));
        }
        if (strlen($firstname) < 2) {
            return (array("st" => 1, "msg" => "First name too short"));
        }
        if (strlen($lastname) < 2) {
            return (array("st" => 1, "msg" => "Last name too short"));
        }
        return (0);
    }

    public function validatePassword($password, $re_password)
    {
        if ($password !== $re_password) {
            return (array("st" => 1, "msg" => "Password 1 and re-password doesn't match!"));
        }
        if (strlen($password) < 4) {
            return (array("st" => 1, "msg" => "Password is too short! (min 4 chars)"));
        }
        $difficulty = 0;
        if (preg_match("/[a-z]/", $password)) {
            $difficulty++;
        }
        if (preg_match("/[A-Z]/", $password)) {
            $difficulty++;
        }
        if (preg_match("/[0-9]/", $password)) {
            $difficulty++;
        }
        if (preg_match("/[\!\@\#\$\%\^\&\*\(\)\_\-\+\=\\\|\/\.\,\:\;\[\]\{\}]/", $password)) {
            $difficulty++;
        }
        if ($difficulty < 2) {
            return (array("st" => 1, "msg" => "Password is too easy! (difficulty: $difficulty)"));
        }

        // !!!!!!     rewrite it. It's not correct regexp
        if (preg_match("/[^a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\_\-\+\=\\\|\/\.\,\:\;\[\]\{\}]/", $password)) {
            return (array("st" => 1, "msg" => "Forbidden chars in password!"));
        }
        return (0);
    }
}