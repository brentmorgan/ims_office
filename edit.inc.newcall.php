<?php    /* *************** call.inc.php **************** */     
session_start();
include("../../checklist/includes/db.inc.php");

if($_POST['action'] == "secretBuilding"){
	$id = $_POST['id'];
//	$list = "<h3>The Building ID is ".$id."</h3>";
	
	$room_list = grabRooms($id);
	
	$list .= "<form name='RoomsForm' method='post' action=''>";
	$list .= "<select id='room' name='room' onChange=\"document.getElementById('equipment_checkboxes').style.display=''; log_roomid=(this.value); getOldIssues(this.value);\">"; //THIS SHOULD BE OPENDIV SO ITS ALL FANCY BUT I CANT MAKE IT WORK YET ???
	$list .= "<option value=''>Select Room</option>";
	while($row = mysql_fetch_assoc($room_list)){
		$list.= "<option value='$row[field]'>".$row['room']."</option>";
	}
	$list .="</select>";
	$list .="</form>";

	echo $list;
}
/*
else if($_POST && $_POST['action'] != "logNewCall"){$list="<h2>balls</h2>";
	echo $list;
}
*/
else if($_POST['action'] == "queryOldIssues"){
	$id = $_POST['id'];

	$list_of_issues = grabIssues($id);
	//$common_problems = grabChecklist();
	$list = "";

	while($row = mysql_fetch_assoc($list_of_issues)){
		if (strlen($row['status'])>47){ // *********************************** THIS REALLLLLLLY NEEDS TO BE CHANGED - TOTALLY DUMB WAY OF DOING IT - BUT IM TIRED AND CANT MAKE IT WORK THE WAY I WANTED TO I.E. COMPARE THE 'CHECKLIST' LIST (VGA CABLE IS NOT INVENTORIED..... VCR DOES NOT WORK.... THE CANNED RESPONSES!) AGAINST THE ACTUAL 'NOTES' SUBMISSIONS, AND ONLY SHOW TH NOTES
		$list .= "<b>".$row['d']."</b>: ".$row['status']."<br />";
		}
	}
	$list .= "</div>";
	echo $list;
}
//** ********************************************************* **//
function grabIssues($room){
	$db = CreateConnection();
	$sql="SELECT status, DATE_FORMAT(datecreated, '%Y %M %D') as d from report_details WHERE room = '".$room."' order by datecreated DESC;";
	$result = mysql_query($sql,$db) or print "Error with grabIssues " . mysql_error();
	CloseConnection($db);
	return $result;
}

//** ************************************* the next couple functions are borrowed from classroom.inc.php ********* **//
function grabChecklist(){
	
	$db = CreateConnection();
	$sql = "select * from lu_checklist";
	$result = mysql_query($sql,$db) or print "Error with grabChecklist " . mysql_error();

	CloseConnection($db);

	return $result;
}

//** this is the same as grabChecklist() above, but it gets my new checklist instead of the old one with things like cables and stuff on it
function grabNewChecklist() {

	$db = CreateConnection();
	$sql = "SELECT * from list";
	$result = mysql_query($sql,$db) or print "Error with getting new checklist: " . mysql_error();
	CloseConnection($db);
	return $result;

}

function grabBuildings(){

	$db = CreateConnection();
	$sql = "select * from lu_buildings where active = 'Y' ";
	$result = mysql_query($sql,$db) or print "Error with grabBuildings: " . mysql_error();

	CloseConnection($db);

	return $result;
}

function grabRooms($building){

	$db = CreateConnection();
	$sql = "select * from lu_rooms r where building = ". $building;

	$result = mysql_query($sql,$db) or print "error with grabrooms: " . mysql_error();

	Closeconnection($db);

	return $result;
}
//** ******************************** FOR SUBMITTING THE NEW CALL INFO ************************************* **//

if($_POST['action'] == "logNewCall"){
	$summary ="";
	$summary .= "<h2>Call Logged</h2>";
	$summary .="Call taken by: ".$_SESSION['user'];
	$summary .= "<br />Was Classroom Support: ".$_POST['support'];
	if($_POST['support']=='yes'){  // ****************************************** CLASSROOM SUPPORT CALLS
/*		if ($_POST['buildings']=='10000'){$_POST['buildings']='unknown';};
		if ($_POST['room']=='10000'){$_POST['room']='unknown';};
		$summary .="<br />Building ID: ".$_POST['buildings'];
		$summary .="<br />Room ID: ".$_POST['room'];
//		$summary .="<br />Reason: ".$_POST['reason'];  REASON IS IRRELEVANT TO CLASSROOM CALLS, ONLY MATTERS FOR NON CS CALLS
		$summary .="<br />Resolved: ".$_POST['radio_resolved'];
*/
		$db = CreateConnection();

		$day = date('D');
		$todays_date = date('j');
		$week = date('W');
		$month = date('n');
		$year = date('Y');

		$sql = "INSERT INTO calls (reported_by, building_id, room_id, resolved_phone, user_error, send_tech, solved_tech, rt_ticket, ticket_num, day_of_week, date, week, month, year) VALUES ('$_SESSION[user]','$_POST[buildings]','$_POST[room]','$_POST[radio_resolved]','$_POST[radio_usererror]','$_POST[radio_tech]','$_POST[radio_tech_solved]','$_POST[radio_rt]','$_POST[radio_rt_ticket]','$day','$todays_date','$week','$month','$year');";

		mysql_query($sql,$db) or print "Error inserting data " . mysql_error();

/*		$sql = "INSERT INTO report_building (reportedby, building_id, phone_code) VALUES ('$_SESSION[user]', '$_POST[buildings]', '10000');"; // SAME INNITIAL INSERT AS FOR NON-CLASSROOM CALLS BELOW, ONLY THE ACTUAL BUILDING CODE IS ALSO INSERTED INSTEAD OF JUST A ZERO
		mysql_query($sql,$db) or print "Error insertin data to report_building ".mysql_error();

		$reportid = mysql_insert_id($db); // SET $REPORTID TO VALUE OF THE ID JUST AUTO-INSERTED IN THE REPORT_BUILDING TABLE

		// SECOND INSERT
		$sql_two = "INSERT INTO report_rooms (reportedby, room, report_id, status, noted) VALUES ('$_SESSION[user]','$_POST[room]','$reportid','$_POST[radio_resolved]','no');";
		mysql_query($sql_two,$db) or print "Error with second insert, insserting into report_rooms " . mysql_error();

		$roomreportid = mysql_insert_id($db); // SET $ROOMREPORTID TO VALUE OF THE ID JUST AUTO-INSERTED IN THE REPORT_ROOMS TABLE

		// THIRD INSERT
		$sql_three = "INSERT INTO report_details (report_id, status, room, room_id, typed) VALUES ('$reportid','$_POST[notes]','$_POST[room]','$roomreportid','1');";
		mysql_query($sql_three,$db) or print "Error with third insert, into report_details " . mysql_error();
		// ADDITIONAL INSERTS *IF* THEY CLICKED ANY OF THE CHECKBOXES FOR SPECIFIC PROBLEMS
		foreach($_POST as $key => $value){
			if(preg_match("/cl_/", $key)){
				$sql_more = "INSERT INTO report_details (report_id, status, room, room_id, typed) VALUES ('$reportid','$value','$_POST[room]','$roomreportid','0');";
				mysql_query($sql_more,$db) or print "Error with additional inserts " . mysql_error();
			}
		}
				
*/
		CloseConnection($db);

	}
/*	else {  // ***************************************************************** OTHER CALLS - NOT CLASSROOM SUPPORT
		if ($_POST['otherCalls']=='10000'){$_POST['otherCalls']='unknown';}; // this should probaly be removed - it will never be 10000 now that i'm subbmitting it through the form before confirmation
		$summary .="<br />Reason for Call: ".$_POST['otherCalls'];
		
		$db = CreateConnection();
		$sql = "INSERT INTO report_building (reportedby, building_id, phone_code) VALUES ('$_SESSION[user]', '0', '$_POST[otherCalls]')"; // VALUE OF ZERO FOR BUILDING_ID B/C THERE IS NO BUILDING, ITS NON-CLASSROOM SUP.
		mysql_query($sql,$db) or print "Error inserting data ".mysql_error();

		$reportid = mysql_insert_id($db); // SETS $REPORTID TO THE VALUE OF THE ID JUST AUTO-INSERTED INTO THE REPORT_BUILDING TABLE
	
		$new_sql = "INSERT INTO call_details (report_id, notes, reason) VALUES ('$reportid','$_POST[notes]','$_POST[otherCalls]')";  
		mysql_query($new_sql,$db) or print "Error with second insert ".mysql_error();

		CloseConnection($db);
	}

	$summary .= "<br />NOTES: ".$_POST['notes'];
*/
	echo $summary;
	}

//** **************************** GET LIST OF POSSIBLE REASONS FOR NON-CLASSROOM SUPPORT CALLS *************** **//

function getReasons(){
	$callReasons = array();

	$db = CreateConnection();
	$sql = "SELECT * FROM lu_calls WHERE id > 100;";
	$result = mysql_query($sql,$db) or print "Error with getting call reasons list!!  " . mysql_error();

	while ($row = mysql_fetch_assoc($result)){
			array_push($callReasons,$row);
	}
	CloseConnection($db);

	return $callReasons;
}

//** ************************** reporting functions ******************************** **//
// FOR NOW THESE ARE BEING USED IN THE TESTFORCALLS.PHP FILE NOW CALLED CALLLIST.PHP

function getClassroomCalls($thisDay){
	$allCalls = array();

	$db = CreateConnection();
	//$thisDay = date("Ymd");                     // THIS GETS ALL THE INFO FOR THE CALLS THAT ******* WERE CLASSROOM SUPPORT *********
	//$sql = "SELECT *, report_details.id as the_id from report_details JOIN report_building ON report_details.report_id=report_building.id WHERE DATE_FORMAT(report_details.datecreated, '%Y%m%d') = '".$thisDay."' AND phone_code != '0' ORDER BY report_details.datecreated DESC; ";
	$sql = "SELECT * from calls WHERE DATE_FORMAT(datecreated, '%Y%m%d') = '".$thisDay."' ORDER BY datecreated DESC";
	$result = mysql_query($sql,$db) or print "Error with showCalls: ". mysql_error();

	while ($row = mysql_fetch_assoc($result)){
			array_push($allCalls,$row);
	}
	CloseConnection($db);

	return $allCalls;
}
function getOtherCalls($thisDay){
	$allCalls = array();

	$db = CreateConnection();
	//$thisDay = date("Ymd");                     // THIS GETS ALL THE INFO FOR THE CALLS THAT WERE ******* NOT CLASSROOM SUPPORT *********
	$sql = "SELECT * from othercalls WHERE DATE_FORMAT(datecreated, '%Y%m%d') = '".$thisDay."' ORDER BY datecreated DESC;";
	//$sql = "SELECT * from call_details JOIN report_building ON call_details.report_id=report_building.id;";
	$result = mysql_query($sql,$db) or print "Error with showCalls: ". mysql_error();

	while ($row = mysql_fetch_assoc($result)){
			array_push($allCalls,$row);
	}
	CloseConnection($db);

	return $allCalls;
}

if (isset($_POST['id'])) {
	$new_update = $_POST['old_status']." ".strtoupper($_SESSION['user']).": UPDATE: ".$_POST['new_text'];
	$new_update = trim(urlencode($new_update));
	str_replace("\r\n", " ", $new_update);
	update_one_call($_POST['id'],$new_update);
	}
	
function update_one_call($id,$update){
	$db = CreateConnection();
	$sql = "UPDATE `report_details` SET status = '$update' WHERE id = '$id';";
	$result = mysql_query($sql,$db) or print "Error with it ". mysql_error();
	CloseConnection;
	}

if ($_POST['action'] == 'updateRow') {

	$db = CreateConnection();
	$sql = "UPDATE `calls` SET user_error = '" . $_POST['problem'] . "', solved_tech = '" . $_POST['solved'] . "', ticket_num = '" . $_POST['rt'] . "'";

	if (is_numeric($_POST['rt'])) {
		$sql .= ", rt_ticket = 'yes' ";
	} else {
		$sql .= ", rt_ticket = 'no' ";
	}

	$sql .= " WHERE id = '" . $_POST['rowNum'] . "';";
	$result = mysql_query($sql,$db) or print "Error with updating row ...... " . mysql_error();
	CloseConnection;

}


function listCalls(){
	if (!$_GET) {
		$report_day = date("Ymd");
	} else {
		$report_day = $_GET['go'];
	}

	$call_list = getOtherCalls($report_day);
	$another_call_list = getClassroomCalls($report_day);
	$list = "<h3>Classroom-Support Calls:</h3>";
	$list .= "<table border='1' width='100%' style='text-align:center;'><tr><th>Edit</td><th>Date</th><th>Reported By</th><th>Room</th><th>Resolved Over Phone?</th><th>Reported Type of Problem</th><th>Sent a Tech?</th><th>Tech Solved Problem?</th><th>Confirm Type of Problem</th><th>RT Ticket #</th></tr>";

	foreach($another_call_list as $row){ 
		if ($row['rt_ticket'] == "yes") {
			if ($row['ticket_num'] == 0) {
				$rt = "<a href='https://rt.wesleyan.edu/Search/Simple.html' target='_blank'>RT</a>";
			} else { 
				$rt = "<a href='https://rt.wesleyan.edu/Ticket/Display.html?id=" . $row['ticket_num'] . "' target='_blank'>" . $row['ticket_num'] . "</a>";
			}
		} else {
			$rt = "No ticket was created";
		}
		if ($row['user_error'] == "yes") {
			$problem = "User Error";
		} else if ($row['user_error'] == "no") {
			$problem = "Equipment Failure";
		} else if ($row['user_error'] == "software") {
			$problem = "Software Issue";
		} else {
			$problem = "Unknown";
		}
		if ($row['send_tech'] != "no") {
			$row['send_tech'] = "Yes, from " . $row['send_tech'];
		}

		$trimmed_status = trim($row['status']);
		$trimmed_status = stripslashes(urldecode($trimmed_status));
		$trimmed_status = str_replace("\r\n", " ", $trimmed_status);
		$trimmed_status = str_replace("'", "\'", $trimmed_status);
		$edit_me = $trimmed_status."<br /><form name=\'edit_info_form_".$row['the_id']."\' style=\'margin:10px;\' action=\'\' method=\'post\' ><textarea name=\'new_text\' cols=\'60\' rows=\'3\'></textarea><input type=\'hidden\' name=\'id\' value=\'".$row['the_id']."\' /><input type=\'hidden\' name=\'old_status\' value=\'".$trimmed_status."\' /><input type=\'submit\' value=\'update\' style=\'margin:10px;\' /></form>";
		// ******* CHANGING THE "EDIT" FUNCTION HERE - WAS <A HREF='JAVASCRIPT:EDITROWN(" . $ROW['ID'] . ")'>EDIT</A>
		$list .= "<tr id='row_num_" . $row['id'] . "'><td id='edit_" . $row['id'] . "'><a href='editcall.php?id=" . $row['id'] . "'>Edit</a></td><td>" . $row['datecreated'] . "</td><td>" . $row['reported_by'] . "</td><td> " . $row['room_id'] . "</td><td>" . $row['resolved_phone'] . "</td><td id='problem_" . $row['id'] . "'>" . $problem . "</td><td>" . $row['send_tech'] . "</td><td id='solved_" . $row['id'] . "'>" . $row['solved_tech'] . "</td><td>Same as Reported</td><td id='rt_" . $row['id'] . "'>" . $rt . "</td></tr>";
	}
	$list .= "</table>";

	$list .= "<h3>Other Calls:</h3>";
	$list .= "<table border='1' width='100%'><tr><th width='15%'>Date</th><th width='15%'>Reported By</th><th width='15%'>Reason for Call</th></tr>";
	foreach($call_list as $row){
	//	$row['notes'] = stripslashes(urldecode($row['notes']));
	//	$row['phone_code'] = urldecode($row['phone_code']);
		$list .= "<tr><td>" . $row['datecreated'] . "</td><td>" . $row['reported_by'] . "</td><td> " . $row['reason'] . "</td></tr>";
	}
	$list .= "</table>";


	$yest = $report_day-1;
	if (substr($yest, 6)=="00") {
		$yest = $yest-69;
	}
	$tomo = $report_day+1;
	if (substr($tomo, 6)=="32") {
		$tomo = $tomo+69;
	}
	$display_day = substr($report_day,4,2)." - ".substr($report_day, 6, 2)." - ".substr($report_day,0,4);
	echo "<div id='report_date' style='width:200px; margin-left:auto; margin-right:auto;'>".
		"<a href='?go=".$yest."' title='previous day' style='text-decoration:none;'> << </a> &nbsp; ".$display_day." &nbsp; <a href='?go=".$tomo."' title='next day' style='text-decoration:none;'> >> </a>".
		"</div>";
	echo $list;


}
?>
