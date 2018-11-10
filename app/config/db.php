<?php

namespace App\Config;

use \Slim\PDO\database as connect;

class DB {
    private $DB_DSN = 'mysql:host=localhost;dbname=matcha;';
//    private $DB_SOC = 'unix_socket=/Users/sshiling/MAMP/mysql/tmp/mysql.sock';
    private $DB_SOC = 'unix_socket=/Users/oivasenk/MAMP/mysql/tmp/mysql.sock';
//    private $DB_SOC = 'socket=/tmp/mysql.sock';

    private $DB_USER = 'root';
    private $DB_PASSWORD = '123456';

    public function connect()
    {
        try {
            $conn = new connect($this->DB_DSN.$this->DB_SOC, $this->DB_USER, $this->DB_PASSWORD);
            $conn->setAttribute(connect::ATTR_ERRMODE, connect::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        return null;
    }
}
