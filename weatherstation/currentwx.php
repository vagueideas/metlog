<?php
// 
// currentwx.php - grabs the most recent record from the weather database
// CS50 Final project
// phil davis Jan 2023
//

// Print debugging information if True
$debug_mode = False; 

// Refresh page every 9 seconds
header("refresh: 9");

// html headers
include 'head.html';

echo "<div class=\"container-fluid\">";

include 'nav.html';

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

//$sql = "SELECT user, pws, barohpa, tempc, intempc, dewptc, humidity, inhumidity, windspeedms, windgustms, winddir, rainmm, dailyrainmm FROM snapshot";
$sql = "SELECT pwsid, pwskey, timestamp, barohpa, tempc, intempc, dewptc, humidity, inhumidity, windspeedms, windgustms, winddir, rainmm, dailyrainmm FROM snapshot ORDER BY timestamp DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($debug_mode == True) {
    echo "<h2>SQL query</h2>"; 
    echo $sql;
    echo "<br>";
}

if (mysqli_num_rows($result) > 0) {
  // output data of each row
  while($row = mysqli_fetch_assoc($result)) {
    $pwsid = $row["pwsid"];
    $pwskey = $row["pwskey"];
    $timestamp = $row["timestamp"];
    $barohpa = $row["barohpa"];
    $tempc = $row["tempc"];
    $intempc = $row["intempc"];
    $dewptc = $row["dewptc"];
    $humidity = $row["humidity"];
    $inhumidity = $row["inhumidity"];
    $windspeedms= $row["windspeedms"];
    $windgustms = $row["windgustms"];
    $winddir = $row["winddir"];
    $rainmm = $row["rainmm"];
    $dailyrainmm = $row["dailyrainmm"];
  }
} else {
  echo "0 results";
}

echo "<br>";

/*
VALUES ($user, $pws, $barohpa, $tempc, $intempc, $dewptc, $humidity, $inhumidity, $windspeedms, $windgustms, $winddir, $rainmm, $dailyrainmm)";

if ($debug_mode == True) {
    if (mysqli_query($conn, $sql)) {
      echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
*/

mysqli_close($conn);

// Table of data for the DB
echo "<h2>Current weather conditions</h2>";

echo "
<table class=\"mytable\">
    <tr>
        <th>Variable </th>
        <th>Raw Value </th>
        <th>Displayed value </th>
    </tr>
    <tr>
        <td>pwsid</td>
        <td>" . $pwsid . "</td>
        <td>" . $pwsid . "</td>
    <tr>
    <tr>
        <td>pwskey</td>
        <td>" . $pwskey . "</td>
        <td>" . $pwskey . "</td>
    <tr>
    <tr>
        <td>timestamp</td>
        <td>" . $timestamp . "</td>
        <td>" . $timestamp . "</td>
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
        <td>" . $windspeedms / 100 . " m/s</td>
    <tr>
    <tr>
        <td>windgustms</td>
        <td>" . $windgustms . "</td>
        <td>" . $windgustms / 100 . " m/s</td>
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
?>

<canvas style="height: 100%; width: 100%;"></canvas> 

<script> src="pureknob.js"></script>

<script>
// Create knob element, 300 x 300 px in size.
var knob = pureknob.createKnob(300, 300);

// Set properties.
knob.setProperty('angleStart', -0.75 * Math.PI);
knob.setProperty('angleEnd', 0.75 * Math.PI);
knob.setProperty('colorFG', '#88ff88');
knob.setProperty('trackWidth', 0.4);
knob.setProperty('valMin', 0);
knob.setProperty('valMax', 100);

// Set initial value.
knob.setValue(50);

/*
 * Event listener.
 *
 * Parameter 'knob' is the knob object which was
 * actuated. Allows you to associate data with
 * it to discern which of your knobs was actuated.
 *
 * Parameter 'value' is the value which was set
 * by the user.
 */
var listener = function(knob, value) {
    console.log(value);
};

knob.addListener(listener);

// Create element node.
var node = knob.node();

// Add it to the DOM.
var elem = document.getElementById('demo');
elem.appendChild(node);
</script>


<?php

echo "</div>";

?>
	
