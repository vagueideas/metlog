<?php

// 
// updateweatherstation.php - captures data sent by PWS and inserts into database
// CS50 Final project
// phil davis Jan 2023
//

// Print debugging information if True
$debug_mode = True; 

// include this php file to read system log files
include 'tail.php';

// Get the baromatric pressure sent in weathercloud api format
function weathercloud() {

    global $debug_mode;

    // tail the last line of the weathercloud log file
    $wc_recieved = tail('/var/log/weather/weathercloud_access.log');
    
    // Explode this string with "/" as a delimiter
    $wc_variables = explode("/", $wc_recieved);

    // Extract the barometer value (more accurate that wunderground's)
    $wc_bar = $wc_variables[10];

    // output for debugging
    if ($debug_mode != True) {

	echo "<br> -- start of weathercloud() function -- </br>";

    	echo "<br>";

    	echo "weather string: " . $wc_recieved;

    	echo "<br><br>";

	// Show the variables split up
    	$i = 0;
    	foreach ($wc_variables as $wc_variable) {
    	        echo $i . " : " . $wc_variable . "<br>";
    	        $i++;
    	}
    	
    	echo "<br><br>";

    	echo "weathercloud barometer: " . $wc_bar;

	echo "<br> -- end of weathercloud() function -- </br>";
    }

    return $wc_bar;
}

// GET variables from wunderground
//o


/*
?ID=user
&PASSWORD=passpass
&action=updateraww
&realtime=1
&rtfreq=5
&dateutc=now
&baromin=30.30
&tempf=42.4
&dewptf=37.5
&humidity=83
&windspeedmph=7.1
&windgustmph=8.5
&winddir=248
&rainin=0.0
&dailyrainin=0.0
&indoortempf=63.8
&indoorhumidity=55
*/

$user = $_GET['ID'];
$password = $_GET['PASSWORD'];
// pressure in inches mercury
$baroinhg = $_GET['baromin'];
// temp, inside and out and dewpoint converted to C
$tempc = round((($_GET['tempf'] - 32) / 1.8), 2); 
$intempc = round((($_GET['indoortempf'] - 32) / 1.8), 2); 
$dewptc = round((($_GET['dewptf'] - 32) / 1.8), 2); 
// % humidity, indoor and out
$humidity = $_GET["humidity"];
$inhumidity = $_GET["indoorhumidity"];
// Wind speed and gust in m/s
$windspeedms = round($_GET["windspeedmph"] * 4.4704, 2); 
$windgustms = round($_GET["windgustmph"] * 4.4704, 2); 
// wind dir in degrees (hopefully true, not mag)
$winddir = $_GET["winddir"];
// rain and dailyrain in mm
$rainmm = round($_GET["rainin"] * 25.4, 2);
$dailyrainmm = round($_GET["dailyrainin"] * 25.4, 2);


echo "<h1>weather pws data capture</h1>";

$bar = weathercloud();


// output for debugging
if ($debug_mode == True) {
    echo "<br>";
    echo "weathercloud baro: " . ($bar / 10) , " hpa";
    echo "<br>";
    echo "<h2>Converted data from wunderground</h2>";
    echo "user: " . $user . "<br>";
    echo "password: " . $password . "<br>";
    echo "baro in inhg: " . $baroinhg . "<br>";
    echo "temp (F): " . $_GET['tempf'] . ", temp (C): . " . $tempc . "<br>";
    echo "indoor temp (F): " . $_GET['indoortempf'] . ", temp (C): . " . $intempc . "<br>";
    echo "dewpt (F): " . $_GET['dewptf'] . ", dewpt (C): . " . $dewptc . "<br>";
    echo "humidity: " . $humidity . "<br>";
    echo "indoor humidity: " . $inhumidity . "<br>";
    echo "windspeed m/s: " . $windspeedms . "<br>";
    echo "wind gust m/s: " . $windgustms . "<br>";
    echo "wind dir: " . $winddir . "<br>";
    echo "rain mm: " . $rainmm . "<br>";
    echo "daily rain mm: " . $dailyrainmm . "<br>";
}

?>
	
