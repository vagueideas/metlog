<?php
header("refresh: 9");

echo "<style>";
include 'style.css';
echo "</style>";

// 
// currentwx.php - grabs the most recent record from the weather database
// CS50 Final project
// phil davis Jan 2023
//

// Print debugging information if True
$debug_mode = True; 

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
$sql = "SELECT user, pws, timestamp, barohpa, tempc, intempc, dewptc, humidity, inhumidity, windspeedms, windgustms, winddir, rainmm, dailyrainmm FROM snapshot ORDER BY timestamp DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($debug_mode == True) {
    echo $sql;
    echo "<br>";
}

if (mysqli_num_rows($result) > 0) {
  // output data of each row
  while($row = mysqli_fetch_assoc($result)) {
    echo "user: " . $row["user"] . " - pws: " . $row["pws"] . "<br>";
    $user = $row["user"];
    $pws = $row["pws"];
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
if ($debug_mode == True) {
    echo "<h2>Current weather conditions</h2>";

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


?>
	
