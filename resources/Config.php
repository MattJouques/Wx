<?php 
session_start(); 
// ###############################################
// #
// # 				configuration file 
// #
// ###############################################

function loadConfig() {
	// configuration
	global $log, $errMsgs, $config, $station, $con, $wx;
	
	
	// TODO - REMOVE THIS
	$_SESSION[AuthState]=true;

// ###############################################
//				 DO NOT EDIT ABOVE HERE
// ###############################################

	// Set database connection 
		$config[dbHost] = "127.0.0.1";							// Set the Database Hostname
		$config[dbUser] = "root";								// Set the database username
		$config[dbPass] = "JRR9fql4";							// Set the database password for the user
		$config[dbName] = "Wx";									// Set the name of the database to use
	// Set Debug Level
		$config[debug] = true;									// true = shows full log in footer
	// Set Default timezone
		date_default_timezone_set('UTC');

// ###############################################
//				 DO NOT EDIT BELOW HERE
// ###############################################

	// Initialise Database
	//
	require_once('functions/database.php');
	if (!$con) {
		if(dbInitialise()==false) {
			$errMsgs .= "<strong>CRITICAL ERROR</stong> - Database failed to initialise<br/>";
			return false;
		}
	}
	//
	// Read Config
	//
	$query = "SELECT * FROM wxConfig";
	if(!$result = $con->query($query)){
    	$errMsgs .= "<strong>CRITICAL ERROR</stong> - Failed to read config from database :" . $con->error . "<br/>";
	} else {
		while ($row = $result->fetch_assoc()) {
			$config[$row['configKey']] = $row['configValue'];
		}
	}
	$result->free();
	$config[referenceTable] = $config[tablePrefix] . "Reference";										// Reference Data Table
	$config[latestTable] = $config[tablePrefix] . "Latest";												// Latest Weather Table
	$config[rulesTable] = $config[tablePrefix] . "Rules";												// Rules Table
	$config[historyTable] = $config[tablePrefix] . "History";											// History Table
	$config[logTable] = $config[tablePrefix] . "Log";													// Log Table
	$config[tideTable] = $config[tablePrefix] . "Tide";													// Log Table
	//
	// Read Station Info
	//
	$query = "SELECT * FROM wxInfo";
	if(!$result = $con->query($query)){
    	$errMsgs .= "<strong>CRITICAL ERROR</stong> - Failed to read station information from database :" . $con->error . "<br/>";
	} else {
		while ($row = $result->fetch_assoc()) {
			$station[$row['infoKey']] = $row['infoValue'];
		}
	}
	$result->free();
	//
	// Setup Session
	//
	$_SESSION['authKey'] = $config[dbName] . "_" . date('d') . "_" . $config[authKey];				// Authorisation
	//
	// Setup time constants
	//
	$_SESSION[dateToday] = date('Y-m-d');															// Set Todays Date
	$_SESSION[dateYesterday] = date('Y-m-d', time() - 60 * 60 * 24);								// Set Yesterdays Date
	$_SESSION[timeNow] = date('H:i:s');																// Set Time Now
	$_SESSION[dateMETAR] = date('dHi');																// METAR Date format
	$_SESSION[datetimeNow] = date('Y-m-d H:i:s');													// Set datetime for session
	$_SESSION[datetime6h] = date("F j, Y", time() - 60 * 60 * 6);									// 6 hours ago
	$_SESSION[datetime3h] = date("F j, Y", time() - 60 * 60 * 3);									// 3 hours ago
	$_SESSION[datetime1h] = date("F j, Y", time() - 60 * 60 * 1);									// 1 hours ago
	$_SESSION[Fdatetime6h] = date("H\:00\Z", time() + 60 * 60 * 6);									// 6 hours ago
}
//
// Load the header information
//
function wxHeader() {																				// TODO - Get this from Db
	global $log, $errMsgs, $config, $station, $con, $wx;
	// METAR
	$query = "SELECT * FROM $config[latestTable] WHERE field = 'METAR'";
	if($result = $con->query($query)){
		while ($row = $result->fetch_assoc()) {
			$wx[METAR] = $row['value'];
		}
	$result->free();
	}
	// Icon
	$query = "SELECT * FROM $config[latestTable] WHERE field = 'wxIcon'";
	if($result = $con->query($query)){
		while ($row = $result->fetch_assoc()) {
			$wx[wxIcon] = $row['value'];
		}
	$result->free();
	}
}



?>