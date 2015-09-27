<html>
<head><h1>V1</h1></head>
<body>
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
				$count = $count + 1;
				if ($count >= 3) {
					break;
				}
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
	echo $json;
	echo "<body><script> var nodes = JSON.parse(" . $json . ");</script></body>";
?>
<svg width = "720", height = "1080">

</svg>

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.6/d3.min.js" charset="utf-8"></script>


<script>

var nodesPropertiesArr = Object.getOwnPropertyNames(nodes);
console.log("REACHED");
console.log(nodesPropertiesArr);
var numRootNodes = nodesPropertiesArr.length;
console.log(numRootNodes);
var dataIn = [[]];
console.log(nodesPropertiesArr[0]);
console.log(nodes.Cookware);
for (p = 0; p < numRootNodes; p++) {
    console.log(nodesPropertiesArr[p]);
    dataIn[p] = nodes[nodesPropertiesArr[p]];
}

</script>

<script>
for(e = 0; e < numRootNodes; e ++)  {
var maxRadius = 540 /((2 * dataIn[e].length) + 1);
}
var depthCounts = [[]];
var maxDepth = [];
for(y = 0; y < numRootNodes; y++) {
    maxDepth[y] = 0;
    for (l = 0; l < dataIn.length; l++) {
        if (dataIn[l].depth > maxDepth) {
            maxDepth[y] = dataIn[l].depth;
        }
    }
}
</script>

<script>

for(t = 0; t < numRootNodes; t++){
    for(o = 1; o <= maxDepth; o ++) {
        depthCounts[o] = 0;
        for(u = 0; u < dataIn[t].length; u ++) {
            if (dataIn[t][u].depth == o) {
                depthCounts[o] ++;
            }
        }
    }
}
</script>

<script>

var svg = d3.select("svg")
            .attr("width", 1080 * numRootNodes);
var circle = svg.selectAll("circle")
            .data(dataIn)
            .enter()
            .append("circle")
            .attr("cy", function(d) {return  - d[i].depth * 2 * maxRadius * sin((i * 6.14) / (depthCounts[d[i].depth])) + 540 } )
            .attr("cx", function(d) {return  d[i].depth * 2 * maxRadius * cos((i * 6.14) / (depthCounts[d[i].depth])) + ((i + 1) * 540) } )
            .attr("r", 0);
circle.transition().attr("r", function(d) {return  maxRadius * (1 - Math.exp( - Number(d[i].time) /50))} );
</script>

<script>

var lines = [];
var linesIndex = 0;
for(n = 0; n < numRootNodes; n++){
    for (s = 0; s < dataIn[n].length; s++) {
        for (a = 0; a < dataIn[n].length; a++) {
            if (dataIn[n][s].cid == dataIn[n][a].pid) {
                lines[linesIndex] = {x1: dataIn[n][s].depth * 2 * maxRadius * cos((s * 6.14) / (depthCounts[dataIn[n][s].depth])) + ((i + 1) * 540), y1: - d[n][s].depth * 2 * maxRadius * sin((s * 6.14) / (depthCounts[d[n][s].depth])) + 540 , x2:  dataIn[n][a].depth * 2 * maxRadius * cos((a * 6.14) / (depthCounts[dataIn[n][a].depth])) + ((a + 1) * 540), y2:- d[n][a].depth * 2 * maxRadius * sin((a * 6.14) / (depthCounts[d[n][a].depth])) + 540 };
                linesIndex ++;
            }
        }
    }
}


var line = svg.selectAll("line")
            .data(lines)
            .enter()
            .append("line")
            .attr("x1", function(d) {return d[i].x1})
            .attr("y1", function(d) {return d[i].y1})
            .attr("x2", function(d) {return d[i].x2})
            .attr("y2", function(d) {return d[i].y2})
            .style("stroke-width", 2)
            .style("stroke", "black");

var text = svg.selectAll("text")
            .data(dataIn)
            .enter()
            .append("text")
            .attr("x", function(d) {return d[i].depth * 2 * maxRadius * cos((i * 6.14) / (depthCounts[d[i].depth])) + ((i + 1) * 540)})
            .attr("y", function(d) {return (- d[i].depth * 2 * maxRadius * sin((i * 6.14) / (depthCounts[d.depth])) + 540) + 12 + (maxRadius * (1 - Math.exp( - Number(d.time) /50)))})
            .attr("font-size", 12)
            .attr("dx", function(d) {return  - (d.url.length * 2)})
            .text(function(d) {return d[i].url});

</script>
</body>
</html>
