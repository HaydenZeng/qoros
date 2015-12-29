<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

list($host, $user, $password, $dbname, $dbprefix) = array_slice($argv, 1, 5);

try {
    $dbh = new PDO("mysql:host=$host", $user, $password);
    $dbh->exec("CREATE DATABASE IF NOT EXISTS {$dbprefix}{$dbname}
    default character set utf8
    default collate utf8_general_ci") or die(print_r($dbh->errorInfo(), true));

} catch (PDOException $e) {
    die("DB ERROR: ". $e->getMessage());
}