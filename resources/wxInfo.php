<?php
session_start(); 
// ###############################################
// #
// # 				 Info Page  
// #
// ###############################################
//
// Setup script
	$functionStart = microtime(true);													// Start Timer for execution
	global $config, $con, $wx;															// Set the core arrays
	require 'Config.php';															// Get the Config File
	loadConfig();																		// Load the site configuration
//
// Output

echo "Current Temp is $_SESSION[temp]$_SESSION[tempunit]<br/>";
?>

<div id="stationInfo">
	<div id="stationAttribs">
		<h4>Station Atttributes</h4>
        <?php echo $station[attribs] ; ?>
    </div> 
    <div id="locationAttribs">
		<h4>Location Atttributes</h4>
        <?php echo $station[locationAttr] ; ?>
    </div>
    <div id="stationRecords">
		<h4>Station Records</h4>
        <?php echo $station[records] ; ?>
    </div>
    <div id="astronomy">
		<h4>Station Information</h4>
        <?php echo $wx[astronomy] ; ?>
    </div>
</div>
<div id="infoNotes">

<h4>Station Rules</h4>
Exposure ratings relate to the site of the temperature and rainfall instruments only, which should ideally be at ground level. Sensors for sunshine, wind speed etc are best exposed as freely as possible, and rooftop or mast mountings are usually preferable.
Exposure guidelines are based on a multiple of the height h of the obstruction above the sensor height; the standard is a minimum distance of twice the height (2h). Thus for a raingauge at 30 cm above ground, a building 5 m high should be at least 9.4 m distant (5 m less 0.3 m, x 2), and a 10 m building should be at least 17 m from a thermometer screen (10 m less 1.5 m, x2).

Mearsurements of air temperature - STANDARD INSTRUMENTS in this context means: Calibrated mercury-in-glass thermometers or calibrated electronic temperature sensors.

Measurements of rainfall - STANDARD INSTRUMENTS in this context means: Standard-pattern (Snowdon or Met Office Mk II pattern) ''five-inch'' copper raingauge, with deep funnel, the rim of the gauge level and mounted at 30 cm above ground level, meeting the minimum exposure requirement of being at least 'twice the height'' of the obstacle away from the obstacle.

UCZ - UCZ descriptions as defined by the World Meteorological Organisation (WMO-No.8, 7th Edition)


Rating Calculations
Each site is automatically allocated a 'site rating'' based on the observing location attributes entries submitted on site registration. The system is based on the quality and exposure of the temperature and rainfall data:
5* = E5, T=A, R=A
4* = E >= 3, T=A, R=A
3* = E >= 3, T[=A,B or C], R[=A,B or C]
2* = E >= 1, T[=Any], R[=Any]
1* = E =0,1,R or U, T[=Any], R[=Any]
(Where E = Exposure, T = Temperature, and R = Rainfall, and each of these are described in Location Attributes).
If temperature is measured at a site, but not rainfall, the site rating will be based on the quality and exposure of the temperature data alone. If rainfall is measured at a site, but not temperature, the site rating will be based on the quality and exposure of the rainfall data alone.
If there is no temperature or rainfall data, the site will be classed as 1*


    
</div>
    
    
<script>
$(document).ready(function() {
	infoFunctions();
});
</script>