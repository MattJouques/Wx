<?php 
// ###############################################
// #
// # 				Weather Submit  
// #
// ###############################################
// #
// #
global $log, $errMsgs, $config, $station, $con, $wx;
require_once('Config.php');																// Get the Config File
loadConfig();																			// Load Config
require 'functions/maths.php';															// Get the maths File																	
//
// Check Auth
if($_GET["key"]) {																		// TODO - Build a config for mapping feed tags
	$key = htmlspecialchars($_GET["key"]);
	if($key != $config[tablePrefix] . $config[authKey]) {
		die('Fail - Authorisation');
	} else {
		// Date
		if ($_GET["dateutc"]) {
			$submit[datetimeUTC] = htmlspecialchars($_GET["dateutc"]);
			$submit[dateUTC] = $_SESSION[dateToday];
			$submit[timeUTC] = $_SESSION[timeNow];
		} else {
			$submit[datetimeUTC] = $_SESSION[datetimeNow];
			$submit[dateUTC] = $_SESSION[dateToday];
			$submit[timeUTC] = $_SESSION[timeNow];
		}
		// Temperature
		if ($_GET["tempf"]) {
			$submit[temp_c] = convert($station[stnBaseUnit_temp], htmlspecialchars($_GET["tempf"]));
		}
		if ($_GET["dewptf"]) {
			$submit[dewpt_c] = convert($station[stnBaseUnit_temp], htmlspecialchars($_GET["dewptf"]));
		}
		// Pressure
		if ($_GET["baroinch"]) {
			$submit[barohpa] = convert($station[stnBaseUnit_pressure], htmlspecialchars($_GET["baroinch"]));
		}
		if ($_GET["baro3hrinch"]) {
			$submit[baro3hrhpa] = convert($station[stnBaseUnit_pressure], htmlspecialchars($_GET["baro3hrinch"]));
		} 
		// Humidity
		if ($_GET["humidity"]) {
			$submit[humidity] = htmlspecialchars($_GET["humidity"]);
		} 
		// Wind
		if ($_GET["winddir"]) {
  			$submit[wind_dir] = htmlspecialchars($_GET["winddir"]);
		} 
		if ($_GET["windspeedmphav_10m"]) {
  			$submit[av_wind_kts] = convert($station[stnBaseUnit_wind], htmlspecialchars($_GET["windspeedmphav_10m"]));
		} 
		if ($_GET["windgustmph_10m"]) {
  			$submit[wind_gust_kts] = convert($station[stnBaseUnit_wind], htmlspecialchars($_GET["windgustmph_10m"]));
		} 
		if ($_GET["windgustdir"]) {
  			$submit[wind_gust_dir] = htmlspecialchars($_GET["windgustdir"]);
		} 
		// Rain
		if ($_GET["raininch"]) {
  			$submit[rainmm] = convert($station[stnBaseUnit_rain], htmlspecialchars($_GET["raininch"]));
		} 
		//
		// Build the Query
		$query = "INSERT INTO " . $config[historyTable];
		$numItems = count($submit);
		$count = 1;
		foreach ($submit as $field => $value) {
			$dbFields .= $field;
			$dbValues .= "'" . $value . "'";
			if ($count < $numItems) {
				$dbFields .= ", ";
				$dbValues .= ", ";
			}
			$count++;
		}
		$query .= " (" . $dbFields . ") ";
		$query .= "VALUES (" . $dbValues . ")";
		if ($results = mysqli_query($con, $query)) {										// Write Entry
			echo "OK";
			$config[wxOutput] = "false";
			require_once('functions/weather.php');
		} else {
			die('Fail - DB write failure: ' . $con->error);									// error
		}
	}
} else {
	die('Fail - No content');
}
?>