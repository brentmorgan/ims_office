<?php
session_start();

include("../../../checklist/includes/db.inc.php");

function grabRooms() {
	$db = CreateConnection();
	$sql = "SELECT field FROM lu_rooms"; // `field` has underscores, `room` does not
	$result = mysql_query($sql,$db) or print "Error getting list of rooms. " . mysql_error();
	CloseConnection($db);

	return $result;
}

function getInfo() {
	$db = CreateConnection();
	$sql = "SELECT * FROM calls WHERE 1 AND ";

	if ($_POST) {
		if ($_POST['report'] != "all") {
			$sql .= "user_error = '" . $_POST['report'] . "' "; //AND ";
		//}
			if ($_POST['andor'] == "or" && $_POST['confirm'] != "all") {
				$sql .= "OR ";
			} else {
				$sql .= "AND ";
			}
		}
		if ($_POST['confirm'] != "all") {
			$sql .= "confirm = '" . $_POST['confirm'] . "' AND ";
		}
	}
	$sql .= " 2;";

	print $sql;

	$result = mysql_query($sql,$db) or print "OH NO!!!! " . mysql_error();
	CloseConnection($db);

	return $result;
}

$room_list = grabRooms();

$all_rooms = array();

while ($row = mysql_fetch_assoc($room_list)){
	array_push($all_rooms, $row);
}

for ($i = 0; $i < sizeof($all_rooms); $i++) {
	$all_rooms[$i][count] = 0;
}

$new_data = getInfo();

while ($row = mysql_fetch_assoc($new_data)){
	foreach ($all_rooms as $key => $val) {
		if ($row['room_id'] == $val['field']) {
			$all_rooms[$key][count]++;
		}
	}
}

// ***************************************************************************************
// ********************** User Interface *************************************************
// ***************************************************************************************

?>
<html>
<head><title>The screen is not showing the right thing.</title></head>
<body>
<form method="post">
Reported Problem:
<select name="report">
	<option value="yes" <?php if ($_POST['report']=='yes') {print "selected";} ?>>User Error</option>
	<option value="no" <?php if ($_POST['report']=='no') {print "selected";} ?>>Equipment Fail</option>
	<option value="software" <?php if ($_POST['report']=='software') {print "selected";} ?>>Software Issue</option>
	<option value="all" <?php if ($_POST['report']=='all'||!$_POST) {print "selected"; } ?>>All</option>
</select>
AND <input type="radio" name="andor" value="and" <?php if ($_POST['andor']=="and"||!$_POST) {print "checked";} ?>>
OR <input type="radio" name="andor" value="or" <?php if ($_POST['andor']=="or") {print "checked";} ?>>
Confirm Problem:
<select name="confirm">
	<option value="yes" <?php if ($_POST['confirm']=='yes') {print "selected";} ?>>User Error</option>
	<option value="no" <?php if ($_POST['confirm']=='no') {print "selected";} ?>>Equipment Fail</option>
	<option value="software" <?php if ($_POST['confirm']=='software') {print "selected";} ?>>Software Issue</option>
	<option value="all" <?php if ($_POST['confirm']=="all"||!$_POST) {print "selected";} ?>>All</option>
</select>
<input type="submit">



<?php


// ************************************************************************
// ******************** Print the results *********************************

// first sort them from highest number of occurrences to lowest number
foreach ($all_rooms as $array_row) {
	$sorting_values['field'][] = $array_row['field'];
	$sorting_values['count'][] = $array_row['count'];
}
array_multisort($sorting_values['count'], SORT_DESC, $sorting_values['field'], SORT_ASC, $all_rooms);

// Now actually print them out
print "<table border='0'>";
foreach ($all_rooms as $key=>$val) {
	print "<tr><td>".$val['field'].": </td><td>".$val['count']."</td></tr>";
}
print "</table>";




//var_dump($all_rooms);
?>
