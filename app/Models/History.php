<?php

namespace App\Models;

use App\Config\db;
use App\Sql\SqlRequest;

class History
{
    public static function getHistory($conn)
    {
        $sql_request = "SELECT ONE AS
                            who,
                            two AS whom,
                            add_time,
                            (1) AS tb
                        FROM
                            connections
                        WHERE
                            connections.one = ?
                        UNION
                        SELECT
                            who,
                            whom,
                            add_time,
                            (2) AS tb
                        FROM
                            likes
                        WHERE
                            likes.who = ?
                        ORDER BY
                            add_time;";

        $arguments = array( $_SESSION['user'], $_SESSION['user'] );
        $data = SqlRequest::readFromDb($sql_request, $arguments, $conn);
        return $data;
    }
}