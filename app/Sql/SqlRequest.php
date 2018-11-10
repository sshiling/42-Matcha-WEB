<?php

namespace App\Sql;

use App\Config\db;

class SqlRequest
{
    public static function create_update_delete($request, $arguments, $conn) {
        $stm = $conn->prepare($request);

        $stm->execute($arguments);
        if (!($result = $stm->rowCount())) {

//            $conn = null;
            return false;
        } else {
//            $conn = null;
            return true;
        }
    }

    public static function readFromDb($request, $arguments, $conn) {
        $stm = $conn->prepare($request);
        $stm->execute($arguments);
        if (empty($result = $stm->fetchAll())) {
//            $conn = null;
            return null;
        } else {
//            $conn = null;
            return $result;
        }
    }

//    public function create($request, $arguments) {
//
//    }
//
//    public function read($request, $arguments) {
//
//    }
//
//    public function update($request, $arguments) {
//
//    }
//
//    public function delete($request, $arguments) {
//
//    }
}