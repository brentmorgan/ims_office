<?php
session_start();
include('../../checklist/includes/loggedin.php');
// **********************************************************************************************************
include('newcall.inc.php'); // ****************** THIS        has been       CHANGED WHEN THIS PAGE GOES LIVE
// **********************************************************************************************************
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="../../checklist/css/classroom.css" />
	<script type="text/javascript" src="../../checklist/js/prototype.js"></script>
	<script type="text/javascript" src="../../checklist/js/scriptaculous.js"></script>
	<script type="text/javascript" src="../../checklist/js/call.js"></script>

</head>
<body>
<?php include ("../../checklist/includes/menu.inc.php"); ?>
<?php 
if ($_POST) {
	$db = CreateConnection();
	$day = date('D');
	$todays_date = date('j');
	$week = date('W');
	$month = date('n');
	$year = date('Y');

	if (isset($_POST['edit'])) { // ***************** EDITING AN OLD SUPPORT CALL
		$sql = "UPDATE calls SET resolved_phone='$_POST[resolved_phone]', user_error='$_POST[user_error]', send_tech='$_POST[send_tech]', solved_tech='$_POST[solved_tech]', confirm='$_POST[confirm_problem]', rt_ticket='$_POST[rt_ticket]', ticket_num='$_POST[ticket_num]' WHERE id = '$_POST[id]'";
		mysql_query($sql,$db) or print "Error updating the data. " . mysql_error();
		/* ********************************************************************** */
		/* THE QUERY   has been   UNCOMMENTED!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
		/* ********************************************************************** */		

		$list = ""; // making a list of the equipment problem checkboxes if any were checked
		foreach ($_POST as $key => $val) {
			if ((strpos($key,"cl_") === 0) && $val != "") {
				$list .= "(NULL, '" . $_POST['id'] . "', '" . $val . "'),";
			}
		}
		$list = substr($list, 0, -1); // remove the last comma
		$sql = "DELETE FROM problems WHERE call_id = '" . $_POST['id'] . "';";
		$sql2 = "INSERT INTO problems (id, call_id, problem) VALUES " . $list . ";";
		mysql_query($sql,$db) or print "Error deleting problem data. " . mysql_error();
		if (isset($list) && strlen($list) > 1) {
			mysql_query($sql2,$db) or print "Error updating problem data. " . mysql_error();
		}
	} else if ($_POST[support] == 'yes') { // ****************** ENTERING A NEW SUPPORT CALL
		if (($_POST['radio_resolved'] == "yes") && ($_POST['radio_tech'] == "no")) { 	// If it was solved over the phone and so NO TECHNICIAN was sent
			$_POST['radio_tech_solved'] = "n/a";					// the "Tech Solved Problem?" field is not applicable
			$_POST['radio_confirm'] = "n/a";					// and same with "Confirm Type of Problem"
		}
		$sql = "INSERT INTO calls (reported_by, building_id, room_id, resolved_phone, user_error, send_tech, solved_tech, confirm, rt_ticket, ticket_num, day_of_week, date, week, month, year) VALUES ('$_SESSION[user]','$_POST[buildings]','$_POST[room]','$_POST[radio_resolved]','$_POST[radio_usererror]','$_POST[radio_tech]','$_POST[radio_tech_solved]','$_POST[radio_confirm]','$_POST[radio_rt]','$_POST[radio_rt_ticket]','$day','$todays_date','$week','$month','$year');";
		mysql_query($sql,$db) or print "Error inserting data!!! " . mysql_error();
		$call_id = mysql_insert_id($db); // ** GETS THE ID OF THE CALL SO WE CAN REFERENCE IT IN THE PROBLEMS TABLE

		$list = ""; // making a list of the equipment problem checkboxes if any were checked
		foreach ($_POST as $key => $val) {
			if ((strpos($key,"cl_") === 0) && $val != "") { // there is always cl_other, even if nothing was entered in the OTHER text field
				$list .= "(NULL, '" .$call_id . "', '" . $val . "'),";
			}
		}
		$list = substr($list, 0, -1); // remove the last comma
		
		if (isset($list) && strlen($list) > 1) {
			$sql = "INSERT INTO problems (id, call_id, problem) VALUES " . $list . ";";	
			mysql_query($sql,$db) or print "Error inserting problem data!!! " . mysql_error();
		// ****************************************************** THIS ONE    has been   UNCOMMENTED TOO!!!!!!!!
		}
	} else if ($_POST[support] == 'no') { // *************** ENTERING A NEW NON-SUPPORT CALL
		$sql = "INSERT INTO othercalls (reported_by, day_of_week, date, week, month, year, reason) VALUES ('$_SESSION[user]','$day','$todays_date','$week','$month','$year','$_POST[otherCalls]');";
		mysql_query($sql,$db) or print "Error inserting other data!!! " . mysql_error();
	}

	CloseConnection($db);
}
?>
<div id="whole_thing" style="width:100%; margin-right:auto; margin-left:auto;">
<?php include ("../../checklist/includes/calls_logo.php"); ?>
<div id="call_tables" style="width:1150px; margin-left:auto; margin-right:auto; position:relative; top:-60px;">
<?php
listCalls();
//echo $list;
?>
</p>
</div>
<!-- COUNT DOWN TO COMMENCEMENT!!!! COUNTDOWN TO COMMENCEMENT!!!
<div style="position:absolute; top:100px; right:20px; width:250px;" id="count_down">
	<?php //include('countdown.php'); ?>
</div>
-->
</body>
</html>
