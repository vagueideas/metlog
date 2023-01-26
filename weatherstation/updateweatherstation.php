<?php

// 
// updateweatherstation.php v 0.4 - captures data sent by PWS and inserts into database
// CS50 Final project
// phil davis Jan 2023
//

// Print debugging information if True
$debug_mode = true;

// include this php file to read system log files
include 'tail.php';

// Read the latest weathercloud data from the database
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

// Get the baromatric pressure sent in weathercloud api format
function weathercloud($wid) {

    global $debug_mode;
    global $conn;

    $sql = "SELECT bar FROM weathercloud WHERE wid=\"$wid\" ORDER BY timestamp DESC LIMIT 1";
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
    	echo "weathercloud barometer: " . $wc_bar;
    	echo "<br>";
	    echo "<br> -- end of weathercloud() function -- </br>";
    }

    return $wc_bar;
}

function wxrecord($saved_var, $current_var, $current, $pwsid) {

    global $debug_mode;
    global $conn;

    if ($debug_mode == True) {
        echo "<h2>Getting and setting weather records</h2>";
    }

    // get record from the db
    $sql = "SELECT id, $saved_var FROM wxrecords WHERE pwsid=\"$pwsid\" AND date(timestamp)=curdate() AND $saved_var IS NOT NULL";

    if ($debug_mode == True) {
        echo "<br>db query: " . $sql;
    }

    // Run this query
    $result = mysqli_query($conn, $sql);

    // If there is already a result check if new high
    if (mysqli_num_rows($result) > 0) {

        // output data of each row
        while($row = mysqli_fetch_assoc($result)) {
        
            if ($debug_mode == True) {
                echo "<br>id: " . $row["id"]. " - value: " . $row[$saved_var]. " - pwsid: " . $pwsid;
            }
            
            # saved value from the db, if new record then delete that row with this id 
            $id = $row["id"];
            $saved = $row[$saved_var];
            
            if ($debug_mode == True) {
                echo "<br>id: " . $id . " saved value: " . $saved;
            }
          

            if ($debug_mode == True) {
                echo "<br>old value: " . $saved . ", new value: " . $current;
            }

            // greater or lesser
            // the default of high records do this

            // if we are going for low records do this
            switch ($saved_var) {
                case "tempclo":
                    $compare = ($current < $saved);
                    break;
                case "intempclo":
                    $compare = ($current < $saved);
                    break;
                default:
                    $compare = ($saved < $current);
            }

           
            //if ($current > $saved) {
            if ($compare) {
            
                if ($debug_mode == True) {
                  echo "<br>New record!";
                }
            
                # run 2 queries, create new record and delete the old
                $sql = "INSERT INTO wxrecords (pwsid, $saved_var) VALUES (\"$pwsid\", $current)";

                if ($debug_mode == True) {

                    if (mysqli_query($conn, $sql)) {
                      echo "<br>" . $sql;
                      echo "<br>New record created successfully";

                    } else {
                      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                    }

                } else {
                    # debugging not enabled, just run the query
                    mysqli_query($conn, $sql);
                }

                $sql = "DELETE FROM wxrecords WHERE id=$id";
            
            # entry exists, but is higher than current value, nothing to do
            } else {
            
                if ($debug_mode == True) {
                  echo "<br>no new record, nothing to update";
                  return;
                }
            }
        }
    # else there is no entry in the db, create a new one
    } else {
        $sql = "INSERT INTO wxrecords (pwsid, $saved_var) VALUES (\"$pwsid\", $current)";

        if ($debug_mode == True) {
            echo "<br>0 results, create new entry.";
        }
    }

    if ($debug_mode == True) {

        if (mysqli_query($conn, $sql)) {
          echo "<br>" . $sql;
          echo "<br>New record created successfully";

        } else {
          echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }

    } else {
        # debugging not enabled, just run the query
        mysqli_query($conn, $sql);
    }

    return;
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
$pwsid = $_GET['ID'];
$pwskey = $_GET['PASSWORD'];
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


if ($debug_mode == True) {
    echo "<h1>weather pws data capture</h1>";
}

// Weathercloud wid and wunderground id must match
$wid = $pwsid;

// Get the more accurate weathercloud barometric reading
$barohpa = weathercloud($wid);

// weather station id sent by weatherstation, must match user entered details
$pws = "pwsid";

// output for debugging
if ($debug_mode == True) {
    echo "<h2>Data from weathercloud</h2>";
    echo "<br>";
    echo "weathercloud baro: " . ($barohpa) , " hpa";
    echo "<br>";
    echo "<h2>Converted data from wunderground</h2>";
    echo "pwsid: " . $pwsid . "<br>";
    echo "pwskey: " . $pwskey . "<br>";
    echo "baro in inhg: " . $baroinhg . " (not used)<br>";
    echo "temp (F): " . $_GET['tempf'] . ", temp (C): . " . $tempc . "<br>";
    echo "indoor temp (F): " . $_GET['indoortempf'] . ", temp (C): . " . $intempc . "<br>";
    echo "dewpt (F): " . $_GET['dewptf'] . ", dewpt (C): " . $dewptc . "<br>";
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

$sql = "INSERT INTO snapshot (pwsid, pwskey, barohpa, tempc, intempc, dewptc, humidity, inhumidity, windspeedms, windgustms, winddir, rainmm, dailyrainmm)
VALUES ('$pwsid', '$pwskey', $barohpa, $tempc, $intempc, $dewptc, $humidity, $inhumidity, $windspeedms, $windgustms, $winddir, $rainmm, $dailyrainmm)";

if ($debug_mode == True) {
    if (mysqli_query($conn, $sql)) {
      echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
} else {
    mysqli_query($conn, $sql);
}

// Check and set weather records
// This are for each day
// === records ===
// = tempchi
// = tempclo
// = intempchi
// = intempclo
// = windspeedmshi
// = windgustmshi
// = rainmmhi
// = dailyrainmmhi

// To set a record, get the values from the records table for today, if the current value 
// is higher, update the record


// params: name of saved value, name of current value, current value, pwsid
wxrecord("tempchi", "tempc", $tempc, $pwsid);
wxrecord("tempclo", "tempc", $tempc, $pwsid);
wxrecord("intempchi", "intempc", $intempc, $pwsid);
wxrecord("intempclo", "intempc", $intempc, $pwsid);
wxrecord("windspeedmshi", "windspeedms", $windspeedms, $pwsid);
wxrecord("windgustmshi", "windgustms", $windgustms, $pwsid);
wxrecord("rainmmhi", "rainmm", $rainmm, $pwsid);
wxrecord("dailyrainmmhi", "dailyrainmm", $dailyrainmm, $pwsid);

mysqli_close($conn);

?>
