<?php

// 
// updateweatherstation.php v 0.2 - captures data sent by PWS and inserts into database
// CS50 Final project
// phil davis Jan 2023
//

// Print debugging information if True
$debug_mode = True; 

// include this php file to read system log files
include 'tail.php';

// Get the baromatric pressure sent in weathercloud api format
function weathercloud($wid) {

    global $debug_mode;

    // Read the latest weathercloud data from the database

    // Lets connect to the db and insert all this data into it
    $servername = "localhost";
    $username = "metlog";
    $password = "metlog";
    $dbname = "metlog";

    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    // Check connection
    if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT bar FROM weathercloud WHERE wid=$wid ORDER BY timestamp DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($debug_mode == True) {
        echo $sql;
        echo "<br>";
    }

    if (mysqli_num_rows($result) > 0) {
        // output data of each row
        while($row = mysqli_fetch_assoc($result)) {
          $wc_bar = $row["bar"];
        }
      } else {
        echo "0 results";
      }
      

    // output for debugging
    if ($debug_mode == True) {

	    echo "<br> -- start of weathercloud() function -- </br>";
    	echo "<br>";
    	echo "<br><br>";
    	echo "weathercloud barometer: " . $wc_bar;
	    echo "<br> -- end of weathercloud() function -- </br>";
    }

    return $wc_bar;
}

/* These are the variables from wunderground
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

// Gather all the variables and convert to metric units
// Values are multiplied, usually by 10, to get rid of decimal points so are of type INT
$user = $_GET['ID'];
$password = $_GET['PASSWORD'];
// pressure in inches mercury, will be disgarded as the weathercloud data is more accurate
$baroinhg = $_GET['baromin'];
// temp, inside and out and dewpoint converted to C
$tempc = round((($_GET['tempf'] - 32) / 1.8), 1) * 10; 
$intempc = round((($_GET['indoortempf'] - 32) / 1.8), 1) * 10; 
$dewptc = round((($_GET['dewptf'] - 32) / 1.8), 1) * 10; 
// % humidity, indoor and out
$humidity = $_GET["humidity"];
$inhumidity = $_GET["indoorhumidity"];
// Wind speed and gust in m/s
$windspeedms = round($_GET["windspeedmph"] * 4.4704, 1) * 10; 
$windgustms = round($_GET["windgustmph"] * 4.4704, 1) * 10; 
// wind dir in degrees (hopefully true, not mag)
$winddir = $_GET["winddir"];
// rain and dailyrain in mm
$rainmm = round($_GET["rainin"] * 25.4, 1) * 10;
$dailyrainmm = round($_GET["dailyrainin"] * 25.4, 1) * 10;


echo "<h1>weather pws data capture</h1>";

// In future version, get this from user db 
$wid = "5283ef9c3c00581f"

// Get the more accurate weathercloud barometric reading
$barohpa = weathercloud($wid);

// Set the weather station name as variable for now, in future this can be determined from user login details
$pws = "pws";

// output for debugging
if ($debug_mode == True) {
    echo "<h2>Data from weathercloud</h2>";
    echo "<br>";
    echo "weathercloud baro: " . ($barohpa) , " hpa";
    echo "<br>";
    echo "<h2>Converted data from wunderground</h2>";
    echo "user: " . $user . "<br>";
    echo "password: " . $password . "<br>";
    echo "baro in inhg: " . $baroinhg . " (not used)<br>";
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

// Table of data for the DB
if ($debug_mode == True) {
    echo "<h2>Variables to be inserted into db</h2>";

    echo "
        <table>
        <tr>
            <th>Variable </th>
            <th>Raw Value </th>
            <th>Displayed value </th>
        </tr>
        <tr>
            <td>user</td>
            <td>" . $user . "</td>
            <td>" . $user . "</td>
        <tr>
        <tr>
            <td>pws</td>
            <td>" . $pws . "</td>
            <td>" . $pws . "</td>
        <tr>
        <tr>
            <td>barohpa</td>
            <td>" . $barohpa . "</td>
            <td>" . $barohpa / 10 . " hpa</td>
        <tr>
        <tr>
            <td>tempc</td>
            <td>" . $tempc . "</td>
            <td>" . $tempc / 10 . "&degC</td>
        <tr>
        <tr>
            <td>intempc</td>
            <td>" . $intempc . "</td>
            <td>" . $intempc / 10 . "&degC</td>
        <tr>
        <tr>
            <td>dewptc</td>
            <td>" . $dewptc . "</td>
            <td>" . $dewptc / 10 . "&degC</td>
        <tr>
        <tr>
            <td>humidity</td>
            <td>" . $humidity . "</td>
            <td>" . $humidity . "%</td>
        <tr>
        <tr>
            <td>inhumidity</td>
            <td>" . $inhumidity . "</td>
            <td>" . $inhumidity . "%</td>
        <tr>
        <tr>
            <td>windspeedms</td>
            <td>" . $windspeedms . "</td>
            <td>" . $windspeedms / 10 . " m/s</td>
        <tr>
        <tr>
            <td>windgustms</td>
            <td>" . $windgustms . "</td>
            <td>" . $windgustms / 10 . " m/s</td>
        <tr>
        <tr>
            <td>winddir</td>
            <td>" . $winddir . "</td>
            <td>" . $winddir . "&deg </td>
        <tr>
        <tr>
            <td>rainmm</td>
            <td>" . $rainmm . "</td>
            <td>" . $rainmm . " mm</td>
        <tr>
        <tr>
            <td>dailyrainmm</td>
            <td>" . $dailyrainmm . "</td>
            <td>" . $dailyrainmm . " mm</td>
        <tr>

        </table>
        <br>";

}

// Lets connect to the db and insert all this data into it
$servername = "localhost";
$username = "metlog";
$password = "metlog";
$dbname = "metlog";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$sql = "INSERT INTO snapshot (user, pws, barohpa, tempc, intempc, dewptc, humidity, inhumidity, windspeedms, windgustms, winddir, rainmm, dailyrainmm)
VALUES ('$user', '$pws', $barohpa, $tempc, $intempc, $dewptc, $humidity, $inhumidity, $windspeedms, $windgustms, $winddir, $rainmm, $dailyrainmm)";

if ($debug_mode == True) {
    if (mysqli_query($conn, $sql)) {
      echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

mysqli_close($conn);

?>
	
