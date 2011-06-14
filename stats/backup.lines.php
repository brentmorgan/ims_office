<?php // ob_start('ob_gzhandler')
session_start();

$DEBUG = 0; // SET TO 1 TO SEE DEBUG INFO, SET TO 0 TO NOT SEE IT!!!!!!! OR SET IT TO 2 (OR MORE) TO SEE A WHOLE BUNCH OF STUFF

include("../../../checklist/includes/db.inc.php");

function grabBuildings() {
	$db = CreateConnection();
	$sql = "SELECT * FROM lu_buildings WHERE active = 'Y' ORDER BY building";
	$result = mysql_query($sql,$db) or print "Oh no! " . mysql_error();
	CloseConnection($db);
	return $result;
}

function grabRooms($building) {
	$db = CreateConnection();
	$sql = "SELECT * FROM lu_rooms WHERE building = " . $building;

	$result = mysql_query($sql,$db) or print "Oh no! " . mysql_error();
	CloseConnection($db);
	return $result;
}


function getNewInformation($which) { // *********************** This builds and executes  ONE new query each time it is run *************************
					// ******* returning an array of dates and times to be counted up later *************************************

	$what_select = "datecreated, id, year, month, week"; // TAKING AWAY THE OPTION OF TIMEPERIOD SELECTION, ONLY WILL DO IT BY WEEKS NOW

	$loc = "no"; //*************** BY default LOC(ation) flag is set to NO
	$which_tables = array();
	switch ($which) {
		case "calls_all":
			array_push($which_tables,"calls","othercalls");
			break;
		case "calls_cs":
			array_push($which_tables,"calls");
			break;
		case "calls_ot":
			array_push($which_tables,"othercalls");
			break;
		default: // *************************************** If $which doesn't match any of the above criteria, it must be a LOCATION instead!!!!!
			array_push($which_tables,"calls");
			$loc = "yes"; // ************************** LOC(ation) flag is set to YES
			break;
	}
 
	$what_dates = "datecreated >= '" . date("Y-m-d H:i:s",$_SESSION['start_date']) . "' AND datecreated <= '" . date("Y-m-d H:i:s",$_SESSION['end_date']) . "'";
	
	$db = CreateConnection();
	$sql = "";
	foreach ($which_tables as $row) {
		$sql .= "(SELECT " . $what_select;
		$sql .= " FROM " . $row;
		$sql .= " WHERE " . $what_dates;
		if ($row == "calls") { $sql .= " AND building_id != '22'"; } // ******* TEST BUILDING 
		if ($loc == "yes") { $sql .= " AND building_id = " . $which; }// ******* IF THEY HAVE SELECTED A PARTICULAR BUILDING
		$sql .= ")";
		if (count($which_tables) == 2 && $row == "calls") { $sql .= " UNION "; }
	}
	$sql .= " ORDER BY datecreated ASC";

	$result = mysql_query($sql,$db) or print "Oh NO! " . $sql . " * * * * " . mysql_error();
	$information = array();
	while ($row = mysql_fetch_assoc($result)) {
		array_push($information, $row);
	}
	if ($DEBUG >= 2) {
		var_dump($information);
	}
	return $information;
}



// **************************************************************************************************************************************************************
// ************************************************** THIS HAS TO BE THE FIRST THING TO HAPPEN ******************************************************************
// ****************************** THIS SETS UP THE DATE RANGE, SETS THE NUMBER OF QUERIES, AND CREATES THE $INFORMATION[] ARRAY.... *****************************
// ****************************** .... EACH ARRAY ELEMENT WILL HOLD THE INFO OF ONE PARTICULAR LINE / QUERY *****************************************************
// **************************************************************************************************************************************************************

if ($_POST) {
//	$start_date = date("Y-m-d H:i:s", mktime(0,0,0,$_POST['st_mo'],$_POST['st_da'],$_POST['st_ye']));
//	$end_date = date("Y-m-d H:i:s", mktime(23,59,59,$_POST['en_mo'],$_POST['en_da'],$_POST['en_ye']));
	$start_date = mktime(0,0,0,$_POST['st_mo'],$_POST['st_da'],$_POST['st_ye']);
	$end_date = mktime(23,59,59,$_POST['en_mo'],$_POST['en_da'],$_POST['en_ye']);

	$_SESSION['start_date'] = $start_date;
	$_SESSION['end_date'] = $end_date;	// We're going to need to access these dates in various places, its probably easiest to just make them session variables

	// ********************************************************************************************************************************************************
	// ******************** First we need to figure out how many LINES will be on the graph, and thus how many QUERIES we will need ***************************
	// ********************************************************************************************************************************************************
	$number_of_queries = 1; // DEFAULT value
				// this will only change if the MORE INFO checkbox IS CHECKED, and if they select certain Calls By values....
	if ($_POST['more_info'] == 'on') {
		$which_calls = array("nothing");     // ********* $which_calls[0] will be nothing, we will set the rest of the array elements to 
							// ****** the values of each particular LINE of the graph
		if ($_POST['calls_by'] == 'all_calls') {
			$number_of_queries = 3;
			$which_calls[1] = "calls_all";
			$which_calls[2] = "calls_cs";				// *********************************************************************************
			$which_calls[3] = "calls_ot";				// ** Need to set up these different possibilities of combinations of quries *******
		} elseif ($_POST['calls_by'] == 'by_location') {
			$number_of_queries = 2;
			$which_calls[1] = "calls_cs";
			$which_calls[2] = $_POST['location'];
		}
	} else { // ***************************** the MORE INFO checkbox is NOT CHECKED!!!!
		switch($_POST['calls_by']) {
			case "all_calls":
				$which_calls[1] = "calls_all";
				break;
			case "cs_calls":
				$which_calls[1] = "calls_cs";
				break;
			case "other_calls":
				$which_calls[1] = "calls_ot";
				break;
			default:		// **** if none of the above values were selected, it must be a LOCATION
				$which_calls[1] = $_POST['location'];
				break;
		}
	}	

	// ****************** Then we run each of the queries ****************************************************************************************************
	$information = array();
	
	for ($i=1; $i <= $number_of_queries; $i++) {
		$information[$i] = getNewInformation($which_calls[$i]);
	}

	$all_values_and_labels = createOneAndOnlyValues($information);

}
// ***************************************************************************************************************************************************************
// ************************************************ ALL QUERIES HAVE BEEN EXECUTED, AND THE $INFORMATION[] ARRAY CREATED *****************************************
// ***************************************************************************************************************************************************************







	
	// ******************************************************************************************************************************************************** 
        // ********************* THIS PART CREATES AN ARRAY $ALL_RECORDS - IT HAS TO BE CREATED ONCE FOR EACH QUERY !!!!!!!!!!!! **********************************
	// ********************************************************************************************************************************************************
function makeOneAllRecords() {

	$first_week_of_month = array(0,1,5,9,13,18,22,27,31,35,40,44,48); // $first_week_of_month[$month_number] gives the week of the year of the start of the month

	// First we make an array conatining each year of our select time period
	$all_records = array();
	
	if ((int)$_POST['st_ye'] == $_POST['en_ye']) {
		$all_records[(int)$_POST['st_ye']] = array();
	} else {
		$all_records = array();
		for ($i = (int)$_POST['st_ye']; $i <= $_POST['en_ye']; $i++) {
			$all_records[$i] = array();
		}
	}

	// Second we make arrays for the first week of the first month of our time period and the first week of the last month
	//	$all_records[(int)$_POST['st_ye']][$_POST['st_mo']] = array(1 => 0, 2 =>0, 3 => 0, 4 => 0);
	//	$all_records[$_POST['en_ye']][$_POST['en_mo']] = array(1 => 0, 2 => 0, 3 => 0, 4 => 0); 	// these made arrays by MONTH, but we're donig it by week of the year instead

	$all_records[(int)$_POST['st_ye']][$first_week_of_month[$_POST['st_mo']]] = 0;
	$all_records[$_POST['en_ye']][$first_week_of_month[$_POST['en_mo']]] = 0;

	// Third we fill in the arrays with all the months (and weeks) in between the first one and the last one
	foreach ($all_records as $row => $val) {
		if ($row == (int)$_POST['st_ye']) {
			$start = $first_week_of_month[$_POST['st_mo']];
		} 
		if ($row == $_POST['en_ye']) {
			$end = $first_week_of_month[$_POST['en_mo']] + 3;
		}
		if (!isset($start)) {
			$start = 1;
		}
		if (!isset($end)) {
			$end = 52;
		}
		for ($i = $start; $i<=$end; $i++) {
			if (!isset($all_records[$row][$i])) {
				$all_records[$row][$i] = 0; //  ...[$i] = array();
			
			/*	for ($w = 1; $w <= 4; $w++) {
					$all_records[$row][$i][$w] = 0; // put 4 weeks into each month, each one with a value of zero. each call will add one to it.
				}	
			*/				// yes I realize some months should have 5 weeks, but not sure how to deal with that atm
			}
		}
		unset($start);
		unset($end);
	}
	if ($DEBUG >= 2) {
		var_dump($all_records); // this is now basically a big array of zeros, with a few YEAR indicators
	}

	return $all_records;
	// ******************************** END ******** ONE INSTANCE OF $ALL_RECORDS HAS BEEN CREATED **************************************************************************
}
	// **********************************************************************************************************************************************************************
	// ************************* OK so now we have a big array ($all_records) containing no information, but it is an array of YEARS, each containing an array of WEEK NUMBERS
	// ************************* these week #s span the whole Date Range, and each one has a value of zero (this eliminates the problem of weeks with no calls beging omitted from the data).
	// ************************* Now we need to add up how many calls actually cam in for each week.
	// **********************************************************************************************************************************************************************




	//***********************************************************************************************************************************************************************
	// ***************!!!!!!!!!!!!!!!!!!!******************** Final Step !!!!!!!!!!!! *********************************************!!!!!!!!!!!!!!!!!!!!!! *******************
	// **********************************************************************************************************************************************************************
	// ************************************ THIS CREATES THE $VALUES[] ARRAY - and FINALY the $ALL_VALES[] Array ***** THIS IS DONE ONLY ONCE *******************************
	// ************************************************** DEPENDS ON THE $INFORMATION[] ARRAY *******************************************************************************
	// **********************************************************************************************************************************************************************
function createOneAndOnlyValues($information) {

	$week_months = array(1 => "Jan", 5 =>"Feb", 9=>"Mar", 13=>"Apr",18=>"May",22=>"Jun",27=>"Jul",31=>"Aug",35=>"Sep",40=>"Oct",44=>"Nov",48=>"Dec");

	$values = array();

	foreach($information as $line => $data) {
		$all_records = makeOneAllRecords(); // create the empty timeline
		foreach($data as $key => $val) {
			if ($DEBUG >= 2) {
				print "$key : ";
				var_dump($val);
				print "<br/>";
			}
			$all_records[$val['year']][$val['week']]++; // fills values into the empty timeline
		}

		foreach ($all_records as $row => $val) {
			ksort($all_records[$row]); // sort it so all the months are in chronological order
		}
	
		if ($DEBUG >= 2) {
			var_dump($all_records);
		}

		$labels = "["; // We only want a single set of Labels
		$values[$line] = "["; // But we can have multiple sets of Values if we're running multiple QUERIES

		foreach ($all_records as $key => $val) { // $key is a single YEAR, $val is an array of WEEKS
			$labels .= "'" . $key . " ";
			foreach ($val as $clef => $value) { // $clef is a week #, $value is the value
				if(isset($week_months[$clef])) {
					$labels .= $week_months[$clef]; // if the week number is the first week of a month we insert the MONTH NAME
				} else {
					$labels .= " . ";
				}
			$labels .= "','";
			$values[$line] .= $value . ",";
			}
		$labels = substr($labels,0,-1); // remove trailing opening paren before begining new YEAR with a opening paren above
		}

		$labels = substr($labels,0,-1); // remove trailing comma
		$values[$line] = substr($values[$line],0,-1); // remove comma
		$labels .= "]";
		$values[$line] .= "]"; 
	}

	$all_values = ""; // If there were multiple QUERIES we make one string of all the VALUES, with each LINE's values separated by a comma
	foreach ($values as $key => $val) {
		$all_values .= $val . ",";
	}
	$all_values = substr($all_values, 0,-1); // remove the trailing comma
	$all_values_and_labels = array($all_values, $labels);
	return $all_values_and_labels;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <!--
        /**
        * o------------------------------------------------------------------------------o
        * | This file is part of the RGraph package - you can learn more at:             |
        * |                                                                              |
        * |                          http://www.rgraph.net                               |
        * |                                                                              |
        * | This package is licensed under the RGraph license. For all kinds of business |
        * | purposes there is a small one-time licensing fee to pay and for personal,    |
        * | charity and educational purposes it is free to use. You can read the full    |
        * | license here:                                                                |
        * |                      http://www.rgraph.net/LICENSE.txt                       |
        * o------------------------------------------------------------------------------o
        */
    -->
    <title>Call Tracking Line Chart</title>
    <lllllllllllllllllllllink rel="stylesheet" href="website.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="stats.css" type="text/css" media="screen" />
  <!--  <link rel="icon" type="image/png" href="/favicon.png"> -->

	<script src="extraFunctions.js"></script>    
	<script src="RGraph.common.js" ></script>
	<script src="RGraph.line.js" ></script>
	<script src="../../../checklist/js/prototype.js" type="text/javascript"></script>
	<script src="../../../checklist/js/scriptaculous.js" type="text/javascript"></script>

    <!--[if IE]><script src="../excanvas/excanvas.compressed.js"></script><![endif]-->

	<script>
	
		/* CLEARS THE AREA OF THE OLD PIE SO THE NEW PIE CAN BE DRAWN WITHOUT SEEING THE OLD TEXT STILL AROUND IT */
/*		function clearPie(whichPie) {  // *************************************** DONT THINK THIS IS NECESSARY ON THIS PAGE, SINCE IT RELOADS EACH TIME
			var pie = document.getElementById(whichPie);
			var context = pie.getContext('2d');
			context.fillStyle = "rgb(255,255,255)";
			context.fillRect(0,0,5000,5000); // JUST DRAWS A BIG WHITE RECTANGE OVER THE OLD PIE
		}
*/
        	window.onload = function ()
		{
				
			// var data = <?php echo $all_values; if (!isset($all_values)) { echo "[0,0,0]"; } ?> ; // [10,4,17,50,25,19,20,25,30,29,30,29];
			
			var line1 = new RGraph.Line("line1", <?php echo $all_values_and_labels[0]; if (!isset($all_values_and_labels[0])){echo "[0,0,0]";} ?> );  //data);
			line1.Set('chart.background.barcolor1', 'rgba(255,255,255,1)');
			line1.Set('chart.background.barcolor2', 'rgba(255,255,255,1)');
			line1.Set('chart.background.barcolor3', 'rgba(238,238,238,1)');
			line1.Set('chart.colors', ['rgba(255,0,0,1)','rgba(0,255,0,1)','rgba(0,0,255,1)']);
			line1.Set('chart.linewidth', 2);
			line1.Set('chart.filled', false);
			line1.Set('chart.hmargin', 5);
			line1.Set('chart.labels', <?php echo $all_values_and_labels[1]; if (!isset($all_values_and_labels[1])) { echo "[0,0,0]"; } ?> ); // ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']);
			line1.Set('chart.gutter', 40);		// The formatting of the TOOLTIPS below needs to be different than the VALUES
			line1.Set('chart.tooltips', <?php 	$all_values_and_labels[0] = str_replace(array("[","]"), "", $all_values_and_labels[0]);
								$all_values_and_labels[0] = "[" . $all_values_and_labels[0] . "]";
								echo $all_values_and_labels[0]; 
								if (!isset($all_values_and_labels[0])) { 
									echo "[0,0,0]"; 
								} 
							?> );
			line1.Set('chart.key', [ 
						<?php
							for($i=1; $i < sizeof($which_calls); $i++) {
								print "'";	
									switch ($which_calls[$i]) {
										case "calls_all":
											print "All Calls";
											break;
										case "calls_cs":
											print "Classroom Support";
											break;
										case "calls_ot":
											print "Other Calls";
											break;
										default:
											print "Location";
											break;
									}
								print "'";
								if ($i < (sizeof($which_calls)-1)) {
									print ",";
								}
							}
						?>
						] );
			line1.Set('chart.shadow', true);
			line1.Set('chart.key.shadow', true);
			line1.Draw();
		}

		function checkCallsByValue(val) {
			if (val == 'by_location') {
				document.getElementById('location').style.display='';
			} else {
				document.getElementById('location').style.display='none';
			}
			if (val == 'cs_calls' || val == 'other_calls') {
				document.ui_form.more_info.checked = false;
				document.getElementById('span_more_info').style.display='none'
			} else {
				document.getElementById('span_more_info').style.display='';
			}
		}

    </script>
</head>
<body>
<?php 
	if ($DEBUG) {
		if ($_POST) {
			print $sql;
			print "<p> Values: "; var_dump($values); print "</p>";
			print "<p> Labels: "; var_dump($labels); print "</p>";
			print "<p> ALL V & L: "; var_dump($all_values_and_labels); print "</p>"; 
		}
	}
?>

<?php include("ui.php"); /************* Insert the User Interface here ********************/ ?>

    <div style="text-align: center">
        <canvas id="line1" width="1200" height="600">[No canvas support]</canvas> <!-- original width:440 height:300 -->
        <canvas id="line2" width="800" height="600">[No canvas support]</canvas> <!--
        <canvas id="pie3" width="450" height="300">[No canvas support]</canvas>
        <canvas id="pie4" width="450" height="300">[No canvas support]</canvas>
        <canvas id="pie5" width="400" height="300">[No canvas support]</canvas>
        <canvas id="pie6" width="370" height="280" style="border: 1px dotted gray">[No canvas support]</canvas>
-->   </div>

</body>
</html>
