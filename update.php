<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

file_put_contents('logger.txt', date('l, F jS Y - g:i:s A')."\n", FILE_APPEND);
file_put_contents('logger.txt', 'POST: '.print_r($_POST, true).'GET: '.print_r($_GET, true), FILE_APPEND);
file_put_contents('logger.txt', 'BODY: '.print_r(json_decode(file_get_contents('php://input')), true), FILE_APPEND);
file_put_contents('logger.txt', "\n", FILE_APPEND);

$data = json_decode(file_get_contents('php://input'));

$db = new mysqli('localhost', 'ben', 'digger078', 'ideascape');

if ($db->connect_errno > 0) {
    die('Unable to connect to database [' . $db->connect_error . ']');
}



if (array_key_exists("query", $data) && isset($data->query->url)) {
    $google = '/google\.com/';
    $isGoogle = preg_match($google, $data->query->url);
    if ($isGoogle === false) {
        die('An error occured.');
    }
    if (!$isGoogle) {
        $url = strstr($data->query->url, "#", true);
	if ($url === false) {
		$url = $data->query->url;
	}
    } else {
        $searchTermsLocation = strrpos($data->query->url, "q=") + 2;
	$searchEndLocation = strpos($data->query->url, "&", $searchTermsLocation);
	if ($searchEndLocation === false) {
		$searchEndLocation = strlen($data->query->url);
	}
	if ($searchTermsLocation != 2) {
		$url = 'https://www.google.com/#q='.substr($data->query->url, $searchTermsLocation, $searchEndLocation - $searchTermsLocation);
	} else {
		$url = 'https://www.google.com/';
	}
//	$url = $data->query->url;
    }
    //$url = strstr($data->query->url, "#", true);
    $pid = 0;
    $id = uniqid("", true);
    if ($data->query->pid != 0 && false) {
        $pid = $data->query->pid;
        $query = "SELECT id FROM nodes WHERE session = '".$data->query->session."' AND url = '".$url."' AND pid = '"
            .$data->query->pid."'";
        if(!$result = $db->query($query)){
            die('There was an error running the query for finding similar ID with same PID [' . $db->error . ']');
        }
        if ($result->num_rows == 0) {
            $query = "INSERT INTO nodes (id, pid, url, session, token, time)"
                ." VALUES ('"
                    ."{$id}"
                    ."', '{$pid}"
                    ."', '{$url}"
                    ."', '{$data->query->session}"
                    ."', '{$data->query->token}"
                    ."', 0)";
            if(!$result = $db->query($query)){
                die('There was an error running the query adding a new entry [' . $db->error . ']');
            }
        } else {
            $id = $result->fetch_assoc()["id"];
        }
    } else {
        $query = "SELECT id, pid FROM nodes WHERE session = '".$data->query->session."' AND url = '".$url."'";
        if(!$result = $db->query($query)){
            die('There was an error running the query retrieving nodes with similar URL\'s [' . $db->error . ']');
        }
        if ($result->num_rows == 0) {
		if ($data->query->pid != 0 && $result->num_rows == 0) {
			$pid = $data->query->pid;
		}
            $query = "INSERT INTO nodes (id, pid, url, session, token, time)"
                ." VALUES ('"
                    ."{$id}"
                    ."', '{$pid}"
                    ."', '{$url}"
                    ."', '{$data->query->session}"
                    ."', '{$data->query->token}"
                    ."', 0)";
            if(!$result = $db->query($query)){
                die('There was an error running the query adding a new entry [' . $db->error . ']');
            }
        } else {
            $line = $result->fetch_assoc();
            $pid = $line["pid"];
            $id = $line["id"];
        }
    }
    $returnQ = json_encode(array('query'=>array('id'=>$id,
                                            'pid'=>$pid,
                                            'req'=>$data->query->req,
                                            'session'=>$data->query->session,
                                            'token'=>$data->query->token)));
    echo $returnQ;
}
if (array_key_exists("viewTime", $data)) {
    foreach ($data->viewTime as $currentUpdate) {
//die(print_r($currentUpdate,true));
        $query = "UPDATE nodes SET time = $currentUpdate->elapsedTime, token = \"$currentUpdate->token\" "
                    ."WHERE time < $currentUpdate->elapsedTime "
                    ."AND id = \"$currentUpdate->id\"";
        if(!$result = $db->query($query)){
            die('There was an error running the query updating elapsed times [' . $db->error . ']');
        }
    }




     $returnV = json_encode(array('viewTime'=>array('status'=>"OK")));
	if (!isset($returnQ)) {
     		echo $returnV;
	}



}

?>
