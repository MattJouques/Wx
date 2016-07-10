<?php session_start(); 
$_SESSION['AuthState']=false;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<!-- Dependencies -->
<?php
	$functionStart = microtime(true);												// Start Timer for execution
	global $log, $errMsgs, $config, $con, $wx;										// Set the core arrays
	require 'resources/Config.php';													// Get the Config File
	loadConfig();																	// Load the site configuration
	wxHeader();
?>
<!-- HTML Head -->
<head>
	<title><?php echo $config[siteTitle];?></title>
<!-- Metadata -->
    <meta name='viewport' content='width=1094'>
    <meta name="keywords" content="weather data, weather, data, weather station">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<!-- Style Sheets -->
	<link href='css/wx.css' rel='stylesheet' type='text/css'>
<!-- javascripts -->
	<script src="scripts/jquery.min.js"></script>
    <script src="scripts/wx.js"></script>
</head>
<body>
    <!-- Main -->
    <div id="main">
    	<div id="header">
            <div class="wxLatest">
				<div id="wxIxon"><img src="<?php echo $config[wxIconPath] . $wx[wxIcon]; ?>.png"></div>
                <div id="title"><?php echo $config[siteTitle];?></div>
                <div id="metar"><?php echo $wx[METAR]; ?></div>
			</div><!-- wxLatest -->
            <div id="navigation">
                <div id="tab1"><a href="wxCurrent">Current</a></div>
                <div id="tab2"><a href="wxAnalysis">Analysis</a></div>
                <div id="tab3"><a href="wxImagery">Imagery</a></div>
                <div id="tab4"><a href="wxInfo">Info</a></div>
                <div id="tab5"><a href="wxAdmin">Admin</a></div>
            </div><!-- navigation -->
        </div> <!-- Header -->
        <div id="block">
        	<div id="content">
  			
        	</div><!-- Content -->
        </div> <!-- Block -->
        <div id="footer">
            <div id="credit">
            <?php 	$functionStop = microtime(true);
                    $executionTime = round($functionStop - $functionStart,3);
                    echo "<a href=\"wxCredits\">Site Credits</a>";
                    echo "&nbsp; &nbsp; Total page execution was completed in : " . $executionTime . " seconds<br/>";
            ?>
            </div><!-- Credit -->
        </div> <!-- footer -->
      
    </div> <!-- main -->
  

</body>
</html>

