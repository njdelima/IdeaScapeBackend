<?php

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
    $url = strstr($data["query"]->url, "#", true);
    $query = "";
    $id = uniqid("", true);
    if ($data["query"]->pid > 0) {
        $query = "SELECT id FROM nodes WHERE session = '".$data["query"]->session."' AND url = '".$url."' AND pid = '"
            .$data["query"]->pid."'";
        if(!$result = $db->query($query)){
            die('There was an error running the query for finding similar ID with same PID [' . $db->error . ']');
        }
        if ($result->num_rows == 0) {
            $query = "INSERT INTO nodes (id, pid, url, session, token, time)"
                ." VALUES ('"
                    ."{$id}"
                    ."', '{$data["query"]->pid}"
                    ."', '{$data["query"]->url}"
                    ."', '{$data["query"]->session}"
                    ."', '{$data["query"]->token}"
                    ."', 0";
            if(!$result = $db->query($query)){
                die('There was an error running the query adding a new entry [' . $db->error . ']');
            }
        } else {
            $id = $result->fetch_assoc()["id"];
        }
    } else {
        $query = "SELECT id, pid FROM nodes WHERE session = '".$data["query"]->session."' AND url = '".$url."'";
        if(!$result = $db->query($query)){
            die('There was an error running the query retrieving nodes with similar URL\'s [' . $db->error . ']');
        }
    }




}

?>
