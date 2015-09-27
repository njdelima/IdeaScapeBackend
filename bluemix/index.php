<head><meta charset="utf-8" /></head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<?php
        echo "HI";
	$string = file_get_contents("https://en.wikipedia.org/wiki/UNIX");
        $url = "http://localhost:2003/";

        $myvars = 'key1=' . $string;

        $ch = curl_init($url);
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_PORT, 2003);
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );

	$file = fopen("file.txt", "w") or die("Unable to open file!");
	fwrite($file, $response);
	fclose($file);
?>
