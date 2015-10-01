<?php
        class Node {
		//properties
		public $id;
		public $pid;
		public $url;
		public $time;
		public $token;
		public $color;
		public $depth;

		function __construct($id, $pid, $url, $time, $token, $depth) {
			$this -> id = $id;
			$this -> pid = $pid;
			$this -> url = $url;
			$this -> time = $time;
			$this -> token = $token;
			$this -> depth = $depth;
			$this -> color = $this->stringToColorCode($this->token);
		}
		private function stringToColorCode($str) {
			$code = dechex(crc32($str));
			$code = substr($code, 0, 6);
			return $code;
		}

	}

	require_once 'login.php';
	//echo "CHECKPOINT 1";
	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

	if ($connection -> connect_error) die ($connection -> connect_error);

	if (isset($_GET['session'])) {
		$session = $connection -> real_escape_string($_GET['session']);
	//	echo "session = " . $session;
	}

	$main_array = array();

	$query = "SELECT * FROM nodes WHERE session='$session'";
	$result = $connection->query($query);

	if (!$result) {
		die("FAILED: $query<br />" . $connection -> error . "<br /><br />");
	}

	$rows = $result->num_rows;
	//echo "\n\n session id rows=" . $rows;
	if ($rows === 0) {
		die("Sorry! No such session ID exists.");
	}

	$query = "SELECT * FROM nodes WHERE session='$session' AND pid='0'";
	$result = $connection->query($query);

	if (!$result) {
		die("FAILED: $query<br />" . $connection -> error . "<br /><br />");
	}

	$rows = $result->num_rows;
	//echo "\n\n pid 0 rows=" . $rows;

	for ($j = 0; $j < $rows; $j++) {
		$result -> data_seek($j);
		$row = $result->fetch_array(MYSQLI_ASSOC);

		$massive_string = "";
		$nodes = array();

		$id = $row['id'];
		$pid = $row['pid'];
		$url = $row['url'];
		$time = $row['time'];
		$token = $row['token'];

		//echo "\n\nABOUT TO START TRAVERSAL\n\n";
		traverse($id, $pid, $url, $time, $token, 1);
		//echo "\n\nFINISHED TRAVERSE\n\n";
		//echo "massive string = " . $massive_string;

        	$ch = curl_init("http://localhost:2003/");
        	curl_setopt( $ch, CURLOPT_POST, 1);
        	curl_setopt( $ch, CURLOPT_POSTFIELDS, 'key1=' . $massive_string);
        	curl_setopt( $ch, CURLOPT_PORT, 2003);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
       		curl_setopt( $ch, CURLOPT_HEADER, 0);
        	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        	$response = curl_exec( $ch );

		$jsonIterator = new RecursiveIteratorIterator(
					new RecursiveArrayIterator(json_decode($response, TRUE)),
					RecursiveIteratorIterator::SELF_FIRST);
		$mainKey = "";
		$count = 0;
		foreach ($jsonIterator as $key => $val) {
	    		if ($key === "label") {
				$mainKey = $mainKey .  $val . ", ";
				//$count = $count + 1;
				//if ($count >= 3) {
				//	break;
				//}
			}
		}
		$mainKey = substr($mainKey, 0, -2);
		$main_array[$mainKey] = $nodes;

//		echo $response;
	}

	function traverse($currentID, $currentPID, $currentURL, $currentTime, $currentToken, $currentDepth) {
		global $connection, $nodes;
		$currentNode = new Node($currentID, $currentPID, $currentURL, $currentTime, $currentToken, $currentDepth);
		array_push($nodes, $currentNode);

		if (stripos($currentURL, "google") == false) {

			$temp = file_get_contents($currentURL);
			//echo "\n\ncurrentId = " . $currentID;
			//echo "\n\ncurrentURL = " . $currentURL;
			//echo "\n\ntemp = " . $temp;
			while ($temp !== false) {
				$temp = getNextParagraph($temp);
				//echo "\n\ntemp = " . $temp;
			}
		}

		$query = "SELECT * FROM nodes WHERE pid='$currentID'";
		$result = $connection -> query($query);

		if (!$result) {
			die("FAILED: $query<br />" . $connection->error . "<br><br>");
		}

		$rows = $result->num_rows;

		for ($j = 0; $j < $rows; $j++) {
			$result -> data_seek($j);
			$row = $result->fetch_array(MYSQLI_ASSOC);

			$id = $row['id'];
			$pid = $row['pid'];
			$url = $row['url'];
			$time = $row['time'];
			$token = $row['token'];

			traverse($id, $pid, $url, $time, $token, $currentDepth+1);
		}
	}

	function getNextParagraph($temp) {
		global $massive_string;
		//echo "<br><br>Get next para temp = " . $temp . "<br><br>";
		$pos1 = stripos($temp, "<p>");

		if ($pos1 == false) {
			return false;
		} else {
			$pos2 = stripos($temp, "</p>");
			$length = $pos2 - $pos1;
			$massive_string = $massive_string . strip_tags(substr($temp, $pos1 + 3, $length)) . "\n\n";
			$temp = substr($temp, $pos2 + 3);
			return $temp;
		}
	}

	$json = json_encode($main_array, JSON_UNESCAPED_SLASHES);
	//echo $json;
	echo "<body><script> var nodes = " . $json . ";</script></body>";
?>


<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["orgchart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('string', 'Manager');
        data.addColumn('string', 'ToolTip');

        data.addRows([
          [{v:'Mike', f:'Mike<div style="color:red; font-style:italic">President</div>'}, '', 'The President'],
          [{v:'Jim', f:'Jim<div style="color:red; font-style:italic">Vice President</div>'}, 'Mike', 'VP'],
          ['Alice', 'Mike', ''],
          ['Bob', 'Jim', 'Bob Sponge'],
          ['Carol', 'Bob', '']
        ]);

        var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
        chart.draw(data, {allowHtml:true});
      }
   </script>

