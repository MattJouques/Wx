<?php
session_start(); 
// ###############################################
// #
// # 				 Credits Page  
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
?>
<h1>Site Credits</h1>
<p>The following credits are offered where source code or imagery have been used or adapted in the creation of this site</p>

<p><strong>Weather forecasting scripts</strong>: - Based on the BT Global Sager weathercaster php scripts by <a href="http://sandaysoft.com/">Buford T. Justice</a>, the standard php weather forecaster scripts have been modifed to:
<ul>
  <li>use a database engine to drive the rules - enabling the administrator to build additional forecasting rules</li>
  <li>provide a means of building your own rules into the database (for weather nuances or micro-climate environments)</li>
  <li>provide a means of using the scripts with data generated from different weather stations and software - Original scripts were designed for use with the Cumulus application</li>
</ul>
</p>
<p><strong>Real-Time Weather Gauges</strong>: - Steel Series weather gauges
<ul>
	<li>Scripts by Mark Crossley</li>
    <li>Gauges drawn using Gerrit Grunwald's <a href="http://harmoniccode.blogspot.com" target="_blank">SteelSeries</a> <a href="https://github.com/HanSolo/SteelSeries-Canvas">JavaScript library</a></li>
    <li>Wind Rose drawn using <a href="http://www.rgraph.net/">RGraph</a></li>
</ul>
</p>

<p><strong>Images</strong>: - Images used in this site are creditied to:
<ul>
  <li>Site Icons by <a href="https://www.iconfinder.com/iconsets/VistaICO_Toolbar-Icons">VistaICO.com</a></li>
  <li>Weather Icons by <a href="http://www.melsbrushes.co.uk/">Mels Brushes</a> - Some icons have been modified from the original</li>
</ul>
Terms of use under creative commons found at relevant links above.
</p>

Magnifier.js from http://mark-rolich.github.io/Magnifier.js/