<?php
session_start();

include("../../../checklist/includes/db.inc.php");
//include("drawroomspie.php");

/* ***************************************************************************************************************************** */

if ($_POST['action']=="whatDidTheyClick") {

	$pie_type = $_POST['pie_type'];
	$slice = $_POST['slice'];	// (SO I DON'T HAVE TO KEEP TYPEING "$_POST" OVER AND OVER)

	if ($pie_type == "building") {
		drawRoomsPie($slice);
	} else if ($pie_type == "problems") {
		drawProblemsPie($slice);
	} else if ($pie_type == "error") {
		; // do nothing
	}

}

/* ****************************************************************************************************************************** */
/* ********* This is the original second pie for the "Calls by Building". We will still want to use this. But for now I am making a new function instead.
             Eventually we will want to give people the option of seeing either one. ******************************************  */
/* 
function drawRoomsPie($slice) {

	$db = CreateConnection();
	$sql = "SELECT * FROM lu_buildings WHERE active = 'Y'";
	$result = mysql_query($sql,$db) or print "Oh NO! " . mysql_error();
	CloseConnection($db);

	while ($row = mysql_fetch_assoc($result)) {
		if ($_POST['slice']==$row['building']) {
			$build_id = $row['id'];
		}
	}
	// get the info about that building specificallyi
	$db = CreateConnection();
	$sql = "SELECT field FROM lu_rooms WHERE building = '" . $build_id . "'";
	$room_list = mysql_query($sql,$db) or print "Problem looking up the list of rooms. " . mysql_error();
	CloseConnection($db);
	$room_array = array();
	while ($row = mysql_fetch_assoc($room_list)) {
		$room = $row['field'];
		array_push($room_array,$room);
	}
	foreach($room_array as $room) {
		$room_count_array[$room] = 0;
	}

	$db = CreateConnection();
	$sql = "SELECT room_id FROM calls WHERE building_id = '" . $build_id . "' AND datecreated >= '" . $_SESSION['start_date'] . "' AND datecreated <= '" . $_SESSION['end_date'] . "'";
	$building_problems = mysql_query($sql,$db) or print "Problem looking up the building problems. " . mysql_error();
	CloseConnection($db);
	while ($row = mysql_fetch_assoc($building_problems)) {
		foreach($room_count_array as $room => $val) {
			if ($room == $row['room_id']) {
				$room_count_array[$row['room_id']]++;
			}
		}
	}
	// $room_count_array = array_reverse($room_count_array); ****** THIS COULD BE USED TO SWITCH THE ARRANGEMENT OF THE PIE AROUND TO MAKE IT MORE READABLE ??????? HOW TO IMPLEMENT???
	//shuffle($room_count_array);
	foreach ($room_count_array as $row => $val) {
		if ($val > 0) {
			print "&" . $row . "=" . $val;
		}
	}
}
*/

/* ************************************* new DrawRoomsPie function ************************************************************** */

function drawRoomsPie($slice) { // This doesn't actually draw a pie, it draws one of those bar graph things
	
	unset($_SESSION['numbers']);
	unset($_SESSION['rooms']); // yes, i know i'm unsetting these in 2 places. i *reeeeally* want to UNset them, mofo.

	$db = CreateConnection();
	$sql = "SELECT * FROM lu_buildings WHERE active = 'Y'";
	$result = mysql_query($sql,$db) or print "Problem with the building lookup. " .mysql_error();
	
	while ($row = mysql_fetch_assoc($result)) {
		if ($_POST['slice'] == $row['building']) {
			$build_id = $row['id'];
		}
	}
	// get speciafic info about that building
	$sql = "SELECT user_error, room_id FROM calls WHERE building_id = '" . $build_id . "' AND datecreated >= '" . $_SESSION['start_date'] . "' AND datecreated <= '" . $_SESSION['end_date'] . "'";
	$compare_error_vs_problems = mysql_query($sql,$db) or print "Something went wrong looking up the info about User Error vs. Equipment Problems. " . mysql_error();
	// make a list of rooms in that building so we can start adding stuff up
	$sql = "SELECT field FROM lu_rooms WHERE building = '" . $build_id . "'";
	$room_list = mysql_query($sql,$db) or print "Problem looking up the list of rooms. " . mysql_error();
	$room_array = array();
	while ($row = mysql_fetch_assoc($room_list)) {
		$room = $row['field'];
		array_push($room_array,$room);
	}
	foreach ($room_array as $room) {
		$room_count_array[$room][error] = 0;
		$room_count_array[$room][equipment] = 0;
		$room_count_array[$room][software] = 0;
		$room_count_array[$room][other] = 0;
	}
	CloseConnection($db);

	while ($row = mysql_fetch_assoc($compare_error_vs_problems)) {
		foreach ($room_count_array as $room => $val) {
			if ($room == $row['room_id']) {
				switch ($row['user_error']) {           // ($val)?
					case "yes": // Yes, is user error
						$room_count_array[$row['room_id']][error]++;
						break;
					case "no":
						$room_count_array[$row['room_id']][equipment]++;
						break;
					case "software":
						$room_count_array[$row['room_id']][software]++;
						break;
					default:
						$room_count_array[$row['room_id']][other]++;
				}
			}
		}
	}
//	var_dump($room_count_array);
/*	foreach ($room_count_array as $room => $val) {
		print "&room=" . $room;
		foreach ($room_count_array[$room] as $category => $num) {
			print "&" . $category . "=" . $num;
		}
	} */
	$list_of_rooms = "";
	$list_of_numbers ="";
	foreach($room_count_array as $room => $val) {
		$list_of_rooms .= "'" . $room . "',";
		$list_of_numbers .= "[";
		foreach ($room_count_array[$room] as $category => $num) {
			$list_of_numbers .= $num . ",";
		}
		$list_of_numbers = substr($list_of_numbers,0,-1); // trailing comma
		$list_of_numbers .= "],";
	}
	$list_of_numbers = substr($list_of_numbers,0,-1);
	$list_of_rooms = substr($list_of_rooms,0,-1);
	//$list_of_rooms .= "]";
	$lists = array($list_of_numbers,$list_of_rooms);
	
	unset($_SESSION['numbers']);
	unset($_SESSION['rooms']);
	$_SESSION['numbers'] = $list_of_numbers;
	$_SESSION['rooms'] = $list_of_rooms;
	
	var_dump($_SESSION);	
	print $list_of_numbers;
}
/* ****************************************************************************************************************************** */

function drawProblemsPie($slice) {

	if ($slice == "Other" ) {

	//	print "You have clicked the 'other' slice. ";

		$db = CreateConnection();
		$sql = "SELECT problem FROM problems, calls WHERE calls.id = problems.call_id AND calls.datecreated >= '" . $_SESSION['start_date'] . "' AND calls.datecreated <= '" . $_SESSION['end_date'] . "'";
		$result = mysql_query($sql,$db) or print "Oh noes! problem looking up all the problems around here! " . mysql_error();
		CloseConnection($db);

		$problems_count = array();
		while ($row = mysql_fetch_assoc($result)) {
			array_push($problems_count, ucfirst($row['problem']));
		}
		$counted_problems = array_count_values($problems_count);

		// We need to eliminat the problems that are not "Others" - the ones that are in the actual list ->
		$db = CreateConnection();
		$sql = "SELECT item FROM list WHERE 1";
		$result = mysql_query($sql,$db) or print "carps! problem looking up the list items! " . mysql_error();
		CloseConnection($db);
		$list_items = array();
		while ($row = mysql_fetch_assoc($result)) {
			array_push($list_items,$row);
		}
		
		$list = array();
		foreach ($list_items as $key => $val) {
			array_push($list, $val['item']);
		}

		$edited_array = array();
		foreach ($counted_problems as $key => $val) {
			$count = 0;
			foreach ($list as $key2 => $val2) {
				if ($key == $val2) {
					$count++;
				}
			}

			if (!isset($min_num_occurrences)) {
				$min_num_occurrences = 2;
			} // MIGHT ADD IN A THING LATER WHERE THE USER CAN CHANGE THEY MIN NUMBER
			if ($count == 0) {
				if ($val >= $min_num_occurrences) { // WE ARE ONLY ADDING IT TO OUR LISTIF THERE ARE MORE THAN THIS NUMBER OF REPORTS OF THE PROBLEM
					$edited_array[$key] = $val;  // THERE ARE A TON OF THINGS THAT ARE REPORTED ONLY ONCE, AND IT IS TOO MUCH TO FIT IN ONE PIE!!!!!!!!!
				}
			}
		}
		//var_dump($edited_array);  // ******* $EDITED_ARRAY IS NOW ALL THE RPBELMS THAT WERE NOT PART OF THE REGULAR LIST (AS KEY) AND VALUE IS THE NUMBER OF OCCURRENCES
		// 	NOW DRAW A PIE SHOWING WHAT THOSE OTHER THINGS WERE THAT PEOPLE WROTE IN....
		foreach ($edited_array as $key => $val) {
			print "&" . $key . "=" . $val;
		}

	} else {

		// do something else:  *** DRAW PIE SHOWING THE ROOMS/BUILDINGS WHERE THIS PROBLEM WAS REPORTED
//		print "You have clicked on the " . $slice . " slice.";
		$db = CreateConnection();
		$sql = "SELECT building_id FROM problems, calls WHERE problem = '" . $slice . "' AND calls.id = problems.call_id AND datecreated >= '" . $_SESSION['start_date'] . "' AND datecreated <= '" . $_SESSION['end_date'] . "'";
		$result = mysql_query($sql,$db);
		$reported_problem_build_id = array();
		while ($row = mysql_fetch_assoc($result)) {
//			array_push($reported_problem_build_id, $row);
			$reported_problem_build_id[$row['building_id']]++;
		}

		$sql = "SELECT building, id FROM lu_buildings WHERE active = 'Y'";
		$result = mysql_query($sql,$db);
		$building_list = array();
		while ($row = mysql_fetch_assoc($result)) {
			array_push($building_list, $row);
		}
		
		foreach ($reported_problem_build_id as $key => $val) {
			foreach($building_list as $building => $id) {
				if ($id['id'] == $key) {
					print "&" . $id['building'] . "=" . $val;
				}
			}
		}
	}

}

/* ****************************************************************************************************************************** */



















function didTheyClickEquipment() {

	$db = CreateConnection();
	$sql = "SELECT * FROM list WHERE 1";
	$result = mysql_query($sql,$db) or print "Oh Noes! " . mysql_error();
	CloseConnection($db);

	while ($row = mysql_fetch_assoc($result)) {
		if ($_POST['slice'] == $row['item']) {
			// IT IS AN EQUPIMENT ITEM
			$must_draw_pie = 1;
			$pie_type = "e";
			$equip = $row['item'];
		}
	}
	if ($_POST['slice'] == "Other") {
		$must_draw_pie = 1;
		$pie_type = "e";
		$equip = "Other";
	}
	// ************** TESTING
//	if ($is_equipment == 1) {
//		print "EQUIPMENT " . $_POST['slice'] . " " . $equip;
//	}
	// END TEST
}


if ($_POST['action']=="wwwwwwwwwwwwwwwwwwwwwwwwwwwwwwhatDidTheyClick") { // || $_POST['action']=="didTheyClickOnABuilding") {  // THIS DOES NOT MAKE SENSE TO ME. "didTheyClickOnABuilding" is not used anywhere as far as I can tell.

	$must_draw_pie = 0; // THIS TELLS US IF WE NEED TO DRAW A PIE OR NOT
	$pie_type = "x";	// THes TELLS US WHY WE NEED TO DRAW THE PIE
				// AND WHAT TYPE OF PIE TO DRAW - "e" will be equipment, "b" will be building

	$db = CreateConnection();
	$sql = "SELECT * FROM lu_buildings WHERE active = 'Y'";
	$result = mysql_query($sql,$db) or print "Oh NO! " . mysql_error();
	CloseConnection($db);

	while ($row = mysql_fetch_assoc($result)) {
		if ($_POST['slice']==$row['building']) {
			// Yes, it is a building!
			$pie_type = "b";
			$must_draw_pie = 1;
			$build_id = $row['id'];
		}
	}
	if ($pie_type == "x") { // pie_type hasn't changed - so check to see if its the equipment type of pie
		didTheyClickEquipment();
	}

//	print $is_building;

	if ($pie_type == "b") {  //******************************************** YES THEY DID CLICK ON A BUILDING!!! *****************

		print $must_draw_pie;
		// get the info about that building specificallyi
		$db = CreateConnection();
		$sql = "SELECT field FROM lu_rooms WHERE building = '" . $build_id . "'";
		$room_list = mysql_query($sql,$db) or print "Problem looking up the list of rooms. " . mysql_error();
		CloseConnection($db);
		$room_array = array();
		while ($row = mysql_fetch_assoc($room_list)) {
			$room = $row['field'];
			array_push($room_array,$room);
		}
		foreach($room_array as $room) {
			$room_count_array[$room] = 0;
		}


		$db = CreateConnection();
		$sql = "SELECT room_id FROM calls WHERE building_id = '" . $build_id . "' AND datecreated >= '" . $_SESSION['start_date'] . "' AND datecreated <= '" . $_SESSION['end_date'] . "'";
		$building_problems = mysql_query($sql,$db) or print "Problem looking up the building problems. " . mysql_error();
		CloseConnection($db);

		while ($row = mysql_fetch_assoc($building_problems)) {
			foreach($room_count_array as $room => $val) {
				if ($room == $row['room_id']) {
					$room_count_array[$row['room_id']]++;
				}
			}

		}

		// $room_count_array = array_reverse($room_count_array); ****** THIS COULD BE USED TO SWITCH THE ARRANGEMENT OF THE PIE AROUND TO MAKE IT MORE READABLE ??????? HOW TO IMPLEMENT???
		//shuffle($room_count_array);

		foreach ($room_count_array as $row => $val) {
			if ($val > 0) {
				print "&" . $row . "=" . $val;
			}
		}
	} else if ($pie_type == "e") { //************************************* THEY ARE IN THE 'EQUIPMENT PROBLEMS IN CLASSROOMS' SECTION, AND CLICKED ON SOMETHING *****************

		print $must_draw_pie;

	}
}





	

?>
