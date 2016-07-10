<?php
// ###############################################
// #
// # 				Chart Output  
// #
// ###############################################
//
global $config, $con;																	
require '../Config.php';																	// Get the Config File
loadConfig();																				// Load the site configuration
$db_table = $config[tablePrefix] . "History";



//
// GET
//
if($_GET) {
	// TODO - Handle GET elelemts
} else {
	// Columns
	$output .= "{\n \"cols\": [\n";
	$output .= "{\"id\":\"\",\"label\":\"Date\",\"pattern\":\"\",\"type\":\"string\"},\n";
	$output .= "{\"id\":\"\",\"label\":\"Time\",\"pattern\":\"\",\"type\":\"string\"},\n";
    $output .= "{\"id\":\"\",\"label\":\"Slices\",\"pattern\":\"\",\"type\":\"number\"}\n";
    $output .= "],\n";
	// Rows
	$output .= "\"rows\": [\n";
	$output .= "{\"c\":[{\"v\":\"02 feb, 2008\",\"f\":null},{\"v\":\"17:01\",\"f\":null}]},{\"v\":3,\"f\":null}]},";
	$output .= "{\"c\":[{\"v\":\"02 feb, 2008\",\"f\":null},{\"v\":\"17:05\",\"f\":null}]},{\"v\":3,\"f\":null}]},";
	$output .= "{\"c\":[{\"v\":\"02 feb, 2008\",\"f\":null},{\"v\":\"17:10\",\"f\":null}]},{\"v\":3,\"f\":null}]},";
	$output .= "{\"c\":[{\"v\":\"02 feb, 2008\",\"f\":null},{\"v\":\"17:15\",\"f\":null}]},{\"v\":3,\"f\":null}]},";
	$output .= "{\"c\":[{\"v\":\"02 feb, 2008\",\"f\":null},{\"v\":\"17:20\",\"f\":null}]},{\"v\":3,\"f\":null}]},";
	$output .= "{\"c\":[{\"v\":\"02 feb, 2008\",\"f\":null},{\"v\":\"17:25\",\"f\":null}]},{\"v\":3,\"f\":null}]}";
    $output .= "]\n";
	$output .= "}";
	
	echo $output;
}





function oldstuff() {
$table = array();
$table[cols] = array();
$table[rows] = array();
// Get Column Names
$db_Fields = getDbFields($db_table);
$numColumns = count($db_Fields);
$count = 0;
// Populate Columns
foreach ($db_Fields as $field => $name) {
	if ($field == "dateutc") {
		//array_push($table[cols], array('label' => 'date', 'type' => 'date'));
		array_push($table[cols], array('label' => 'time', 'type' => 'datetime'));
	} else if ($field == "id") {
		continue;
	} else {
		array_push($table[cols], array('label' => $field, 'type' => 'number'));
	}
}
// Get Data
$rows = array();
$data = array();
//$countfields=1;
//$countrows=1;
$query = "SELECT * FROM " . $db_table . " LIMIT 10";
if ($result = $con->query($query)) {	
	while ($row = $result->fetch_assoc()) {	
		foreach ($row as $field => $value) {
			if ($field == "id") {
				continue;
			} else if ($field == 'dateutc') {
				//$dataDate = strtotime(trim(substr($value, 0, 11)));
				//$date = date('Y-m-d', $dataDate);
				//$dataTime = strtotime(trim(substr($value, -8, 8)));
				//$time = date('H:i:s', $dataTime);
				//$data[] = array('date' => $date);
				$data[] = array($field => $value);
			} else {
				$data[] = array($field => (float)$value);
			}
			//echo "Completed field number $countfields for $field<br/>";
			$countfields++;
		}	
		$rows[] = array('c' => $data);
		$data = array();
		//echo "Completed row number $countrows<br/>";
		$countrows++;
	}
}
$table[rows] = $rows;


// encode the table as JSON
$jsonTable = json_encode($table);

// return the JSON data
echo $jsonTable;
}
?>