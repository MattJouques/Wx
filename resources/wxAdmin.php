<?php
session_start(); 
// ###############################################
// #
// # 				 Admin Page 
// #
// ###############################################
//
// Setup script
	$functionStart = microtime(true);													// Start Timer for execution
	global $config, $con, $wx;															// Set the core arrays
	require 'Config.php';																// Get the Config File
	loadConfig();																		// Load the site configuration
//
// Check authentication
	if ($_SESSION['AuthState']!=true) {													// If the session authenticated
		if($_POST['authcode'] != $_SESSION['authKey']) {
			echo "<div id=\"authentication\">\n";
			echo "<div id=\"authenticationImg\">\n";
			echo "<img src=\"$config[siteIconPath]Restricted.png\">";
			echo "</div>";
			echo "<div id=\"authenticationContent\">\n";
			echo "Authentication is required to use the admin functions<br/>Please enter the authorisation code<br/>";
			echo "<form name=\"authForm\" class=\"auth\" method=\"post\" action=\"resources/wxAdmin.php\">";
			echo "<input type=\"password\" name=\"authcode\" placeholder=\"Authorisation Code\" size=\"50\" /><br/>";
			echo "<input type=\"submit\" value=\"Authenticate\" />";
			echo "</form>";
			echo "</div>";
			echo "</div>";
			echo "<script language=\"JavaScript\">$(document).ready(function() { authFunctions(); });</script>";
			die();
		} else {
			$_SESSION['AuthState']=true;
		}
	}
//	
// Option Selectors
//
	// percent Select Items
	$count = 0;
	$percentOptions .= "<option value=\"NULL\">NULL</option>";
	while ($count < 110) {
		$percentOptions .= "<option value=\"" . $count . "\">" . $count . "%</option>";
		$count = $count + 10;
	}
	//
	// Weather Codes
	$wxQuery =  "SELECT * FROM $config[referenceTable] WHERE category = 'wxCode'";
	$wxSelectOption .= "<option value=\"NULL\">NULL</option>";
	if ($result = $con->query($wxQuery)) {											// Run the Query
		while ($row = $result->fetch_assoc()) {										// Get Results
			$wxSelectOption .= "<option value=\"" . $row['code'] . "\">" . $row['code'] . "</option>";
		}
	$result->free();
	}
	// Rate Select Items
	$count = 0.00;
	$rateOptions .= "<option value=\"NULL\">NULL</option>";
	while ($count < 5) {
		$rateOptions .= "<option value=\"" . $count . "\">" . $count . "</option>";
		$count = $count + 0.1;
	}
	//
	// Pressure Select Items
	$count = -10;
	$pressureSelectOption .= "<option value=\"NULL\">NULL</option>";
	while ($count < 10.5) {
		$pressureSelectOption .= "<option value=\"" . $count . "\">" . $count . "</option>";
		$count = $count + 0.5;
	}
	//
	// Temperature Select Items
	$count = -10;
	$tempSelectOption .= "<option value=\"NULL\">NULL</option>";
	while ($count < 41) {
		if($count < 10) {
			$tempSelectOption .= "<option value=\"" . $count . "\">" . $count . "</option>";
			$count++;
		} else {
			$tempSelectOption .= "<option value=\"" . $count . "\">" . $count . "</option>";
			$count = $count + 5;
		}
	}
	//
	// Direction Items
	$direction .= "<option value=\"NULL\">NULL</option>";
	$direction .= "<option value=\"Northerly\">Northerly</option>";
	$direction .= "<option value=\"North Easterly\">North Easterly</option>";
	$direction .= "<option value=\"Easterly\">Easterly</option>";
	$direction .= "<option value=\"South Easterly\">South Easterly</option>";
	$direction .= "<option value=\"Southerly\">Southerly</option>";
	$direction .= "<option value=\"South Westerly\">South Westerly</option>";
	$direction .= "<option value=\"Westerly\">Westerly</option>";
	$direction .= "<option value=\"North Westerly\">North Westerly</option>";
	//
	// Direction Change Items
	$directionChange .= "<option value=\"NULL\">NULL</option>";
	$directionChange .= "<option value=\"Backing\">Backing</option>";
	$directionChange .= "<option value=\"Veering\">Veering</option>";
	$directionChange .= "<option value=\"Variable\">Variable</option>";
	$directionChange .= "<option value=\"Calm\">Calm</option>";
	//
	// Speed Select Items
	$count = 0;
	$speedSelectOption .= "<option value=\"NULL\">NULL</option>";
	while ($count < 105) {
		$speedSelectOption .= "<option value=\"" . $count . "\">" . $count . "</option>";
		if($count < 10) {
			$count = $count + 0.5;
		} else {
			$count = $count + 10;
		}
	}
	//
	// cloudBase Select Items
	$count = 000;
	$cloudbaseSelectOption .= "<option value=\"NULL\">NULL</option>";
	while ($count < 360) {
		$cloudbaseSelectOption .= "<option value=\"" . $count . "\">" . $count . "</option>";
		if($count < 105) {
			$count = $count + 5;			// TODO - Padd the leading zero
		} else {
			$count = $count + 10;
		}
	}
	//
	// Cloud Cover Select Items
	$cloudQuery =  "SELECT * FROM reference WHERE category = 'cloud'";
		$cloudcoverSelectOption .= "<option value=\"NULL\">NULL</option>";
	if ($result = $con->query($cloudQuery)) {										// Run the Query
		while ($row = $result->fetch_assoc()) {										// Get Results
			$cloudcoverSelectOption .= "<option value=\"" . $row['key'] . "\">" . $row['value'] . "</option>";
		}
	$result->free();
	}
	


// Output
?>
<div id="adminInterface">
	<div id="adminCommands">
    <h4>Admin interface</h4>
    <form name="adminCmdForm" class="ajax" method="post" action="resources/functions/admin.php">
    <div class="controlsGroup">
        Select : <select name="table">
                        <option value="Log" class="ajax">Admin Messages</option>
                        <option value="History" class="ajax">Weather History</option>
                        <option value="Observations" class="ajax">Weather Observations</option>
                        <option value="Rules" class="ajax">Weather Rules</option>
                        <option value="Station" class="ajax">Station Info</option>
                        <option value="Config" class="ajax">Configuration</option>
                </select>
        <input type="hidden" name="request" value="view" />       
        <input type="submit" value="View" />
    </div>
    </form>
        
        <hr/>
    	<h4>Weather Rule Filters</h4>
        <form name="wxRules" class="ajax" method="post" action="resources/functions/admin.php">
        <input type="hidden" name="request" value="view" />
        <input type="hidden" name="table" value="Rules" />
        <input type="hidden" name="filter" value="True" />
        <div class="controlsGroup">
        	<h5>Rule Details</h5>
            Rating: <select name="wxRuleRating"><?php echo $percentOptions; ?></select>
            Weather: <select name="wxCode"><?php echo $wxSelectOption; ?></select>   
		</div>
        <div class="controlsGroup">
        	<h5>Precipitation</h5>		
            Rate Range:	<select name="loPrecipRate"><?php echo $rateOptions; ?></select>
            			<select name="hiPrecipRate"><?php echo $rateOptions; ?></select>
		</div>
        <div class="controlsGroup">
        	<h5>Pressure</h5>
            Pressure Range:	<select name="loPressureTrend"><?php echo $pressureSelectOption; ?></select>
            			<select name="hiPressureTrend"><?php echo $pressureSelectOption; ?></select>
		</div>
        <div class="controlsGroup">
        	<h5>Humidity</h5>
            Humidity Range: <select name="loHumidity"><?php echo $percentOptions; ?></select>
							<select name="hiHumidity"><?php echo $percentOptions; ?></select>
		</div>
        <div class="controlsGroup">
        	<h5>Temperature & Dew Point</h5>
            Temp Range: <select name="loTemp"><?php echo $tempSelectOption; ?></select>
            			<select name="hiTemp"><?php echo $tempSelectOption; ?></select><br/>
            Dew Range: 	<select name="loDewDiff"><?php echo $percentOptions; ?></select>
            			<select name="hiDewDiff"><?php echo $percentOptions; ?></select>
		</div>
        <div class="controlsGroup">
        	<h5>Wind</h5>
            Dir: <select name="windDir"><?php echo $direction; ?></select>
            Change: <select name="loHumidity"><?php echo $directionChange; ?></select><br/>
            Avg Range: <select name="loWindAvg"><?php echo $speedSelectOption; ?></select>
            			<select name="hiWindAvg"><?php echo $speedSelectOption; ?></select><br/>
            Gust Range: <select name="loWindGust"><?php echo $speedSelectOption; ?></select>
            			<select name="hiWindGust"><?php echo $speedSelectOption; ?></select>
		</div>
        <div class="controlsGroup">
        	<h5>Cloud</h5>
            Cover: <select name="cloudCover"><?php echo $cloudcoverSelectOption; ?></select><br/>
            Base Range: <select name="loCloudBase"><?php echo $cloudbaseSelectOption; ?></select>
            			<select name="hiCloudBase"><?php echo $cloudbaseSelectOption; ?></select>
		</div>
        	<input type="submit" value="Filter" />
		</form>
    </div><!-- adminCommands -->
    <div id="adminResponse"></div>
</div><!-- adminInterface -->


<!-- Load page specific Scripts -->
<script>
$(document).ready(function() {
	wxAdminFunctions();
});
</script>
