<?php
/* ***************************************************************************************************************************************************** */
/* ******************************************** Transfering this drawRoomsPie function from pies.ind.php *********************************************** */
/* ******************************************** in hopes that it will make my $_SESSION variables immediately available ******************************** */
/* ***************************************************************************************************************************************************** */

function drawRoomsPie($slice) {
	$db = CreateConnection();
	$sql = "SELECT * FROM lu_buildings WHERE active = 'Y'";
	$result = mysql_query($sql,$db) or print "Problem with looking up the buildings list. " . mysql_error();

	while ($row = mysql_fetch_assoc($result)) {
		if ($_POST['slice'] == $row['building']) {
			$build_id = $row['id'];
		}
	}

	$sql = "SELECT user_error, room_id FROM calls WHERE building_id = '" . $build_id . "' AND datecreated >= '" . $_SESSION['start_date'] . "' AND datecreated <= '" . $_SESSION['end_date'] . "'";
	$compare_error_vs_problems = mysql_query($sql,$db) or print "Problem looking up the info to compare User Error vs. Equipment Problems. " . mysql_error();

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
			if ($room == $rom['room_id']) {
				switch ($row['user_error']) {
					case "yes":
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

	$list_of_rooms = "";
	$list_of_numbers = "";
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

	$_SESSION['numbers'] = $list_of_numbers;
	$_SESSION['rooms'] = $list_of_rooms;

	print $list_of_numbers;
	print $list_of_rooms;
}

?>
