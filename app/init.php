<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
mb_internal_encoding("UTF-8");

session_start();

try {
	$db = new PDO('mysql:host=localhost;dbname=wc_invoices;charset=utf8', 'root', 'root');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
	$error_message = $e->getMessage();
	die("An error occured. ERR: ".$error_message);
}

// $STH = $DB->prepare("SELECT * FROM users WHERE user = :s");
// $STH->execute(array(25));
// $User = $STH->fetch();

// $sth = $dbh->prepare('SELECT name, colour, calories FROM fruit WHERE calories < :calories AND colour = :colour');
// $sth->bindParam(':calories', $calories);
// $sth->bindParam(':colour', $colour);
// $sth->execute();

//$results = $sth->fetchAll(PDO::FETCH_ASSOC);