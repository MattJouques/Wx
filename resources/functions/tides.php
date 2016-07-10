<?php 
// ###############################################
// #
// # 				Tidal Calculations  
// #
// ###############################################
// #
//
// 
function getTides() {
	//
	global $log, $errMsgs, $config, $station, $con, $wx;	
	//
	$functionStart = microtime(true);
	// Check the status of tide feed & Get the latest tidal data
	if (getTideData) {
		echo "Tide Data Worked";
	}
	// Apply revised forecast to prediction
	
	// write the forecast updates to the database
	
	
}
//
// Get the latest Tidal Data
//
function getTideData() {
	//
	global $log, $errMsgs, $config, $station, $con, $wx;
	//
	// Check the database for the date of the last tidal data update
	$tidalEvents = 0;
	$query = "SELECT updateDateTime FROM $config[tideTable] WHERE date = $_SESSION[dateToday]";
	if($result = $con->query($query)){
		while ($row = $result->fetch_assoc()) {
			$lastUpdate = $row['updateDateTime'];
			$tidalEvents++;
			
		}
	$result->free();
	}
	echo  "tide data last updated at: $lastUpdate with $tidalEvents events logged<br/>";
	
	
	// Get the tide data from the feed if required
	
	// write latest tide data to the database
	
	// Retrive the data from the the Db
	
	
	
	
	
}








//
// Get Tidal Data
//
function oldTide() {


	$count = 1;
	if ($config[includeTides] == true) {
		$tidalEvents = 0;
		$query = "SELECT * FROM $config[latestTable] WHERE field LIKE '%tide%'";
		if($result = $con->query($query)){
			while ($row = $result->fetch_assoc()) {
				$wx[$row['field']] = $row['value'];
				if (strpos($row['field'],"Height")) {
					$tidalEvents++;
					//echo "($tidalEvents) tide height (" . $row['value'] . ")";
				}
			}
		$result->free();
		}
		if ($xfeedDate != $_SESSION[dateToday]) {													// Only get this once a day
			$tidefeedRequired = true;
			$wx[lastTideH] = $wx['tide'.$tidalEvents.'Height'];
			$wx[lastTideT] = $wx['tide'.$tidalEvents.'Time'];
		}
		// Get Latest Tide Data
		if ($tidefeedRequired == true) {
			$rawTideFeed = file_get_html($station[stnLocalTideFeed]);
			if ($rawTideFeed) {
				$rawTide = str_ireplace("br/", "br", $rawTideFeed);
				$rawTideFields = array();
				$rawTideFields = explode("br", $rawTide);
				$count = 1;
				foreach ($rawTideFields as $field => $value) {
					$value = ltrim($value,'&gt;');													// Remove unwanted prefix characters
					$value = rtrim($value,'&lt;');													// Remove unwanted suffix characters
					$value = trim($value);
					if (strpos($value,'High') || strpos($value,'Low') ) { 
						$tideItems[$count] = $value;
						$count++;
					}
				}
				$tidalEvents = count($tideItems);
				foreach ($tideItems as $itemsField => $itemsValue) {
					$value = strip_tags($itemsValue);
					$value = trim($value);
					$time = trim(substr($value,0,5));												// Obtain tide time from feed item
					$height = trim(substr($value,-10,4));											// Obtain tide height from feed item
					$tideref = "tide" . $itemsField;
					$wx[$tideref."Time"] = $time . ":00";
					$wx[$tideref."Height"] = $height;
				}
			}
		}
		
		// Calculate the periods and ranges of tide
		$current = 1;
		while ($current <= $tidalEvents) {
			// Set the current event details
			$tideTime = new DateTime($_SESSION[dateToday] . " " . $wx['tide'.$current.'Time']);			// Tide event Time
			$tideHeight = $wx['tide'.$current.'Height'];												// Tide Event Height
			// Set the previous event details
			$previous = $current -1;
			if ($current == 1) {
				$prev_tideHeight = round($wx[lastTideH],1);
				$prev_tideTime = new DateTime($_SESSION[dateYesterday] . " " . $wx[lastTideT]);
			} else {
				$prev_tideHeight = round($wx['tide'.$previous.'Height'],1);
				$prev_tideTime = new DateTime($_SESSION[dateToday] . " " . $wx['tide'.$previous.'Time']);
				
			}
			// Calculate the Range												
			$tideRangeH = abs($prev_tideHeight-$tideHeight);											// Get the Range Height
			$diff_RangeT = $prev_tideTime->diff($tideTime);												// Calculate the Time difference for the Range
			$tideRangeT = ($diff_RangeT->h * 60) + $diff_RangeT->i;										// Get the Range Minutes
			$intervalMins = round($tideRangeT / 6);
				echo "(Previous) : " . $prev_tideTime->format('D H:i') . " at $prev_tideHeight m, Range $tideRangeH with Interval of $intervalMins mins <br/>";
			// Calculate time at intevals through period
			$m[0] = ($prev_tideTime->format('H') * 60) + $prev_tideTime->format('i');					// Starting base
			$m_count = 1;
			while ($m_count <=6) {
				$m_previous = $m_count - 1;
				$m[$m_count] = $m[$m_previous] + $intervalMins;
				if ($m[$m_count] > 1440) {
					$m[$m_count] = $m[$m_count] - 1440;
				}
				list($m_hrs, $m_mins) = explode('.', $m[$m_count] / 60);
				$m_mins = str_pad($m_mins, 20, "0", STR_PAD_RIGHT);
				$m_mins = trim(substr($m_mins,0,3));
				$m_mins = round((str_pad($m_mins, 5, "0.", STR_PAD_LEFT) *60));
				$t[$m_count] = str_pad($m_hrs, 2, "0", STR_PAD_LEFT) . ":" . str_pad($m_mins, 2, "0", STR_PAD_LEFT);
				$m_count++;
			}
			$t[6] = $tideTime->format('H:i');
			// Calulate Heights at intervals															// TODO - Use Harmonic Constants
			if ( $prev_tideHeight < $tideHeight ) {
				$h1 = round($prev_tideHeight + ($tideRangeH * 0.10),1);
				$h2 = round($h1 + ($tideRangeH * 0.15),1); 
				$h3 = round($h2 + ($tideRangeH * 0.25),1);
				$h4 = round($h3 + ($tideRangeH * 0.25),1);
				$h5 = round($h4 + ($tideRangeH * 0.15),1);
			} else {
				$h1 = round($prev_tideHeight - ($tideRangeH * 0.10),1);
				$h2 = round($h1 - ($tideRangeH * 0.15),1); 
				$h3 = round($h2 - ($tideRangeH * 0.25),1);
				$h4 = round($h3 - ($tideRangeH * 0.25),1);
				$h5 = round($h4 - ($tideRangeH * 0.15),1);
			}
			$h6 = $tideHeight;
			//echo "Time " . $prev_tideTime->format('H:i') . " ($m0 mins) the height will be $tideHeight<br/>";
			
			echo "At $t[1] the predicted tide is for $h1 m and forecast is $p1 m<br/>";
			echo "At $t[2] the predicted tide is for $h2 m and forecast is $p2 m<br/>";
			echo "At $t[3] the predicted tide is for $h3 m and forecast is $p3 m<br/>";
			echo "At $t[4] the predicted tide is for $h4 m and forecast is $p4 m<br/>";
			echo "At $t[5] the predicted tide is for $h5 m and forecast is $p5 m<br/>";
			echo "At $t[6] the predicted tide is for $h6 m and forecast is $p6 m<br/>";

			$current++;	
		}
	}
}


?>