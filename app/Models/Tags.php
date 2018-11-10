<?php

namespace App\Models;

use App\Config\db;
use App\Sql\SqlRequest;

class Tags
{
    public static function getTagsList($conn) {
        $sql_request = "SELECT tag FROM tags WHERE 1 ORDER BY tag ASC;";
        $arguments = array();

        if (!($data = SqlRequest::readFromDb($sql_request, $arguments, $conn)))
            return array();

        $res = array();
        foreach ($data as $tag)
            $res[] = $tag['tag'];

        return ($res);
    }

    public static function checkTags($data, $conn) {
        $list = Tags::getTagsList($conn);
        $new_tag = array_diff($data, $list);

        foreach ($new_tag as $tag) {
            if (strlen($tag) > 0)
                Tags::addNewTag($tag, $conn);
        }

        return $new_tag;
    }

    public static function addNewTag($data, $conn) {
        $sql_request = "INSERT INTO `tags`(`tag`) VALUES (?);";
        $arguments = array( $data );
        return SqlRequest::create_update_delete($sql_request, $arguments, $conn);
    }

}
