<?php

namespace App\Models;

use App\Sql\SqlRequest;

class Like
{
    public static function updateLikes($request, $response, $conn)
    {
        $my_id = $_SESSION['user'];
        $user_id = $request->getParams()['uid'];

        // Check if like exist. If yes - delete it. If not - create new one.
        $sql_request = "SELECT id FROM likes WHERE who = :who AND whom = :whom";
        $arguments = array("who" => $my_id, "whom" => $user_id);

        // 1. Check if like exist. If yes - delete it.
        if (($result = SqlRequest::readFromDb($sql_request, $arguments, $conn))) {

            // 1.1. Like exist, so we need to delete it.
            $sql_request = "DELETE FROM likes WHERE who = :who AND whom = :whom";
            SqlRequest::create_update_delete($sql_request, $arguments, $conn);


            // 1.2. Check if there is similar like and if yes - delete connection
            $sql_request = "SELECT id FROM likes WHERE who = :whom AND whom = :who";
            if (($result = SqlRequest::readFromDb($sql_request, $arguments, $conn))) {
                $arguments['who2'] = $arguments['who'];
                $arguments['whom2'] = $arguments['whom'];
                $sql_request = "DELETE FROM connections WHERE (one = :who AND two = :whom) OR (one = :whom2 AND two = :who2)";
                SqlRequest::create_update_delete($sql_request, $arguments, $conn);
                return array("st" => 4, "msg" => "Disconnect");
            }
            return array("st" => 3, "msg" => "Dislike");
            // 2. If like doesn't exist - create it.
        } else {
            // 2.1. Like doesn't exist. So we need to create new one
            $sql_request = "INSERT INTO likes (who, whom, add_time) VALUES (?, ?, ?)";
            $arguments = array($my_id, $user_id, time());
            SqlRequest::create_update_delete($sql_request, $arguments, $conn);

            // 2.2. When add new like check if there is mutual like and if yes - create new connection
            $sql_request = "SELECT id FROM likes WHERE who = :whom AND whom = :who";
            $arguments = array("who" => $my_id, "whom" => $user_id);
            if (($result = SqlRequest::readFromDb($sql_request, $arguments, $conn))) {
                $sql_request = "INSERT INTO connections (one, two, add_time) VALUES (?, ?, ?)";
                $arguments = array($my_id, $user_id, time());
                SqlRequest::create_update_delete($sql_request, $arguments, $conn);
                return array("st" => 2, "msg" => "Connect");
            }
            return array("st" => 1, "msg" => "Like");
        }
    }
}
