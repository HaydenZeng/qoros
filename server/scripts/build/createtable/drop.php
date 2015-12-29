<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

list($host, $user, $password, $dbname, $dbprefix) = array_slice($argv, 1, 5);

try {
    $dbh = new PDO("mysql:host=$host", $user, $password);
    $dbh->exec("DROP DATABASE IF EXISTS {$dbprefix}{$dbname}") or die(print_r($dbh->errorInfo(), true));
    
} catch (PDOException $e) {
    die("DB ERROR: ". $e->getMessage());
}