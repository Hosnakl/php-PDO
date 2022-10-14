<?php
function connect(){
    $dbConnection = parse_ini_file("Lab5.ini");
    extract($dbConnection);
    try {
        return new PDO($dsn, $username, $password);
    } catch (PDOException $e) {
        die ("Connection failed: " . $e->getMessage());
    }
    //return null;
}
?>