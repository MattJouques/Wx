<?php 
// ####################################################
// # 
// #						wxRules Engine
// #
// ####################################################
// 
//
function getRules($ruleRequest = "Live") {
	global $events, $config, $con, $ref_ids, $wx, $request;
	//
	// Build the query
	//
	if ($ruleRequest == "View") {
		$query = "SELECT id, wxRuleRating, wxCode, ruleName, Icon FROM " . $config[rulesTable];
		$query .= " WHERE id > 0";
		foreach ($request as $field => $value) {
			switch ($field) {
			case "table" : break;
			case "dbTable" : break;
			case "filter" : break;
			case "filterSql" : break;
			case "filterSql" : break;
			case "requestSql" : break;
			case "request" : break;
			case "wxRuleRating" : if ($value != "NULL") { $query .= " AND wxRuleRating >= '$value'";} break;
			case "wxCode" : if ($value != "NULL") { $query .= " AND wxCode LIKE '%$value%'";} break;
			case "loPrecipRate" : if ($value != "NULL") { $query .= " AND loPrecipRate >= $value";} break;
			case "hiPrecipRate" : if ($value != "NULL") { $query .= " AND hiPrecipRate <= $value";} break;
			case "loPressureTrend" : if ($value != "NULL") { $query .= " AND loPressureTrend >= $value";} break;
			case "hiPressureTrend" : if ($value != "NULL") { $query .= " AND hiPressureTrend <= $value";} break;
			case "loHumidity" : if ($value != "NULL") { $query .= " AND loHumidity >= $value";} break;
			case "hiHumidity" : if ($value != "NULL") { $query .= " AND hiHumidity <= $value";} break;
			case "loTemp" : if ($value != "NULL") { $query .= " AND loTemp >= $value";} break;
			case "hiTemp" : if ($value != "NULL") { $query .= " AND hiTemp <= $value";} break;
			case "loDewDiff" : if ($value != "NULL") { $query .= " AND loDewDiff >= $value";} break;
			case "hiDewDiff" : if ($value != "NULL") { $query .= " AND hiDewDiff <= $value";} break;
			case "windDir" : if ($value != "NULL") { $query .= " AND windDir LIKE '%$value%'";} break;
			case "loWindAvg" : if ($value != "NULL") { $query .= " AND loWindAvg >= $value";} break;
			case "hiWindAvg" : if ($value != "NULL") { $query .= " AND hiWindAvg <= $value";} break;
			case "loWindGust" : if ($value != "NULL") { $query .= " AND loWindGust >= $value";} break;
			case "hiWindGust" : if ($value != "NULL") { $query .= " AND hiWindGust <= $value";} break;
			case "cloudCover" : if ($value != "NULL") { $query .= " AND cloudCover LIKE '%$value%'";} break;
			case "loCloudBase" : if ($value != "NULL") { $query .= " AND loCloudBase >= $value";} break;
			case "hiCloudBase" : if ($value != "NULL") { $query .= " AND hiCloudBase <= $value";} break;
			default : $query .= "id > 0";
			}
		}
	} else {
		if (empty($wx)) {
			$query = "SELECT * FROM $config[latestTable]";
			if ($result = $con->query($query)) {
				while ($row = $result->fetch_assoc()) {	
					$wx[$row[field]] = $row[value];
				}
			}
		}
		$query = "SELECT id, wxRuleRating, wxCode, ruleName, Icon FROM " . $config[rulesTable];
		$query .= " WHERE ";
		// Cloud cover
		$query .= "(cloudCover IS NULL OR cloudCover = '$wx[cloudCoverMETAR]')";
		$query .= " AND (loCloudBase IS NULL OR loCloudBase < $wx[cloudbase])";
		$query .= " AND (hiCloudBase IS NULL OR hiCloudBase > $wx[cloudbase])";
		// Temperature
		$query .= " AND (loTemp IS NULL OR loTemp < $wx[temp])";
		$query .= " AND (hiTemp IS NULL OR hiTemp > $wx[temp])";
		// Dew Point Difference
		$query .= " AND (loDewDiff IS NULL OR loDewDiff < $wx[dewDiff])";
		$query .= " AND (hiDewDiff IS NULL OR hiDewDiff > $wx[dewDiff])";
		// Humidity
		$query .= " AND (loHumidity IS NULL OR loHumidity < $wx[hum])";
		$query .= " AND (hiHumidity IS NULL OR hiHumidity > $wx[hum])";
		// Pressure
		$query .= " AND (loPressureTrend IS NULL OR loPressureTrend < $wx[presstrendval])";
		$query .= " AND (hiPressureTrend IS NULL OR hiPressureTrend > $wx[presstrendval])";
		// Wind
		$query .= " AND (loWindAvg IS NULL OR loWindAvg < $wx[wspeed])";
		$query .= " AND (hiWindAvg IS NULL OR hiWindAvg > $wx[wspeed])";
		$query .= " AND (loWindGust IS NULL OR loWindGust < $wx[wgust])";
		$query .= " AND (hiWindGust IS NULL OR hiWindGust > $wx[wgust])";
		$query .= " AND (windDir IS NULL OR windDir < $wx[avgbearing])";						// TODO - Fix this
		$query .= " AND (windDirChange IS NULL OR windDirChange = '$wx[windDirChange]')";
		// Precipitation
		$query .= " AND (loPrecipRate IS NULL OR loPrecipRate < $wx[rrate])";
		$query .= " AND (hiPrecipRate IS NULL OR hiPrecipRate > $wx[rrate])";
	}
	//
	// Process rules
	//
	$valid_Rules = array();
	if ($result = $con->query($query)) {
		$numRules = $result->num_rows;
		if ($numRules > 1) {
			$count = 0;
			while ($row = $result->fetch_assoc()) {
				if ($count >= $config[resultsPerPage]) {
					$valid_Rules[$count] = array(NULL, "!", "!", "Too Many results - Apply more filters", "!");
					break;
				}
				$rules[$row[id]] = $row;
				$ruleRating[$row[id]] = $row[wxRuleRating];
				foreach ($row as $field => $value) {
					$temp[$field] = $value;
				}
				$valid_Rules[$row[id]] = $temp;
				$count++;
			}
			$topRuleRating = max($ruleRating);
			$topRule = array_search($topRuleRating, $ruleRating);
			$rule = $rules[$topRule];
		} else {
			while ($row = $result->fetch_assoc()) {
				$rule = $row;
				$valid_Rules = $row;
			}
		}
		if ($numRules == 0) {
			$events .= "Missing rules for the conditions logged";
		} else if ($numRules > 1) {
			$events .= "Conflicting Rules for conditions logged";
			$ref_ids = array_keys($rules);
		}
		if ($ruleRequest == "View") {
			return $valid_Rules;
		} else if ($ruleRequest == "Live") {
			logAdminEvents();
			return $rule;
		} else if ($ruleRequest == "Active") {
			return $valid_Rules;
		}
	}
}