<?php

// 
// updateweatherstation.php - captures data sent by PWS and inserts into database
// CS50 Final project
// phil davis Jan 2023
//

// include this php file to read system log files
include tail.php

// Get the baromatric pressure sent in weathercloud api format
function weathercloud() 
{
    // tail the last line of the weathercloud log file
    $wc_variables = tail('/var/log/weather/weathercloud_access.log');
    
    // Extract the barometer value (more accurate that wunderground's)
    $wc_bar = $wc_variables[10];

    return $wc_bar;

}

$bar = weathercloud();

echo $bar;

?>

