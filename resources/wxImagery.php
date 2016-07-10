<?php
// ###############################################
// #
// # 				Weather Imagaery 
// #
// ###############################################
//
	global $config, $con, $wx;
	require 'Config.php';																// Get the Config File
	loadConfig();																		// Load the site configuration
	// Process Request
	if($_POST) {
		if ($_POST[image] == "radarChart") {
			$image = $config[radarChart];
			$controlOutput .= "<a href='surfaceChart'>Surface Pressure</a>";
			$controlOutput .= "<a href='surfacePrognosis'>Surface Prognosis</a>";
		} else if ($_POST[image] == "surfacePrognosis") {
			$image = $config[surfacePrognosis];
			$controlOutput .= "<a href='surfaceChart'>Surface Pressure</a>";
    		$controlOutput .= "<a href='radarChart'>Radar Chart</a>";
		} else if ($_POST[image] == "surfaceChart") {
			$image = $config[surfaceChart];
			$controlOutput .= "<a href='surfacePrognosis'>Surface Prognosis</a>";
    		$controlOutput .= "<a href='radarChart'>Radar Chart</a>";
		}
	} else {
		$image = $config[surfaceChart];
		$controlOutput .= "<a href='surfacePrognosis'>Surface Prognosis</a>";
    	$controlOutput .= "<a href='radarChart'>Radar Chart</a>";
	}
	// Build Image
	$imageOutput = "<img id='thumb-inside' src='$image' data-mode='inside' data-zoomable='true'>"
	
	
	
	
//
// Output
?>
<div id="imageryControls">
    <?php echo $controlOutput; ?>
</div>
<div id="imageryMain">
	<?php echo $imageOutput; ?>
</div>
    
<script type="text/javascript" src="scripts/Event.js"></script>
<script type="text/javascript" src="scripts/Magnifier.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$('#content').find($('<div />', { class: 'loader' })).remove();
	// Magnifier
	var evt = new Event(),
    m = new Magnifier(evt);
	m.attach({
		thumb: '#thumb-inside',
		large: '<?php echo $image ?>',
		mode: 'inside',
		zoom: 2,
		zoomable: true
	});
	imageryFunctions();
});
</script>