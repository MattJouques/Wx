<?php
//
// ###########################################################
//
//					Maths Functions		
//
// ###########################################################
//
// Standard Conversions
//
function convert($from, $input) {
	//
	// TODO - Change this to use configuration units
	//
	// Temperature
	if ( $from == "F" ) {
		$result = round(($input - 32) * 0.5556,1);
		return $result;
	} else if ($from == "C" ) {
		$result = round(($input * 1.8) + 32,1);
		return $result;
	}
	// Pressure
	else if ($from == "cm" || $from == "cmHg") {		
		$result = round ($input * 13.332239, 2);
		return $result;	
	} else if ($from == "hPa" || $from == "mb") {		
		$result = $input;
		return $result;
	} else if ($from == "in" || $from == "inHg") {		
		$result = round ($input / 0.02953, 2);		
	} else if ($from == "kPa") {		
		$result = ($input * 10);
		return $result;	
	} else if ($from == "mm" || $from == "mmHg") {		
		$result = round ($input * 1.3332239, 2);
		return $result;
	}
	// wind to knots
	else if ($from == "Mph" ) {
		$result = round($input * 0.8689624190816,1);
		return $result;
	}
	// Rain to mm
	else if ($from == "inch") {
		$result = round($input / 0.039370,1);
		return $result;
	}
	// No valid conversions return the input
	else {
		return $input;
	}
}

?>


