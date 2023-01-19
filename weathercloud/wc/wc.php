<?php

//
// A hackish way to get info from weathercloud into the database
// When the PWS contacts the URL it creates an 404 event, running this script and reading the 
// variables from the log file
// phil davis jan 2023
//


// Print debugging information if True
$debug_mode = True;

include 'tail.php';

// tail the last line of the weathercloud log file
$wc_recieved = tail('/var/log/weather/weathercloud_access.log');
 
// Explode this string with "/" as a delimiter
$wc_variables = explode("/", $wc_recieved);

// Grab all the variables
$wid = $wc_variables[6];
$key = $wc_variables[8];
$bar = $wc_variables[10];
$wdir = $wc_variables[12];
$wspd = $wc_variables[14];
$wspdhi = $wc_variables[16];
$rainrate = $wc_variables[18];
$rain = $wc_variables[20];
$temp = $wc_variables[22];
$chill = $wc_variables[24];
$hum = $wc_variables[26];
$dew = $wc_variables[28];
$tempin = $wc_variables[30];
// This has " HTTP..." in the string, so extract the number part like this...
$humin = (int)filter_var($wc_variables[32], FILTER_SANITIZE_NUMBER_INT);


// output for debugging
if ($debug_mode == True) {

    echo "<h2> Weathercloud data </h2>";

    echo "<br>";

    echo "<h3>Weather string</h3> " . $wc_recieved;

    echo "<br><br>";

    // Show the variables split up
    echo "<h3>Exploded variables</h3>";
    $i = 0;
    foreach ($wc_variables as $wc_variable) {
            echo $i . " : " . $wc_variable . "<br>";
            $i++;
    }
}



if ($debug_mode == True) {
    echo "<h2>Weathercloud variables</h2>";
    echo "Weather station id: " . $wid . "<br>";
    echo "Weather station key: " . $key . "<br>";
    echo "Barometric pressure: " . $bar / 10 . " hpa<br>";
    echo "Wind direction: " . $wdir . "&deg<br>";
    echo "Wind speed: " . $wspdi / 10 . " m/s<br>";
    echo "Wind gust speed: " . $wspdhi / 10 . " m/s<br>";
    echo "Rain rate: " . $rainrate / 10 . " mm/hr<br>";
    echo "Daily rain: " . $rain / 10 . " mm<br>";
    echo "Temperature: " . $temp / 10 . "&degC<br>";
    echo "Wind chill: " . $chill / 10 . "&degC<br>";
    echo "Humidity: " . $hum . "%<br>";
    echo "Dew point: " . $dew / 10 . "&degC<br>";
    echo "Indoor temperature: " . $tempin / 10 . "&degC<br>";
    echo "Indoor humidity: " . $humin . "%<br>";
}








?>
