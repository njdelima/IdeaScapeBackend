<?php
        require_once 'login.php';

	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

	if ($connection -> connect_error) die ($connection -> connect_error)

	if (isset($_GET['session'])) {
		$session = $connection -> real_escape_string($_POST['session']);
	}

	$query = "SELECT * FROM nodes WHERE session

	echo "HI";
	$string = file_get_contents("https://en.wikipedia.org/wiki/Cold_War");
	$meaningful_string = "";

	$flag = true;

	while ($flag === true) {
		$flag = getNextParagraph();
	}

	function getNextParagraph() {
		global $string, $meaningful_string;

		$pos1 = stripos($string, "<p>");

		if ($pos1 === false) {
			return false;
		} else {
			$pos2 = stripos($string, "</p>");
			$length = $pos2 - $pos1;
			$meaningful_string = $meaningful_string . strip_tags(substr($string, $pos1 + 3, $length)) . "\n\n";
			$string = substr($string, $pos2 + 3);
			return true;
		}
	}

	$url = "http://localhost:2003/";

        $myvars = 'key1=' . $meaningful_string;

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
