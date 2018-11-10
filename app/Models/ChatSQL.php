<?php

namespace App\Models;

use App\Sql\SqlRequest;

class ChatSQL
{
    public static function checkConnection($who, $whom, $conn) {
        $who2 = $whom;
        $whom2 = $who;
        $sql_request = "SELECT * FROM connections WHERE (one = :who AND two = :whom) OR (one = :who2 AND two = :whom2) LIMIT 1";
        $arguments = array($who, $whom, $who2, $whom2);
        if (SqlRequest::readFromDb($sql_request, $arguments, $conn)) {
            return true;
        }
        return false;
    }

    public static function sendMessageToDB($who, $whom, $what, $add_time, $conn) {
        $sql_request = "INSERT INTO messages (who, whom, what, add_time) VALUES (?, ?, ?, ?)";
        $arguments = array($who, $whom, $what, $add_time);
        SqlRequest::create_update_delete($sql_request, $arguments, $conn);
    }

    public static function getUserName($id, $conn) {
        $sql_request = "SELECT firstname, lastname FROM users WHERE id = :id LIMIT 1";
        $arguments = array($id);
        return (SqlRequest::readFromDb($sql_request, $arguments, $conn));
    }

    public static function updateViews($who, $whom, $add_time, $conn) {
        // get add_time from DB
        $sql_request = "SELECT add_time from views WHERE who = :who AND whom = :whom ORDER BY add_time DESC LIMIT 1";
        $arguments = array("who" => $who, "whom" => $whom);
        $last_time = SqlRequest::readFromDb($sql_request, $arguments, $conn)[0]['add_time'];
        var_dump($last_time);
        if (!isset($last_time) || ($add_time - $last_time) > 150) {                           // check this if
            $sql_request = "INSERT INTO views (who, whom, add_time) VALUES (?, ?, ?)";
            $arguments = array($who, $whom, $add_time);
            SqlRequest::create_update_delete($sql_request, $arguments, $conn);
            return 1;
        }
        return 0;
    }

    public static function sendNotification($user_id, $text, $status, $add_time, $from, $conn) {
        $sql_request = "INSERT INTO notifications (user_id, text, status, add_time, sid) VALUES (?, ?, ?, ?, ?)";
        $arguments = array($user_id, $text, $status, $add_time, $from);
        SqlRequest::create_update_delete($sql_request, $arguments, $conn);
    }

    public static function updateThisUserOnline($who, $time_of_last_visit, $is_online, $conn) {
        $sql_request = "UPDATE users SET time_of_last_visit = :time_of_last_visit, is_online = :is_online WHERE id = :id";
        $arguments = array('time_of_last_visit' => $time_of_last_visit, 'is_online' => $is_online, 'id' => $who);
        SqlRequest::create_update_delete($sql_request, $arguments, $conn);
    }

    public static function getMsgList($id, $conn) {
        $sql_request = "SELECT 
                          *, 
                          (SELECT CONCAT(firstname, ' ', lastname) FROM users WHERE users.id = messages.who) as who_name,
                          DATE_FORMAT(FROM_UNIXTIME(add_time), '%h:%i:%s - %e %b %Y') AS 'date'
                        FROM 
                          messages
                        WHERE (
                          who = ? OR whom = ?) AND (who = ? OR whom = ?) 
                        ORDER BY add_time ASC;";
        $arguments = array($_SESSION['user'], $_SESSION['user'], $id, $id);
        return (SqlRequest::readFromDb($sql_request, $arguments, $conn));
    }
}
