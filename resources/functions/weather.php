<?php 
// ###############################################
// #
// # 				Weather Data  
// #
// ###############################################
// #
// # Gets data from the weather station, processes results and generates the current & forecast
// # forecast is based on the Sager Forecasting engine
//
//
global $log, $errMsgs, $config, $station, $con, $wx, $trigger;																		

getWeather();


//
// Weather Analysis
//
function getWeather() {
	global $log, $errMsgs, $config, $station, $con, $wx;
	define('__ROOT__', dirname(dirname(__FILE__))); 
	require_once(__ROOT__.'/Config.php');														// Get the Config File
	loadConfig();
	require 'feeds.php';
	//
	// Set the global position
	//
	$query = "SELECT code FROM $config[referenceTable] WHERE Category = 'zone' AND filterLo < $station[stnGPSdecLAT] AND filterHi > $station[stnGPSdecLAT]";
	if($result = $con->query($query)) {
		 $value = $result->fetch_array();
		 $station[zone] = $value[0];
		 $result->free();
	}
	//
	//	Get the weather data from the station
	//
	$functionStart = microtime(true);
	if ($config[weatherStationDataFile] == "Database") {
		$query = "SELECT * FROM $config[latestTable]";
		if($result = $con->query($query)){
			while ($row = $result->fetch_assoc()) {
				$wx[$row['field']] = $row['value'];
			}
		}
	} else if ($fileContents = preg_split("/\\r\\n|\\r|\\n/", file_get_contents($_SERVER['DOCUMENT_ROOT'].$config[weatherStationDataFile]))) {
		foreach ($fileContents as $value) {
			if($value!="") {
				$raw = str_replace('"', "", $value);
				$temp = explode (":", $raw);
				if($temp[2]) {
					$temp[1] = $temp[1] . ":" . $temp[2];
				}
				$wxField = ltrim($temp[0],"{");
				if($wxField=="timeUTC") {
					$wxValue = str_replace(',', "", $temp[1]);
				} else {
					$wxValue = rtrim($temp[1], ",");
				}
				$wx[$wxField] = rtrim($wxValue, "}");
			}
		}
		// Get Daily items from the weather station
		//
		if($fileContents = explode (",", file_get_contents($_SERVER['DOCUMENT_ROOT'].$config[dailyDataFile]))) {
			foreach ($fileContents as $value) {
				$raw = str_replace('"', "", $value);
				$wxField = trim(ltrim(strstr($raw, ':', true),"{"));
				$wxValue = ltrim(strstr($raw, ':'),":");
				$wx[$wxField] = rtrim($wxValue, "}");
			}
		}
	} else {
		$errMsgs .= "Unable to get data from weather station<br/>";
		die();
	}
	// Debug
	$functionStop = microtime(true);
	$executionTime = round($functionStop - $functionStart,5);
	$log .= "Getting Weather Station Data took: " . $executionTime . " seconds<br/>";	
	//
	// Get Information from Wunderground
	//
	$functionStart = microtime(true);
	if ($config[wg_API_Key]) {
		// Moon Phase
		$query = "SELECT * FROM $config[latestTable] WHERE field = 'xfeedDate'";
		if($result = $con->query($query)){
			while ($row = $result->fetch_assoc()) {
				$xfeedDate = $row['value'];
			}
			if ($xfeedDate == $_SESSION[dateToday]) {												// Only get this once a day
				while ($row = $result->fetch_assoc()) {
					if ($row['field'] == "moonday") {
						$wx[moonday] = $row['value'];
					}
					if ($row['field'] == "percentIlluminated") {
						$wx[percentIlluminated] = $row['value'];
					}
				}
			} else {
				$moonFeed = json_decode(file_get_contents("http://api.wunderground.com/api/$config[wg_API_Key]/astronomy/q/pws:IWESTMER2.json"), true, 512); 
				$moonPhase = $moonFeed[moon_phase];
				$wx[moonday] = $moonPhase[ageOfMoon];												// Sets moon icon
				$wx[percentIlluminated] = $moonPhase[percentIlluminated];
				$wx[xfeedDate] = $_SESSION[dateToday];
			}
		$result->free();
		} else {
			$moonFeed = json_decode(file_get_contents("http://api.wunderground.com/api/$config[wg_API_Key]/astronomy/q/pws:IWESTMER2.json"), true, 512); 
			$moonPhase = $moonFeed[moon_phase];
			$wx[moonday] = $moonPhase[ageOfMoon];													// Sets moon icon
			$wx[percentIlluminated] = $moonPhase[percentIlluminated];
			$wx[xfeedDate] = $_SESSION[dateToday];
		}
		// Weather Alerts
		$query = "SELECT * FROM $config[latestTable] WHERE field = 'xfeedTime'";
		$checkTime = (time() - ($config[wgroundRefresh] * 60));
		if($result = $con->query($query)){
			while ($row = $result->fetch_assoc()) {
				$xfeedTime = $row['value'];
			}
			$remaining = $xfeedTime - (time() - ($config[wgroundRefresh] * 60));
			if ($remaining < 0) {													// Only get this every 10 mins
				$alertFeed = json_decode(file_get_contents("http://api.wunderground.com/api/$config[wg_API_Key]/alerts/q/pws:IWESTMER2.json"), true, 512);
				$alertDetail = $alertFeed[alerts];
				$alertInfo = $alertFeed[response];
				$wx[alertType] = $alertDetail[type];
				$wx[alertLevel] = $alertDetail[level_meteoalarm_name];
				$wx[alertTitle] = $alertDetail[description];
				$wx[alertDetail] = $alertDetail[level_meteoalarm_description];
				$wx[alertAttribution] = $alertDetail[attribution];
				$wx[alertNumber] = $alertInfo[alerts];
				$wx[xfeedTime] = time();
			} else {
				$query = "SELECT * FROM $config[latestTable] WHERE field LIKE '%alert%'";
				if($result = $con->query($query)){
					while ($row = $result->fetch_assoc()) {
						$wx[$row['field']] = $row['value'];
					}
				$result->free();
				}												
			}
		}
		if ($wx[alertNumber] == 0) {
			$wx[alertStatus] = "None";
			$wx[alertText] = "No weather Alerts";
		} else {
			$wx[alertStatus] = $wx[alertLevel];
			$wx[alertText] = $wx[alertNumber] . " alerts";
		}
	}
	// Debug
	$functionStop = microtime(true);
	$executionTime = round($functionStop - $functionStart,5);
	$log .= "Getting wunderground Data took: " . $executionTime . " seconds<br/>";	
	//
	// Tide Data
	//
	if ($config[includeTides] == true) {
		require_once('tides.php');
		$tideData = getTides();
	}
	// Debug
	$functionStop = microtime(true);
	$executionTime = round($functionStop - $functionStart,5);
	$log .= "Getting tidal Data took: " . $executionTime . " seconds<br/>";	
	//
	// Get cloud cover from METAR
	//
	$functionStart = microtime(true);
	$html = "http://weather.noaa.gov/pub/data/observations/metar/stations/$station[stnLocalMETARcode].TXT"; 
	if($metar = file_get_html($html)) {
		$slashes = array ("////", "///", "//"); 												// Removes unnecessary slashes from $metar:
		$metar = str_replace ($slashes, "", $metar);
		$metar = substr ($metar, 29);
		$comments = array ("BECMG", "RMK", "TEMPO");											// Removes unnecessary comments from $metar:
		foreach ($comments as $comment) {
			$pos = strpos ($metar, " ".$comment);
			if ($pos !== false) {
				$metar = substr ($metar, 0, $pos);
			}
		}
		$ctcodes = array ("ACC", "ACSL", "CB", "CBMAM", "CCSL", "SCSL", "TCU");					// Removes Cloud Type Codes at the end of cloud heights
		$metar = str_replace ($ctcodes, "", $metar);
		$cdata = explode (" ", preg_replace ("/[0-9]+/", "", $metar) );							// Removes cloud heights
		$ccodes = explode (", ","VV, OVC, BKN, SCT, FEW, CLR, SKC, CAVOK, NCD, NSC");			// METAR Cloud Codes
		$cresult = array_intersect ($ccodes, $cdata);											// Finds matches in both $ccodes and $cdata
		$clouds = array_values ($cresult);														// Returns the first Cloud Code match found only
		$wx[cloudCoverMETAR] = array_shift ($clouds);											// Sets current Cloud Cover
	}
	//
	// Cloud base
	//
	$functionStart = microtime(true);
	$wx[cloudbase] = (($wx[temp]-$wx[dew])*1000) / 2.5;											// Calculates current Cloud base
	$wx[cloudbaseMETAR] = substr(str_pad($wx[cloudbase], 5, "0", STR_PAD_LEFT), 0, 3);			// Set Cloudbase to abbreviated form
	// Debug
	$functionStop = microtime(true);
	$executionTime = round($functionStop - $functionStart,5);
	$log .= "setting Cloud Data took: " . $executionTime . " seconds<br/>";	
	//
	// Wind (Position 1 on the Sager Model)
	//
	$functionStart = microtime(true);
	// Determine the current average wind direction
	$query = "SELECT code, Description FROM $config[referenceTable] WHERE Category = 'windDirection' AND filterLo < $wx[avgbearing] AND filterHi > $wx[avgbearing]";
	if($result = $con->query($query)) {
		 $value = $result->fetch_array();
		 $wx[windDirection] = $value[0];
		 $wx[windDirectionFull] = $value[1];
		 $result->free();
	}
	// Determine the 6h average wind direction
	$query = "SELECT code FROM $config[referenceTable] WHERE Category = 'windDirection' AND filterLo < $wx[avgbearing6h] AND filterHi > $wx[avgbearing6h]";
	if($result = $con->query($query)) {
		 $value = $result->fetch_array();
		 $wx[windDirection6h] = $value[0];
		 $result->free();
	}
	// Check if there has been no change
	if ($wx[windDirection6h] == $wx[windDirection]) {
		$wx[windDirChange] = "Calm";
	} else {
		// Determine wind direction change
		$query = "SELECT code FROM $config[referenceTable] WHERE Category = 'windChange' AND filter1 = '$wx[windDirection]' AND filter2 = '$wx[windDirection6h]'";
		if($result = $con->query($query)) {
			 $value = $result->fetch_array();
			 $wx[windDirChange] = $value[0];
		 $result->free();
		}
	}
	// Set the Wind Forecast position based on location
	$query = "SELECT code FROM $config[referenceTable] WHERE Category = 'forecastWindCode' AND zoneFilter = '$station[zone]' AND filter1 = '$wx[windDirection]' AND filter2 = '$wx[windDirChange]'";
	if($result = $con->query($query)) {
		$value = $result->fetch_array();
		$wx[windForecastPosition] = $value[0];													// Sets position 1 on Sager Model
	$result->free();
	}
	$log .= "Position 1 is $wx[windForecastPosition] <br/>";

	//
	// Pressure (Positions 2 and 3 on the Sager Model)
	//
	if ($config[pressUnit] != 'Hpa') {															// Convert to base if required
		require 'maths.php';
		$wx[press] = convert_Pressure_to_Hpa($wx[press], $config[pressUnit]);
		$wx[presstrendval] = convert_Pressure_to_Hpa($wx[pressTrendVal], $config[pressUnit]);
	}
	$query = "SELECT code FROM $config[referenceTable] WHERE Category = 'forecastBaroPosition' AND filterLo < $wx[press] AND filterHi > $wx[press]";
	if($result = $con->query($query)) {
		 $value = $result->fetch_array();
		 $wx[pressurePosition] = $value[0];														// Sets Position 2 on the Sager Model
		 $log .= "Position 2 is $wx[press]($wx[pressurePosition]) <br/>";
	$result->free();
	}
	$query = "SELECT code, description FROM $config[referenceTable] WHERE Category = 'baroTrend' AND filterLo < $wx[presstrendval] AND filterHi > $wx[presstrendval]";
	if($result = $con->query($query)) {
		 $value = $result->fetch_array();
		 $wx[pressureTrendPosition] = $value[0];												// Sets Position 3 on the Sager Model
		 $wx[pressureTrend] = $value[1];
		 $log .= "Position 3 is $wx[pressureTrend] ($wx[pressureTrendPosition]) <br/>";
	$result->free();
	}
	//
	// Weather (Position 4 on the Sager Model) and Process Additional items
	//
	$wx[dewDiff] = $wx[temp] - $wx[dew];														// Set dewpoint differential
	if($wx[rrate] == 0.0 ) {																	// It is not precipitating now
		if ($wx[LastRainTipISO] == $_SESSION[dateToday]) {										// Has it rained today?
		$query = "SELECT dateutc FROM $config[historyTable] WHERE dateutc LIKE '%$_SESSION[dateToday]%' AND rainmm NOT LIKE '0.00' ORDER BY 1 DESC LIMIT 1";
			if($result = $con->query($query)) {
				$value = $result->fetch_array();
				if (empty($value[0])) {															// It has not rained today
					$wx[rainText] .= "It has not rained today <br/>";
				} else {																		// It has rained today
					$lastRainToday = explode(" ",$value);
					$wx[lastRainTipDay] = date('Y-m-d'); 										// lastRainTip Day set to today
					$wx[lastRainTipTime] = $lastRainToday[0];									// Set Time of last rain
					if ($wx[lastRainTipTime] < $_SESSION[datetime1h]) {
						$wx[rainText] .= "Precipitation has been logged in the last hour <br/>";
					}
				}
			$result->free();
			}
			$errMsgs .= "ERROR - Unable to get history (W" . __LINE__ . ")";
		}
		$query = "SELECT code, Description FROM $config[referenceTable] WHERE Category = 'wxPosition' AND filter1 LIKE '%$wx[cloudCoverMETAR]%'";
		if($result = $con->query($query)) {
			 $value = $result->fetch_array();
			 $wx[wxPosition] = $value[0];													// Sets Position 4 on the Sager Model
			 $wx[cloudCover] = $value[1];													// Sets Friendly Cloud Cover
			 $log .= "Position 4 is $wx[cloudCover] ($wx[wxPosition]) <br/>";
		$result->free();
		}
	} else {																				// It is currently Precipitating
		$wx[lastRainTipDay] = date('Y-m-d'); 												// lastRainTip Day set to today
		$wx[lastRainTipTime] = date('H:i');													// LastRainTip Time set to now
		$wx[wxPosition] = 5;																// Sets Position 4 on the Sage Model
	}
	// Debug
	$functionStop = microtime(true);
	$executionTime = round($functionStop - $functionStart,5);
	$log .= "Creating the sager forecast took: " . $executionTime . " seconds<br/>";	
	//
	// Process Weather Rules
	//
	$functionStart = microtime(true);
	require 'rulesEngine.php';																// Get the Rules File
	$currentWeather = getRules();
	if (empty($currentWeather)) {
		$wx[wxText] = "There are no rules to match the current conditions <br/>";
	} else { 
		foreach ($currentWeather as $field => $value) {
			if ($field == "Icon") {
				if (empty($value)) {
					$wx[wxIcon] = "unsettled";
				} else {
					$wx[wxIcon] = $value;
				}
			}
			if ($field == "wxCode") {
				$wx[wxCode] = $value;
			}
		}
		$query = "SELECT * FROM $config[referenceTable] WHERE category = 'wxCode' AND code = '$wx[wxCode]'";
		if($result = $con->query($query)) {
			while ($row = $result->fetch_assoc()) {
				if ($row['Description']) {
					$wx[wxText] = $row['Description'];
				} else {
					$wx[wxText] = "Unable to determine current conditions";
				}
			}
		$result->free();
		} else {
			$wx[wxText] = "Unable to determine current conditions";
		}
	}
	// Debug
	$functionStop = microtime(true);
	$executionTime = round($functionStop - $functionStart,5);
	$log .= "Weather Rules took: " . $executionTime . " seconds<br/>";		
	//
	// Create the forecast
	//
	$functionStart = microtime(true);
	$forecastCode = $wx[windForecastPosition].$wx[pressurePosition].$wx[pressureTrendPosition].$wx[wxPosition];
	$query = "SELECT code, Description FROM $config[referenceTable] WHERE Category = 'forecast' AND filter1 = '$forecastCode'";
	if($result = $con->query($query)) {
		 $value = $result->fetch_array();
		 $wx[forecastCode] = $value[1];
		 $forecastItems = explode('_', $value[0]);
		 $query = "SELECT Description FROM $config[referenceTable] WHERE Category = 'forecastExpected' AND code = '$forecastItems[0]'";
		 if($result = $con->query($query)) {
		 	$value = $result->fetch_array();
		 	$wx[forecast] = $value[0] . "; ";													// Expected
		 }
		 $query = "SELECT Description FROM $config[referenceTable] WHERE Category = 'forecastWindVelocities' AND filter1 = '$config[windUnit]' AND code = '$forecastItems[1]'";
		 if($result = $con->query($query)) {
		 	$value = $result->fetch_array();
		 	$wx[forecast] .= $value[0];															// Velocities
		 }
		 $query = "SELECT Description FROM $config[referenceTable] WHERE Category = 'forecastWinds' AND zoneFilter = '$station[zone]' AND code = '$forecastItems[2]'";
		 if($result = $con->query($query)) {
		 	$value = $result->fetch_array();
		 	$wx[forecast] .= " " . $value[0];													// Direction
		 }
		 if ($forecastItems[3]) {
		 	$query = "SELECT Description FROM $config[referenceTable] WHERE Category = 'forecastWinds' AND zoneFilter = '$station[zone]' AND code = '$forecastItems[2]'";
			 if($result = $con->query($query)) {
				$value = $result->fetch_array();
				$wx[forecast] .= ", becoming " . $value[0];										// Becoming
			 }
		 }													
		 $log .= "Forecast is : ($wx[forecastCode]) $wx[forecast] <br/>";
		 $result->free();
	}
	// Debug
	$functionStop = microtime(true);
	$executionTime = round($functionStop - $functionStart,5);
	$log .= "Building the forecast content took: " . $executionTime . " seconds<br/>";	
	//
	// TODO - Refine the forecast message for the time of day
	//
	//
	// Build METAR content
	$M_time = $_SESSION[dateMETAR] . "Z ";
	$M_wind .= str_pad($wx[avgbearing], 3, "0", STR_PAD_LEFT) . " ";
	$M_wind .= str_pad($wx[wlatest], 2, "0", STR_PAD_LEFT);
	$M_wind .= "G" . str_pad($wx[wgust], 2, "0", STR_PAD_LEFT) . "KT ";
	$M_cloud = $wx[cloudCoverMETAR] . $wx[cloudbaseMETAR];	
	$M_temp = $wx[temp] . "/" . $wx[dew];	
	//
	// Generate METAR
	$wx[METAR] = $M_time . " " . $M_wind . $wx[metarWx] . " " . $M_cloud . " " . $M_temp . " " . $wx[press] . $wx[pressunit];
	$wx[METAR] .= " " . $wx[pressureTrend] . " (" . $wx[presstrendval] . ")";
	//
	// Write the results to the database
	$functionStart = microtime(true);
	$wx[updateTime] = $_SESSION[datetimeNow];
	ksort($wx);
	foreach ($wx as $field => $value) {
		$query = "INSERT INTO $config[latestTable] (field, value) VALUES ('$field','$value') ON DUPLICATE KEY UPDATE value = '$value'";
		$con->query($query);
	}
	// Debug
	$functionStop = microtime(true);
	$executionTime = round($functionStop - $functionStart,5);
	$log .= "Writing the Data took: " . $executionTime . " seconds<br/>";
	if ($config[wxOutput] == "false") {
		return $wx;
	} else {
		wxOutput();	
	}
}
//
// Output Function
//
function wxOutput() {
	global $log, $errMsgs, $config, $station, $con, $wx;
	// Page content
	echo "<div id='alerts' title='$wx[alertDescription]'>";
		echo "<a href='wxAlerts'><img src='$config[siteIconPath]Alert_$wx[alertStatus].png'></a>";
	echo "</div>";
	echo "<div id='weather'>";
		echo "<strong>Current Conditions: </strong>$wx[cloudCover], $wx[wxText], $wx[rainText]. "; 
		echo "Temperature $wx[temp]$wx[tempunit], dewpoint $wx[dew]$wx[tempunit] and humidity at $wx[hum]%.";
		echo " Wind is $wx[windDirChange] $wx[windDirectionFull] at $wx[wlatest] $config[windUnit]";
		echo " gusting $wx[wgust] $config[windUnit].";
		echo " Pressure is $wx[pressureTrend] at $wx[press] $config[pressUnit]";
		echo " with $wx[presstrendval] $config[pressUnit] change in the last 3 hours. ";
		echo "The <strong>forecast</strong> to $_SESSION[Fdatetime6h] is $wx[forecast].";
	echo "</div>";
	echo "<div id='log'>";
		echo $log;
	echo "</div>";
	
}

	
?>
<script>
$(document).ready(function() {
	$('#weather').find($('<div />', { class: 'loader' })).remove();
	weatherFunctions();
});
</script>
