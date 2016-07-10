<?php
session_start();
// ###############################################
// #
// # 				Admin Interface 
// #
// ###############################################		
//
// Setup script
$functionStart = microtime(true);															// Start Timer for execution
global $log, $config, $request, $con, $wx;													// Set the core arrays
require 'Config.php';																	// Get the Config File
loadConfig();																				// Load the site configuration
// 
// Load Scripts
//



?>
<!-- scripts -->
	<script src="scripts/jquery.min.js" type="text/javascript"></script>
  	<script src="scripts/steelseries_tween.min.js" type="text/javascript"></script>
  	<script src="scripts/language.min.js" type="text/javascript"></script>
  	<script src="scripts/gauges.js" type="text/javascript"></script>
	<script src="scripts/windrose.js" type="text/javascript"></script>
  	<script src="scripts/RGraph.common.core.min.js" type="text/javascript"></script>
  	<script src="scripts/RGraph.rose.min.js" type="text/javascript"></script>
	
<!-- Style Items-->
	<style type=text/css>
		@media(-webkit-min-device-pixel-ratio: 2) {}
	</style>


<!--Status Message-->
<div id="Status">
        <canvas id="canvas_led" width="25" height="25"></canvas>
    	<canvas id="canvas_timer" width="70" height="25"></canvas>
        <br/>to next update:
</div>
<!--No Script Message -->
<noscript>
      <h2 style="color:red; text-align:center">&gt;&gt;This page requires JavaScript enabling in your browser.&lt;&lt;<br>&gt;&gt;Please enable scripting it to enjoy this site at its best.&lt;&lt;</h2>
</noscript>

<!-- Display Weather Gauges-->
  <div class="row">
    <div class="gauge">
      <div id="tip_0">
        <canvas id="canvas_temp" class="gaugeSizeSml"></canvas>
      </div>
      <input id="rad_temp1" type="radio" name="rad_temp" value="out" checked onclick="gauges.doTemp(this);"><label id="lab_temp1" for="rad_temp1">Outside</label>
      <input id="rad_temp2" type="radio" name="rad_temp" value="in" onclick="gauges.doTemp(this);"><label id="lab_temp2" for="rad_temp2">Inside</label>
    </div>
    <div class="gauge">
      <div id="tip_1">
        <canvas id="canvas_dew" class="gaugeSizeSml"></canvas>
      </div>
      <input id="rad_dew1" type="radio" name="rad_dew" value="dew" onclick="gauges.doDew(this);"><label id="lab_dew1" for="rad_dew1">Dew Point</label>
      <input id="rad_dew2" type="radio" name="rad_dew" value="app" checked onclick="gauges.doDew(this);"><label id="lab_dew2" for="rad_dew2">Apparent</label>
      <br>
      <input id="rad_dew3" type="radio" name="rad_dew" value="wnd" onclick="gauges.doDew(this);"><label id="lab_dew3" for="rad_dew3">Wind Chill</label>
      <input id="rad_dew4" type="radio" name="rad_dew" value="hea" onclick="gauges.doDew(this);"><label id="lab_dew4" for="rad_dew4">Heat Index</label>
      <br>
      <input id="rad_dew5" type="radio" name="rad_dew" value="hum" onclick="gauges.doDew(this);"><label id="lab_dew5" for="rad_dew5">Humidex</label>
    </div>
    <div class="gauge">
      <div id="tip_4">
        <canvas id="canvas_hum" class="gaugeSizeSml"></canvas>
      </div>
      <input id="rad_hum1" type="radio" name="rad_hum" value="out" checked onclick="gauges.doHum(this);"><label id="lab_hum1" for="rad_hum1">Outside</label>
      <input id="rad_hum2" type="radio" name="rad_hum" value="in" onclick="gauges.doHum(this);"><label id="lab_hum2" for="rad_hum2">Inside</label>
    </div>
  </div>
  <div class="row">
    <div id="tip_6" class="gauge">
      <canvas id="canvas_wind" class="gaugeSizeSml"></canvas>
    </div>
    <div id="tip_7" class="gauge">
      <canvas id="canvas_dir" class="gaugeSizeSml"></canvas>
    </div>
    <div id="tip_10" class="gauge">
      <canvas id="canvas_rose" class="gaugeSizeSml"></canvas>
    </div>
  </div>
  <div class="row">
    <div id="tip_5" class="gauge">
      <canvas id="canvas_baro" class="gaugeSizeSml"></canvas>
    </div>
    <div id="tip_2" class="gauge">
      <canvas id="canvas_rain" class="gaugeSizeSml"></canvas>
    </div>
    <div id="tip_3" class="gauge">
      <canvas id="canvas_rrate" class="gaugeSizeSml"></canvas>
    </div>
  </div>
  <div class="row">
    <div id="tip_8" class="gauge">
      <canvas id="canvas_uv" class="gaugeSizeSml"></canvas>
    </div>
    <div id="tip_9" class="gauge">
      <canvas id="canvas_solar" class="gaugeSizeSml"></canvas>
    </div>
  </div>

  <div class="content">
    <div class="unitsTable">
      <div style="display:table-row">
        <div id="temperature" class="cellRight">
          <span id="lang_temperature">Temperature</span>:
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsTemp1" type="radio" name="rad_unitsTemp" value="C" checked onclick="gauges.setUnits(this);"><label id="lab_unitsTemp1" for="rad_unitsTemp1">&deg;C</label>
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsTemp2" type="radio" name="rad_unitsTemp" value="F" onclick="gauges.setUnits(this);"><label id="lab_unitsTemp2" for="rad_unitsTemp2">&deg;F</label>
        </div>
      </div>
      <div style="display:table-row">
        <div id ="rainfall" class="cellRight">
          <span id="lang_rainfall">Rainfall</span>:
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsRain1" type="radio" name="rad_unitsRain" value="mm" checked onclick="gauges.setUnits(this);"><label id="lab_unitsRain1" for="rad_unitsRain1">mm</label>
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsRain2" type="radio" name="rad_unitsRain" value="in" onclick="gauges.setUnits(this);"><label id="lab_unitsRain2" for="rad_unitsRain2">Inch</label>
        </div>
      </div>
      <div style="display:table-row">
        <div id="pressure" class="cellRight">
          <span id="lang_pressure">Pressure</span>:
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsPress1" type="radio" name="rad_unitsPress" value="hPa" checked onclick="gauges.setUnits(this);"><label id="lab_unitsPress1" for="rad_unitsPress1">hPa</label>
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsPress2" type="radio" name="rad_unitsPress" value="inHg" onclick="gauges.setUnits(this);"><label id="lab_unitsPress2" for="rad_unitsPress2">inHg</label>
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsPress3" type="radio" name="rad_unitsPress" value="mb" onclick="gauges.setUnits(this);"><label id="lab_unitsPress3" for="rad_unitsPress3">mb</label>
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsPress4" type="radio" name="rad_unitsPress" value="kPa" onclick="gauges.setUnits(this);"><label id="lab_unitsPress4" for="rad_unitsPress4">kPa</label>
        </div>
      </div>
      <div style="display:table-row">
        <div id="wind" class="cellRight">
          <span id="lang_windSpeed">Wind Speed</span>:
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsWind4" type="radio" name="rad_unitsWind" value="km/h" checked onclick="gauges.setUnits(this);"><label id="lab_unitsWind4" for="rad_unitsWind4">km/h</label>
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsWind3" type="radio" name="rad_unitsWind" value="m/s" onclick="gauges.setUnits(this);"><label id="lab_unitsWind3" for="rad_unitsWind3">m/s</label>
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsWind1" type="radio" name="rad_unitsWind" value="mph" onclick="gauges.setUnits(this);"><label id="lab_unitsWind1" for="rad_unitsWind1">mph</label>
        </div>
        <div style="display:table-cell">
          <input id="rad_unitsWind2" type="radio" name="rad_unitsWind" value="kts" onclick="gauges.setUnits(this);"><label id="lab_unitsWind2" for="rad_unitsWind2">knots</label>
        </div>
      </div>
    </div>
    </div><!--gauges-->