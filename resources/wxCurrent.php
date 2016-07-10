<?php
session_start();
// ###############################################
// #
// # 				Current Weather  
// #
// ###############################################
//
?>
<div id="links">
    Show <a href="wxGauges" class="content">Real-time Gauges</a>
    Add an <a href="wxObservation" class="content">observation</a>
</div>
<div id="weather">

</div><!-- weather -->

<script>
$(document).ready(function() {
	currentFunctions();
});
</script>