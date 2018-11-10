<?php

namespace App\Models;

use App\Sql\SqlRequest;

class Report
{
    public static function reportUser($request, $response, $conn)
    {
        $my_id = $_SESSION['user'];
        $user_id = $request->getParams()['uid'];
        $report_time = time();
        $arguments = array("who" => $my_id, "whom" => $user_id);
        $arguments2 = array($my_id, $user_id, $report_time);

        // Check if report exist. If not - create new one.
        $sql_request = "SELECT id FROM reports WHERE who = :who AND whom = :whom";

        // Check if report doesn't exist.
        if (!($result = SqlRequest::readFromDb($sql_request, $arguments, $conn))) {
            // Report doesn't exist. So we need to create new one
            $sql_request = "INSERT INTO reports (who, whom, add_time) VALUES (?, ?, ?)";
            SqlRequest::create_update_delete($sql_request, $arguments2, $conn);
            return array("st" => 0, "msg" => "OK");
            // Report already exist.
        } else {
            return array("st" => 1, "msg" => "OK");
        }
    }
}
