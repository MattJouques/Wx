<?php
// ###############################################
// #
// # 				Weather Alerts  
// #
// ###############################################

global $log, $errMsgs, $config, $station, $con, $wx;
require 'Config.php';														// Get the Config File
loadConfig();
// Get the Alert Details
$query = "SELECT * FROM $config[latestTable] WHERE field LIKE '%alert%'";
if($result = $con->query($query)){
	while ($row = $result->fetch_assoc()) {
		$wx[$row['field']] = $row['value'];
	}
$result->free();
}

// Page content
	
echo "<div id='alertIcon'>";
echo "<img src='$config[siteIconPath]Alert_$wx[alertStatus].png'>";
echo "</div>";
echo "<div id='alertDetail'>";
if (empty($wx[alertNumber])) {
	echo "There are no weather alerts currently in force";
} else {
	echo "There are $wx[alertNumber] $wx[alertLevel] alerts in force at this time";
	echo "<h4>$wx[alertTitle]</h4>$wx[alertDetail]";
	echo "<em>$wx[alertAttribution]</em>";
}
echo "</div>";
// Output
?>
