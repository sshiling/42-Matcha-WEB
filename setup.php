<?php
session_start();

$DB_USER = "root";
$DB_PASSWORD = "123456";
$DB_NAME = "matcha";
$DB_DSN = "mysql:host=localhost;dbname=matcha";
$DB_DSN_SHORT = "mysql:host=localhost";

// CREATE DATABASE
try {
    $conn = new PDO($DB_DSN_SHORT, $DB_USER, $DB_PASSWORD);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "CREATE DATABASE IF NOT EXISTS `" . $DB_NAME . "`";
    // use exec() because no results are returned
    $conn->exec($sql);
    echo "Database 'matcha' created successfully<br>";
}
catch(PDOException $e)
{
    echo $sql . "<br>" . $e->getMessage();
}

$conn = null;

// CREATE USERS TABLE
try {
    $conn = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Temporary variable, used to store current query
    $templine = '';

    // Read in entire file
    $lines = file('matcha.sql');

    // Loop through each line
    foreach ($lines as $line)
    {

        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;


        // Add this line to the current segment
        $templine .= $line;

        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';')
        {
            $conn->exec($templine);
    //        echo "Tables imported successfully";

            // Reset temp variable to empty
            $templine = '';
        }
    }

}
catch(PDOException $e){
    echo $sql . "<br>" . $e->getMessage();
}

$conn = null;

$f = fopen(".htaccess", "a+");
fwrite($f, "<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>");
fclose($f);

echo "<script>setTimeout(function (){ window.location.replace('/') }, 2000);</script>";

session_unset();
