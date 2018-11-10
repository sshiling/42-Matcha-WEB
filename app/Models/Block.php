<?php

namespace App\Models;

use App\Sql\SqlRequest;

class Block
{
    // Check if user block exist. If yes - delete it. If not - create new one.
    public static function blockUser($request, $response, $conn)
    {
        $my_id = $_SESSION['user'];
        $user_id = $request->getParams()['uid'];
        $add_time = time();

        $sql_request = "SELECT id FROM blocks WHERE who = :who AND whom = :whom";
        $arguments = array("who" => $my_id, "whom" => $user_id);

        // 1. Check if block exist. If yes - delete it.
        if (($result = SqlRequest::readFromDb($sql_request, $arguments, $conn))) {

            // Block exist, so we need to delete it.
            $sql_request = "DELETE FROM blocks WHERE who = :who AND whom = :whom";
            SqlRequest::create_update_delete($sql_request, $arguments, $conn);
            return array("st" => 0, "msg" => "OK");
            // 2. If block doesn't exist - create it.
        } else {
            // 2.1. Block doesn't exist. So we need to create new one
            $sql_request = "INSERT INTO blocks (who, whom, add_time) VALUES (?, ?, ?)";
            $arguments2 = array($my_id, $user_id, $add_time);
            SqlRequest::create_update_delete($sql_request, $arguments2, $conn);

            // 2.2. When add new block - delete like if exist
            $sql_request = "DELETE FROM likes WHERE who = :who AND whom = :whom";
            SqlRequest::create_update_delete($sql_request, $arguments, $conn);

            // 2.3. When add new block - delete connection if exist
            $sql_request = "DELETE FROM connections WHERE (one = :who AND two = :whom) OR (one = :whom2 AND two = :who2)";
            $arguments['who2'] = $arguments['who'];
            $arguments['whom2'] = $arguments['whom'];
            SqlRequest::create_update_delete($sql_request, $arguments, $conn);
            return array("st" => 1, "msg" => "OK");
        }
    }
}
