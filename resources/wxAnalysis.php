<?php
// ###############################################
// #
// # 				Weather Analysis  
// #
// ###############################################
//
//$functionStart = microtime(true);															// Start Timer for execution
global $log, $config, $request, $con, $wx;													// Set the core arrays
$table = $config[tablePrefix] . "History";
require 'Config.php';																		// Get the Config File
loadConfig();																				// Load the site configuration
// 
//
?>
    <script type="text/javascript">
		$('#visualization').append($('<div />', { class: 'loader' }));
	</script>
	
	<script type="text/javascript"
        src='https://www.google.com/jsapi?autoload={"modules":[{"name":"visualization","version":"1", "callback":"scriptsLoaded"}]}'>
    </script>
    <script type="text/javascript">
      
	  
	  // Listeners
	  
	  
	  
	  // Functions
      function drawVisualization() {
        var wrap = new google.visualization.ChartWrapper({
           'chartType':'LineChart',
           'dataSourceUrl':'http://spreadsheets.google.com/tq?key=pCQbetd-CptGXxxQIG7VFIQ&pub=1',
           'containerId':'visualization',
           'query':'SELECT A,D WHERE D > 100 ORDER BY D',
           'options': {'title':'Population Density (people/km^2)', 'legend':'none'}
           });
         wrap.draw();
		 // Listeners
		 google.visualization.events.addListener(wrap, 'ready', onReady);
		 
		 
      }
	  
	  function scriptsLoaded() {
		  //alert('scriptsLoaded');
		  drawVisualization();
	  }
	  
	  function onReady() {
		  //alert('ready');
		  $('#visualization').find($('<div />', { class: 'loader' })).remove();
	  }
	  
    </script>

<h4>Weather Analysis <?php echo $titleprefix ; ?></h4>
<div id="dashboard"></div>
<div id="visualization" style="height: 400px; width: 400px;"></div>


<!-- Page Specific Javascript -->
<script>
$(document).ready(function() {
	analysisFunctions();
});
</script>
