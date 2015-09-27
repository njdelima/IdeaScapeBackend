<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

file_put_contents('logger.txt', date('l, F jS Y - g:i:s A')."\n", FILE_APPEND);
file_put_contents('logger.txt', 'POST: '.print_r($_POST, true).'GET: '.print_r($_GET, true), FILE_APPEND);
file_put_contents('logger.txt', 'BODY: '.print_r(json_decode(file_get_contents('php://input')), true), FILE_APPEND);
file_put_contents('logger.txt', "\n", FILE_APPEND);

$post = json_decode(file_get_contents('php://input'));

$db = new mysqli('localhost', 'ben', 'digger078', 'ideascape');

$session = uniqid("", true);
$token = uniqid("", true);

if ($db->connect_errno > 0) {
	die('Unable to connect to database [' . $db->connect_error . ']');
}
if (!isset($post->mode)) {
	die('Mode not specified.');
} 
if (strcmp($post->mode, "create") == 0) {
	do {
		$session = uniqid("", true);
		$query = "SELECT * FROM nodes WHERE session = '$session'";
		if(!$result = $db->query($query)){
			die('There was an error running the query [' . $db->error . ']');
		}
	} while ($result->num_rows != 0);
	$token = uniqid("", true);
} elseif (strcmp($post->mode, "join") == 0) {
	if (!isset($post->session)) {
		die('Session to join not specified.');
	}
	$session = $post->session;
	$query = "SELECT * FROM nodes WHERE session = '$session'";
	if(!$result = $db->query($query)){
		die('There was an error running the query [' . $db->error . ']');
	}
	if ($result->num_rows == 0) {
		die('Session does not exist.');
	}
	do {
		$token = uniqid("", true);
		$query = "SELECT * FROM nodes WHERE session = '$session' AND token = '$token'";
		if(!$result = $db->query($query)){
			die('There was an error running the query [' . $db->error . ']');
		}
	} while ($result->num_rows != 0);
}
echo json_encode(array('session'=>$session, 'token'=>$token));


?>
