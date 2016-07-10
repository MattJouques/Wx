<?php 
// ###############################################
// #
// # 			Database functions file 
// #
// ###############################################
//
// Initialise the databse connection
//
function dbInitialise(){
	// Setup Function
	global $log, $errMsgs, $config, $con, $session, $wx;
	$con = mysqli_connect($config[dbHost],$config[dbUser],$config[dbPass],$config[dbName]);					// Create Connection
	if (mysqli_connect_errno($con)) {
		$errMsgs .= "<strong>CRITICAL ERROR</strong> - Failed to connect to MySQL:" . mysqli_connect_error() . "<br/>";		// Check connection
		return false;
	} else {
		return true;
	}
}
//
// get fields (columns) from Database
//
function getDbFields($table) {
	// Setup Function
	$functionStart = microtime(true);												// Log Execution Time
	global $log, $errMsgs, $config, $con, $session, $wx;
	// Function
	if(isset($table)) {
		$query = "SELECT * FROM " . $table . " LIMIT 1";
		if ($result = $con->query($query)) {	
			while ($finfo = $result->fetch_field()) {								// Get Field types
				$dbColumns[$finfo->name] = $finfo->type;
			}
		} else {
			$errMsgs .= "Error - No fields were returned in $table<br/>";
			return false;
		}
	} else {
		$errMsgs .= "Error - Table not defined for function getDbFields<br/>";
		return false;
	}
	// Debug
	$functionStop = microtime(true);
	if($config[debug]==true){
		$executionTime = round($functionStop - $functionStart,5);
		$log .= "getDbFields(): " . $executionTime . " seconds<br/>";
		$log .= "getDbFields() returned " . count($dbColumns) . " fields from " . $table . "<br/> ";
	}
	// Return
	return $dbColumns;	
}
//
// Insert Records into the database
//
function dbInsert($table, $contents) {
	// Setup Function
	global $log, $errMsgs, $config, $con, $session, $wx;
	// Functions
	if(empty($table)) {																// Check for Empty Table
		return false;																// Die if Table not provided
	}
	if(empty($contents)) {															// Check for write contents
		return false;																// Die if contents not provided
	} else {
		if(is_array($contents)) {													// Check for expected Array
			$dbColumns = getDbFields($table);										// Get Db Column Names
			foreach ($dbColumns as $dbField) {
				if (array_key_exists($dbField, $contents)) {						// Check submission matches Db fields
					next;
				} else {
					unset($contents[$dbField]);										// Remove submission fields not in DB
				}
			}
		}
		$numFields = count($contents);												// Count to ID last record
		$count=1;																	// Start the Counter
		foreach ($contents as $field => $value) {
			if($field=="id" or $field=="request") {
				$count++;
			} else {
				$dbFields .= $field;
				if($value==0 or $value==0.0) {
					$dbValues .= "'" . $value . "'";
				} else if ($value==NULL) {
					$dbValues .= "NULL";											// Set the Values to NULL if no value
				} else {
					$dbValues .= "'" . $value . "'";								// Add ' around values where not NULL
				}
				if($count!=$numFields) {											// Add , after entries when not last
					$dbFields .= ", ";	
					$dbValues .= ", ";							
					$count++;														// Increment the Counter
				}
			}
		}
	}
	$query = "INSERT INTO " . $table;												// Build the Query
	$query .= " (" . $dbFields . ") ";
	$query .= "VALUES (" . $dbValues . ")";
	if ($results = mysqli_query($con, $query)) {									// Write Entry
		$log .= "Database updated with id: " . mysqli_insert_id($con) . "<br/>";	// Update Log
	} else {
		$errMsgs .= "DB write failure: " . $con->error . "<br/>";					// Log error
	}
	// Debug
	if($config[debug]==true){
		$log .= "dbInsert(): " . $executionTime . " seconds<br/>";
		$log .= "dbInsert() executed with query = " . $query . "<br/> ";
	}
	// Return
	return true;
}
//
// Amend record in the database
//



//
// Delete records from the database
//

//
// Log Admin Event
//
function logAdminEvents() {
	global $log, $errMsgs, $events, $config, $station, $con, $wx, $ref_ids;
	if (is_array($ref_ids)) {
		$count_ref_ids = count($ref_ids);
		$count = 1;
		foreach ($ref_ids as $key => $value) {
			$ref_id_content .= $value;
			if ($count < $count_ref_ids) {
				$ref_id_content .= ",";
				$count++;
			}
		}
	} else if ($ref_ids) {
		$ref_id_content = $ref_ids;
	} else {
		$ref_id_content = NULL;
	}
	$query = "INSERT INTO $config[logTable] (status, dateutc, event, ref_ids) VALUES ('1', '$_SESSION[datetimeNow]', '$events', '$ref_id_content')";
	if ($results = mysqli_query($con, $query)) {									// Write Entry
		$log .= "Database updated with id: " . mysqli_insert_id($con) . "<br/>";	// Update Log
	} else {
		die("DB write failure: " . $con->error . "<br/>");							// return error
	}
	return true;
}

//
// Encryption
//
function encrypt($string) { 
   global $session;
   $result = '';
    for($i = 0; $i < strlen($string); $i++) {
    	$char = substr($string, $i, 1);
    	$keychar = substr($session[key], ($i % strlen($session[key]))-1, 1);
    	$char = chr(ord($char) + ord($keychar));
    	$result .= $char;
    }
    return base64_encode($result);
} 
function decrypt($string) { 
	global $session;
	$result = '';
	$string = base64_decode($string);
	for($i = 0; $i < strlen($string); $i++) {
	$char = substr($string, $i, 1);
    	$keychar = substr($session[key], ($i % strlen($session[key]))-1, 1);
    	$char = chr(ord($char) - ord($keychar));
    	$result .= $char;
    }
    return $result;
} 
