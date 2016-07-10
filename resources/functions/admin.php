<?php
session_start();
// ###############################################
// #
// # 				Admin Display 
// #
// ###############################################		
//
// Setup script
global $log, $errMsgs, $events, $config, $station, $con, $request, $wx;
require '../Config.php';																	// Get the Config File
loadConfig();																				// Load the site configuration
// 
// Check Authentication
if ($_SESSION[AuthState]!=true) {															// If the session authenticated
	$output .= "<img src=\"$config[siteIconPath]Restricted.png\"><strong>ERROR</strong> - "; 
	$output .= "Your session is no longer authenticated. Please refresh page to re-authenticate.<br/>";
} else {
	// Check for Post data
	if($_POST) {
		foreach ($_POST as $field => $value) {
			$request[$field] = $value;
			//$log .= "POST: $field = $value <br/>";
		}
		if ($request[request]) {
			if ($request[table]) {
				if ($request[request] == "insert") {														
					$output .= insert();												
				} else if ($request[request] == "delete") {													
					$output .= delete();
				} else if ($request[request] == "amend") {													
					$output .= amend();
				} else if ($request[request] == "view") {
					$output .= view();											
				} else {
					$output .= $requestError . "(A" . __LINE__ . ")";
				}
				sendOutput($output);
			} else {
				$output .= $requestError . "(A" . __LINE__ . ")";
				sendOutput($output);
			}
		} else {
			if ($request[command] != "ShowCurrent") {
				$output .= $requestError . "(A" . __LINE__ . ")";
				sendOutput($output);
			}
		}
	// If no Post data, assume log view
	} else {
		if ($_GET) {
			foreach ($_GET as $id => $action) {
				echo "request to $action id: $id <br/>";
			}
		} else {
			viewLog();
		}
	}
}
//					
// ###############################################
// #					Output
// ###############################################
//
function sendOutput($output) {
	global $log, $errMsgs, $events, $config, $station, $con, $request, $wx;
	// Stop the clock
	$functionStop = microtime(true);
	// Render main output
	if ($output) {
		echo "<div id=\"adminOutputMain\">" . $output . "</div>";
	} else {
		echo "<div id=\"adminOutputMain\"><img src=\"$config[siteIconPath]Stop.png\">No Output from script was generated</div>";
	}
	// Debug
	if($config[debug]==true){
		$executionTime = round($functionStop - $functionStart,5);
		$log .= "admin(): " . $executionTime . " seconds<br/>";
		foreach ($request as $field => $value) {
			$log .= "request: $field = $value<br/>";
		}
		echo "<hr/><div id=\"adminOutputLog\">" . $log . "</div>"; 
	}
	// Add javascripts to page
	echo "<script> $(document).ready(function() { adminFunctions(); }); </script>";
	// Stop processing
	die();
}
//
// ###############################################
// #				 Functions
// ###############################################
//		
function getCurrentRules() {
	// TODO - Show Rules for the current Weather
	$output = "Here are some current Rules";
	return $output;	
	
}
//
// View Log
//
function viewLog() {
	//
	global $log, $errMsgs, $events, $config, $station, $con, $request, $wx;
	$request[dbTable] = $config[rulesTable];
	//
	$request[table] = $config[logTable];
	$request[sql] = "SELECT * FROM $config[logTable] WHERE status = 1";
	$paginationOutput = pagination();
	// Build Record Actions
	$recordActions = "Actions";																			// TODO - Create Actions
	// Build Output for Log Table
	$output .= "<img src=\"$config[siteIconPath]Time.png\"><h4>Admin Eventss</h4><br/>";
	$output .= "<div id=\"adminEventsTable\">";
	$output .= "<table>";
	$output .= "<tr class=\"header\">";																	// Header Row
	$output .= "<th scope=\"col\">ID</th>";
	$output .= "<th scope=\"col\">status</th>";																		
	$output .= "<th scope=\"col\">Date & Time (UTC)</th>";
	$output .= "<th scope=\"col\">Event</th>";
	$output .= "<th scope=\"col\">Related IDs</th>";
	$output .= "<th scope=\"col\">Actions</th>";
	$output .= "</tr>";
	// Build Results
	$count=0;
	if ($result = $con->query($request[sql])) {											
		while ($row = $result->fetch_assoc()) {									
			if ($count==0) {
				$output .=  "<tr>";																	
				$count++;
			} else {
				$output .=  "<tr class=\"alt\">";																		
				$count=0;
			}																	
			$fieldID = $row[id];
			foreach ($row as $field => $value) {
				if ($field == "status") {
					$output .= "<th>Un-Actioned</th>";
				} else {
					$output .= "<th>$value</th>";
				}
			}
			$output .= "<th>$recordActions</th>";													// Action items
			$output .= "</tr>";
		}
		$result->free();
	}
	sendOutput($output);
}
//
// View Request
//
function view() {
	global $log, $errMsgs, $events, $config, $station, $con, $request, $wx;
	if ($request[table]) {
		$request[dbTable] = $config[tablePrefix] . $request[table];
		if ($request[filter] == "True") {
			$request[filterSql] .= " WHERE ";
			$count = 1;
			foreach ($request as $field => $value) {
				switch ($field) {
					case "table" : $count++; break;
					case "dbTable" : $count++; break;
					case "filter" : $count++; break;
					case "filterSql" : $count++; break;
					case "filterSql" : $count++; break;
					case "requestSql" : $count++; break;
					case "request" : $count++; break;
					default :
						if (empty($value)) {
							break;
						} else {
							$request[filterSql] .= "$field LIKE '%$value%' ";
							if ($count < $num_post_items) { $request[filterSql] .= " AND "; $count++; }
						}
				}
			}
		}
		//
		// Rules
		//
		if ($request[table] == "Rules") {
			//
			require 'rulesEngine.php';
			//
			$output .= "<img src=\"$config[siteIconPath]Settings.png\"><h4>Weather Rules</h4><br/>";
			//
			// Active Rules
			//
			$active_Rules = array();
			$active_Rules = getRules("Active");
			$output .= "<div id='active_rules'>";
			$output .= "<h4>Active Rules</h4>";
			$output .= "<table>";
			$output .= "<tr class=\"header\">";																		// Header Row
			$output .= "<th scope=\"col\">ID</th>";																			
			$output .= "<th scope=\"col\">Rating</th>";
			$output .= "<th scope=\"col\">wx Code</th>";
			$output .= "<th scope=\"col\">Name</th>";
			$output .= "<th scope=\"col\">Icon</th>";
			$output .= "<th scope=\"col\">Actions</th>";
			$output .= "</tr>";
			$count=0;
			if (empty($active_Rules)) {
				$output .= "No rules meet current weather conditions";
			} else {
				$returnedRules = count($active_Rules);
				foreach ($active_Rules as $viewRule) {
					if ($count==0) {
						$output .=  "<tr>";																	
						$count++;
					} else {
						$output .=  "<tr class=\"alt\">";																		
						$count=0;
					}
					foreach ($viewRule as $field => $value) {
						if ($field == "Icon") {
							if ($value == "" ) {
								$output .= "<th>Not Set</th>";
							} else {
								$output .= "<th><img src=\"" . $config[wxIconPath] . $value . "\"></th>";
							}
						} else {
							$output .= "<th>$value</th>";
						}
					}
					$output .= "<th></th>";
					$output .=  "</tr>";	
				}	
			}
			$output .= "</table></div>";
			//
			// Rules Viewer
			//
			$output .= "<div id=\"wxRulesTable\">";
			$output .= "<h4>System Rules</h4>";
			$output .= "<table>";
			$output .= "<tr class=\"header\">";																		// Header Row
			$output .= "<th scope=\"col\">ID</th>";																			
			$output .= "<th scope=\"col\">Rating</th>";
			$output .= "<th scope=\"col\">wx Code</th>";
			$output .= "<th scope=\"col\">Name</th>";
			$output .= "<th scope=\"col\">Icon</th>";
			$output .= "<th scope=\"col\">Actions</th>";
			$output .= "</tr>";
			$count=0;
			//
			$valid_Rules = array();
			$valid_Rules = getRules("View");																	// Get the Rules File
			if (empty($valid_Rules)) {
				$output .= "No rules meet these criteria";
			} else {
				$returnedRules = count($valid_Rules);
				foreach ($valid_Rules as $viewRule) {
					if ($count==0) {
						$output .=  "<tr>";																	
						$count++;
					} else {
						$output .=  "<tr class=\"alt\">";																		
						$count=0;
					}
					foreach ($viewRule as $field => $value) {
						if ($field == "Icon") {
							if ($value == "" ) {
								$output .= "<th>Not Set</th>";
							} else {
								$output .= "<th><img src=\"" . $config[wxIconPath] . $value . "\"></th>";
							}
						} else {
							$output .= "<th>$value</th>";
						}
					}
				$output .= "<th><a href='admin' data-filter='$row[id]' data-request='edit' data-table='Rules'><img src='$config[siteIconPath]Edit.png'></a>";
				$output .= "<a href='$row[id]=delete'><img src='$config[siteIconPath]Delete.png'></a></th>";
				$output .=  "</tr>";	
				}	
			}
			$output .= "</table></div>";
			
		//
		// History
		//
		} else if ($request[table] == "History") {
			$request[sql] = "SELECT * FROM `". $request[dbTable] . "`";
			if ($request[filter]) {	
				// Apply date filters
				if ($request[filter]=="Month") {
					$timeframe = date('Y-m-d', strtotime('-30 days'));
				} else if ($request[filter]=="Week") {
					$timeframe = date('Y-m-d', strtotime('-7 days'));
				} else if ($request[filter]=="Yesterday") {
					$timeframe = date('Y-m-d', strtotime('-1 days'));
				} else if ($request[filter]=="Today") {
					$timeframe = date('Y-m-d');
				} else {
					$timeframe = NULL;
				}
				if ($timeframe != NULL) {
					$request[filterSql] .= " WHERE dateutc > '" . $timeframe . "'";
					$request[sql] .= " WHERE dateutc > '" . $timeframe . "'";
				}
			}
			$request[sql] .= " ORDER BY id DESC";
			$output .= pagination();
			// Build Output for History Table
			$output .= "<img src=\"$config[siteIconPath]Calendar.png\"><h4>Weather History</h4><br/>";
			$output .= "<div id=\"wxHistoryTable\">";
			$output .= "<table>";
			$output .= "<tr class=\"header\">";																	// Header Row
			$output .= "<th scope=\"col\">id</th>";																			
			$output .= "<th scope=\"col\">dateUTC</th>";
			$output .= "<th scope=\"col\">Temp &deg;C</th>";
			$output .= "<th scope=\"col\">DewPoint &deg;C</th>";
			$output .= "<th scope=\"col\">Barometer</th>";
			$output .= "<th scope=\"col\">3hr Baro</th>";
			$output .= "<th scope=\"col\">Humidity</th>";
			$output .= "<th scope=\"col\">Avg Wind Dir</th>";
			$output .= "<th scope=\"col\">Avg Wind Kts</th>";
			$output .= "<th scope=\"col\">Wind Gust Kts</th>";
			$output .= "<th scope=\"col\">WInd Gust Dir</th>";
			$output .= "<th scope=\"col\">Rain mm</th>";
			$output .= "<th scope=\"col\">24hr Rain</th>";
			$output .= "<th scope=\"col\">Actions</th>";													
			$output .= "</tr>";
			$count=0;																							// used to alternate colour
			if ($result = $con->query($request[sql])) {
				while ($row = $result->fetch_assoc()) {
					if ($count==0) {
						$output .=  "<tr>";																		// Result Row
						$count++;
					} else {
						$output .=  "<tr class=\"alt\">";														// Result Row
						$count=0;
					}
					$fieldID = $row[id];
					foreach ($row as $field => $value) {									
						if ($field == "Icon") {
							$output .= "<th><img src=\"$config[iconPath]$value\"></th>";
						} else {
							$output .= "<th>$value</th>";
						}
					}
					$output .= "<th><a href='$row[id]'>edit</a></th>";														// Action items
					$output .= "</tr>";
				}
				$result->free();
			}
			$output .= "</table></div>";
			$output .= $paginationOutput;
		//
		// Observations
		//
		} else if ($request[table] == "Observations") {
			$output .= "<img src=\"$config[siteIconPath]Stop.png\">function showDb_Observations not yet built<br/>";			// TODO
		//
		// Log
		//
		} else if ($request[table] == "Log") {
			$output .= "<img src=\"$config[siteIconPath]Stop.png\">function showDb_Log not yet built<br/>";						// TODO
		//
		// Config
		//
		} else if ($request[table] == "Config") {
			$output .= "<img src=\"$config[siteIconPath]Config.png\"><h4>Configuration</h4><br/><hr/>";
			$query .= "SELECT * FROM wxConfig";
			// Build Form
			$output .= "<div id=\"configTable\">";
			$output .= "<form name=\"wxConfig\" class=\"ajax\" method=\"post\" action=\"resources/functions/admin.php\">";
        	$output .= "<input type=\"hidden\" name=\"request\" value=\"amend\" />";
			$output .= "<input type=\"hidden\" name=\"table\" value=\"Config\" />";
			if ($result = $con->query($query)) {
				while ($row = $result->fetch_assoc()) {
					$output .= "<strong>" . $row['configKey'] . "</strong>: ";
					$output .= "<input type=\"text\" name=\"" . $row['configKey'] . "\" value=\"" . $row['configValue'] . "\" /><br/>";
				}
				$result->free();
			}
			$output .= "<input type=\"submit\" value=\"save\" />";
			$output .= "</form></div>";
		} else if ($request[table] == "Station") {
			$output .= "<img src=\"$config[siteIconPath]Stop.png\">function showDb_Station not yet built<br/>";					// TODO
		}
	} else {
		$output .= "<img src=\"$config[siteIconPath]Error.png\">Invalid request was submitted to admin console (A" . __LINE__ . ")";
	}
	return $output;
}
//
// Insert
//
function insert() {
	global $log, $errMsgs, $events, $config, $station, $con, $request, $wx;
	
	$output .= "<img src=\"$config[siteIconPath]Stop.png\">function insert not yet built<br/>";
	
	// Output
	return $output;
}
//
// amend
//
function amend() {
	global $log, $errMsgs, $events, $config, $station, $con, $request, $wx;
	if($request[table] == Config) {
		$count = 1;
		$db_table = $config[tablePrefix] . "Config";
		$numfields = count($request) -3;
		$query = "UPDATE " . $config[tablePrefix] . "Config ";
		foreach ($request as $field => $name) {
			if ($field != "request" && $field != "table" && $field != "authKey") {
				$query = "UPDATE " . $config[tablePrefix] . "Config SET configValue = '" . $name . "' WHERE configKey = '" . $field . "'";
				echo $query;
				$con->query($query);
			}
		}
	} else {
		// TODO - Process normal submissions
	}
	// require an ID

	
	// Output
	return $output;
}
//
// delete
//
function delete() {
	global $log, $errMsgs, $events, $config, $station, $con, $request, $wx;
	// require an ID
	$output .= "<img src=\"$config[siteIconPath]Stop.png\">function delete not yet built<br/>";
	
	// Output
	return $output;
}
//
// Create Paginaton
//
function pagination() {
	global $log, $errMsgs, $events, $config, $station, $con, $request, $wx, $pagination;											
	// Get Results total																				
	$query = "SELECT COUNT(id) FROM " . $request[dbTable];										// Build pagination query
	if ($request[filterSql]) {
		$query .= $request[filterSql];	
	}
	echo $query;
	$result = mysqli_query($con, $query);
	$row = mysqli_fetch_row($result);
	$numResults = $row[0];																		// Define number of results in result set
	$result->free();
	$rowsPerPage = $config[resultsPerPage];
	// Check if pagination of results is required
	if ($numResults > $config[resultsPerPage]) {												// Pagination required
		// Add data to append to links for inclusion in POST
		$urlData = "data-request=\"$request[request]\" data-table=\"$request[table]\" data-filter=\"$request[filter]\" ";
		// Set pages
		$lastPage = ceil($numResults/$rowsPerPage);												// Sets the page number of Last page in set
		if ($lastPage < 1) {																	// Protect Pagination to whole
			$lastPage = 1;
		}
		if ($request[page]) {																	// Capture current page number
			$currentPage = preg_replace('#[^0-9]#', '', $request[page]);						// Strip POST element to numbers only
		} else {
			$currentPage = 1;																	// Default to page 1 if nothing set
		}
		if ($currentPage < 1) {																	// Protect Page Num to last page
			$currentPage = 1;
		} else if ($currentPage > $lastPage) {
			$currentPage = $lastPage;
		}
		$request[sql] .= " LIMIT " . ($currentPage - 1) * $rowsPerPage . ", " . $rowsPerPage;	// Set Pagination limits
		// Build Output for pagination
		$output .= "<div id=\"pagination\">";
		$output .= "page $currentPage of $lastPage - ";
		$output .= "$numResults records retrieved";
		if ($lastPage != 1) {
			if ($currentPage > 1) {
				$previousPage = $currentPage - 1;
				$output .= "<div class=\"left\">";
				$output .= "<a href=\"admin.php\" data-page=\"1\" $urlData >First</a>";
				$output .= "<a href=\"admin.php\" data-page=\"$previousPage\" $urlData >Previous</a>";
				$output .= "</div>";
			} else {
				$output .= "<div class=\"left\">";
				$output .= "<span>First</<span>";
				$output .= "<span>Previous</<span>";
				$output .= "</div>";
			}
			if ($currentPage != $lastPage) {
				$nextPage = $currentPage + 1;
				$output .= "<div class=\"right\">";
				$output .= "<a href=\"admin.php\" data-page=\"$nextPage\" $urlData >Next</a>";
				$output .= "<a href=\"admin.php\" data-page=\"$lastPage\" $urlData>Last</a>";
				$output .= "</div>";	
			} else {
				$output .= "<div class=\"right\">";
				$output .= "<span>Next</<span>";
				$output .= "<span>Last</<span>";
				$output .= "</div>";
			}
		}
		$output .= "</div>";
	} else {
		return false;
	}
	return $output;
}			

// ###############################################
// #				Old Functions
// ###############################################

//
// Command - Show database entry
//
function cmdShowDb() {
	global $log, $errMsgs, $events, $config, $station, $con, $request, $wx;
	// Rules Table
	if($request[table] == "Rules") {
		$query = "SELECT Icon, `id`, ruleName, wxRuleRating, wxCode, comments FROM `wxRules`";
		if ($request[commandFilter] && $request[commandFilterVal]) {
			$query .= " WHERE " . $request[commandFilter] . "= '" . $request[commandFilterVal] . "'";
		}
		
	// History Table
	} else if ($request[table] == "History") {
		if ($request[table]) {
			// Set the timeframe
			if ($request[filter]=="Month") {
				$timeframe = date('Y-m-d', strtotime('-30 days'));
			} else if ($request[filter]=="Week") {
				$timeframe = date('Y-m-d', strtotime('-7 days'));
			} else if ($request[filter]=="Yesterday") {
				$timeframe = date('Y-m-d', strtotime('-1 days'));
			} else {
				$timeframe = date('Y-m-d');
			}
			$query .= "SELECT * FROM wxHistory WHERE dateutc > '" . $timeframe . "' ORDER BY id DESC ";
			$log .= $query . "<br/>";
		}
		// Set Data block for inclusion in any POST requests
		$urlData .= "data-request=\"$request[request]\" data-table=\"wxRules\" data-filter=\"$request[commandFilter]\" ";
		
		// Determine pagination requirements for results
		$sql = "SELECT COUNT(id) FROM wxHistory WHERE dateutc > '" . $timeframe . "'";
		$paginationQuery = mysqli_query($con, $sql);
		$row = mysqli_fetch_row($paginationQuery);
		$numResults = $row[0];
		$rowsPerPage = 15;																				// Number of items to display per page
		$lastPage = ceil($numResults/$rowsPerPage);														// Sets the page number of Last page in set
		if ($lastPage < 1) {																			// Protect Pagination to whole
			$lastPage = 1;
		}
		if ($postContent['page']) {																		// Capture current page number
			$currentPage = preg_replace('#[^0-9]#', '', $postContent['page']);							// Strip POST element to numbers only
		} else {
			$currentPage = 1;																			// Default to page 1 if nothing set
		}
		if ($currentPage < 1) {																			// Protect Page Num to last page
			$currentPage = 1;
		} else if ($currentPage > $lastPage) {
			$currentPage = $lastPage;
		}
		$query .= "LIMIT " . ($currentPage - 1) * $rowsPerPage . ", " . $rowsPerPage;					// Set Pagination limits
		$paginationPosition = "page $currentPage of $lastPage";
		$paginationRecords = "$numResults records retrieved";
		if ($lastPage != 1) {
			if ($currentPage > 1) {
				$previousPage = $currentPage - 1;
				$pagination .= "<div class=\"left\">";
				$pagination .= "<a href=\"admin.php\" name=\"1\">First</a>&nbsp;";
				$pagination .= "<a href=\"admin.php\" name=\"$previousPage\">Previous</a>;";
				$pagination .= "</div>";
			}
			if ($currentPage != $lastPage) {
				$nextPage = $currentPage + 1;
				$pagination .= "<div class=\"right\">";
				$pagination .= "<a href=\"admin.php\" data-page=\"$nextPage\" $urlData >Next</a>";
				$pagination .= "<a href=\"admin.php\" name=\"$lastPage\">Last</a>";
				$pagination .= "</div>";	
			}
			
		}
		// Build Record Actions
		$recordActions = "Actions";																		// TODO - Create Actions
		
	// Build Output for Config Table
	} else if ($request[table] == "Config") {
		$output .= "<img src=\"$config[siteIconPath]Config.png\"><h4>Configuration</h4><br/><hr/>";
		$query .= "SELECT * FROM wxConfig";
		if ($result = $con->query($query)) {
			while ($row = $result->fetch_assoc()) {
				$output .= "<strong>" . $row['configKey'] . "</strong>: " . $row['configValue'] . "<br/>";
			}
			$result->free();
		}
		
	} else {
		$output .= "<img src=\"$config[siteIconPath]Error.png\"><strong>ERROR</strong> - Invalid table requested<br/>";
	}
	// Return
	return $output;

}
?>
