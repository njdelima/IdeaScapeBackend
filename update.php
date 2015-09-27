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



if (array_key_exists("query", $data)) {
    $google = '/google\.com/';
    $isGoogle = preg_match($google, $data->query->url);
    if ($isGoogle === false) {
        die('An error occured.');
    }
    if (!$isGoogle) {
        $url = strstr($data->query->url, "#", true);
    } else {
        $searchTermsLocation = strpos($data->query->url, "q=") + 2;
        $url = 'https://www.google.com/#q='.substr($data->query->url, $searchTermsLocation);
    }
    $url = strstr($data->query->url, "#", true);
    $pid = 0;
    $id = uniqid("", true);
    if ($data->query->pid > 0) {
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
                    ."', '{$data->query->url}"
                    ."', '{$data->query->session}"
                    ."', '{$data->query->token}"
                    ."', 0)";
            if(!$result = $db->query($query)){
		die($query."1");
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
            $query = "INSERT INTO nodes (id, pid, url, session, token, time)"
                ." VALUES ('"
                    ."{$id}"
                    ."', '{$pid}"
                    ."', '{$data->query->url}"
                    ."', '{$data->query->session}"
                    ."', '{$data->query->token}"
                    ."', 0)";
            if(!$result = $db->query($query)){
		die($query."2");
                die('There was an error running the query adding a new entry [' . $db->error . ']');
            }
        } else {
            $pid = $result->fetch_assoc()["pid"];
            $id = $result->fetch_assoc()["id"];
        }
    }
    $returnQ = json_encode(array('query'=>array('id'=>$id,
                                            'pid'=>$pid,
                                            'req'=>$data->query->req,
                                            'session'=>$data->query->session,
                                            'token'=>$data->query->token)));
    echo $returnQ;
} elseif (array_key_exists("viewTime", $data)) {
    foreach ($data as $currentUpdate) {
        $query = "UPDATE nodes SET time = $currentUpdate->elapsedTime token = $currentUpdate->token "
                    ."WHERE time < $currentUpdate->elapsedTime "
                    ."AND id = $currentUpdate->id";
        if(!$result = $db->query($query)){
            die('There was an error running the query updating elapsed times [' . $db->error . ']');
        }
    }





    // $return1 = json_encode((object)array('query'=>array('id'=>$id,
    //                                                 'pid'=>$pid,
    //                                                 'req'=>$data->query->req,
    //                                                 'session'=>$data->query->session,
    //                                                 'token'=>$data->query->token)));
    // echo $return;




}

?>
