<?php

namespace App\Models;

use App\Config\db;
use App\Sql\SqlRequest;

class Notif
{
    public static function getNotifList($conn) {
        $sql_request = "SELECT * FROM notifications WHERE user_id = ? ORDER BY add_time DESC;";
        $arguments = array($_SESSION['user']);

        $data = SqlRequest::readFromDb($sql_request, $arguments, $conn);

        if ($data)
            Notif::makeRead($conn);

        return $data;
    }

    public static function makeRead($conn) {
        $sql_request = "UPDATE `notifications` SET status = 1 WHERE user_id = ?;";
        $arguments = array( $_SESSION['user'] );
        return SqlRequest::create_update_delete($sql_request, $arguments, $conn);
    }

    public static function getNew($conn) {
        $sql_request = "SELECT COUNT(id) as num FROM notifications WHERE user_id = ? AND status = 0";
        $arguments = array($_SESSION['user']);
        $data = SqlRequest::readFromDb($sql_request, $arguments, $conn)[0];
        return $data ? $data['num'] : -1;
    }

}
