<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once "db.php";
if (!(isset($_GET["code"]) && !empty(trim($_GET["code"])))) {
	header("location: ./");
	exit;
} else {
	global $db;
	$code = $_GET["code"];
	$sql = "UPDATE data SET visits = visits + 1 WHERE code = ?;";
	$stmt = $db->prepare($sql);
	$stmt->bind_param("s", $code);
	$stmt->execute();
	$sql = "SELECT url FROM data WHERE code = ?;";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("s", $code);
		if ($stmt->execute()) {
			$stmt->store_result();
			$stmt->bind_result($url);
			if ($stmt->num_rows < 1) {
				header("location: ./");
				exit;
			}
			while ($stmt->fetch()) {
				header("location: $url");
				exit;
			}
		} else {
			header("location: ./");
			exit;
		}
	} else {
		header("location: ./");
		exit;
	}
}
?>