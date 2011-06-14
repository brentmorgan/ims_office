<?php 
session_start();
include('../../checklist/includes/loggedin.php');
include('newcall.inc.php');
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="../../checklist/css/classroom.css" />
	<script type="text/javascript" src="../../checklist/js/prototype.js"></script>
	<script type="text/javascript" src="../../checklist/js/scriptaculous.js"></script>
	<script type="text/javascript" src="../../checklist/js/call.js"></script>
</head>
<body>
<?php 
	include("../../checklist/includes/menu.inc.php"); 
?>
<div id="whole_thing" style="width:100%; margin-right:auto; margin-left:auto;">
<?php 
	include("../../checklist/includes/calls_logo.php");

	$db = CreateConnection();
	$sql = "SELECT * FROM calls WHERE id = '" . $_GET['id'] . "';";
	$result = mysql_query($sql,$db) or print "Error getting data for that ID. " . mysql_error();
	CloseConnection($db);

	print "<div id='buttons' style='text-align:left; width:500px; margin:auto; position:relative; top:-70px;'>";
	while ($row = mysql_fetch_assoc($result)) {
											 // ******************************************************************
		print "<form name='boxes' method='post' action='edit.newcalllist.php'>"; //******************************** ACTION NEEDS TO BE CHANGED!!!!!!!!
		print "<input type='hidden' name='edit' value='yes' />";		 // *****************************************************************
		print "<input type='hidden' name='id' value='" . $_GET['id'] . "' />";
		print "<b>" . $row['room_id'] . "</b>";
		print "<br />Reported by: " . $row['reported_by'];
		print "<br />" . $row['day_of_week'] . " " . $row['datecreated'];
		print "<br />";
		$list =  "<br /><h4>Resolved over phone:</h4> <input type='radio' name='resolved_phone' value='yes' ";
		if ($row['resolved_phone']=='yes') {
			$list .= " checked ";
		}
		$list .= " />Yes <input type='radio' name='resolved_phone' value='no' ";
		if ($row['resolved_phone']=='no') {
			$list .= " checked ";
		}
		$list .= " />No";
		print $list;

		$list = "<br /><h4>Type of problem:</h4> <input type='radio' name='user_error' value='yes' onClick='Effect.SlideDown(\"checklist\")' ";
		if ($row['user_error']=='yes') {
			$list .= " checked ";
		}
		$list .= " />User Error <input type='radio' name='user_error' value='no' onClick='Effect.SlideDown(\"checklist\")' ";
		if ($row['user_error']=='no') {
			$list .= " checked ";
		}
		$list .= " />Equipment Failure <br />";
		$list .= "<input type='radio' name='user_error' value='software' ";
		if ($row['user_error']=='software') {
			$list .= " checked ";
		}
		$list .= " />Software Issue ";
		$list .= "<input type='radio' name='user_error' value='unsure' onClick='document.getElementById(\"checklist\").style.display=\"none\"'  ";
		if ($row['user_error']=='unsure') {
			$list .= " checked ";
		}
		$list .= " />Not Sure";
		print $list;


		$list = "<br /><h4>Sending a Technician?</h4> <input type='radio' name='send_tech' value='office' onClick='Effect.SlideDown(\"tech_solved_div\")' ";
                if ($row['send_tech']=='office') {
                        $list .= " checked ";
                }
                $list .= " />Yes, from Office <input type='radio' name='send_tech' value='lab' onClick='Effect.SlideDown(\"tech_solved_div\")' ";
                if ($row['send_tech']=='lab') {
                        $list .= " checked ";
                }
                $list .= " />Yes, from Lab <input type='radio' name='send_tech' value='no' onClick='document.getElementById(\"tech_solved_div\").style.display=\"none\"' ";
                if ($row['send_tech']=='no') {
                        $list .= " checked ";
                }
                $list .= " />No<br />";
               print $list;

		if ($row['send_tech'] == 'office' || $row['send_tech'] == 'lab') {
			$vis_tech = "";
		} else {
			$vis_tech = "none";
		}

		$list = "<div id='tech_solved_div' style='display:" . $vis_tech . "' ><h4>Technician solved the problem?</h4> <input type='radio' name='solved_tech' value='yes' ";
		if ($row['solved_tech']=='yes') {
			$list .= " checked ";
		}
		$list .= " />Yes <input type='radio' name='solved_tech' value='no' ";
		if ($row['solved_tech']=='no') {
			$list .= " checked ";
		}
		$list .= " />No <input type='radio' name='solved_tech' value='unsure' ";
		if ($row['solved_tech']=='unsure') {
			$list .= " checked ";
		}
		$list .= "/>Not Sure<br/><input type='radio' name='solved_tech' value='magic' ";
		if ($row['solved_tech']=='magic') {
			$list .= " checked ";
		}
		$list .= " />Problem Solved Itself</div>";
		print $list;


		$list = "<h4>Confirm Type of problem:</h4> <input type='radio' name='confirm_problem' value='yes' onClick='Effect.SlideDown(\"checklist\")' ";
		if ($row['confirm']=='yes') {
			$list .= " checked ";
		}
		$list .= " />User Error <input type='radio' name='confirm_problem' value='no' onClick='Effect.SlideDown(\"checklist\")' ";
		if ($row['confirm']=='no') {
			$list .= " checked ";
		}
		$list .= " />Equipment Failure <br/>";
		$list .= "<input type ='radio' name='confirm_problem' value='software' ";
		if ($row['confirm']=='software') {
			$list .= " checked ";
		}
		$list .= " />Software Issue ";
		$list .= "<input type='radio' name='confirm_problem' value='unsure' onClick='document.getElementById(\"checklist\").style.display=\"none\"'  ";
		if ($row['confirm']=='unsure') {
			$list .= " checked ";
		}
		$list .= " />Not Sure";
		print $list;



		$list = "<br /><h4>RT Ticket?</h4> <input type='radio' name='rt_ticket' value='yes' onClick='document.getElementById(\"rt_ticket_number\").style.display=\"\"' ";
		if ($row['rt_ticket']=='yes') {
			$list .= " checked ";
		}
		$list .= " />Yes <input type='radio' name='rt_ticket' value='no' onClick='document.getElementById(\"rt_ticket_number\").style.display=\"none\"' ";
		if ($row['rt_ticket']=='no') {
			$list .= " checked ";
		}
		$list .= " />No";
		print $list;

		if ($row['rt_ticket']=='yes') {
			$vis = "";
		} else {
			$vis = "none";
		}
		print "<div id='rt_ticket_number' style='display:" . $vis . "'><input type='text' name='ticket_num' value='" . $row['ticket_num'] . "' /></div>";
		
		$db = CreateConnection();	
		$sql2 = "SELECT * FROM problems WHERE call_id = '" . $_GET['id'] . "';";
		$problems = mysql_query($sql2,$db) or print "Error getting problem data. " . mysql_error();
		CloseConnection($db);
		$all_problems = " "; // this will just be a string containing all the problems which were checked off
		while ($prob = mysql_fetch_assoc($problems)) {
			$all_problems .= $prob['problem'];
		}

		if ($row['user_error'] != 'unsure') {
			$vis2 = "";
		} else {
			$vis2 = "none";
		}
		print "<div id='checklist' style='display:" . $vis2 . "'>";
			print "<span class='directions'>Check the problem items:</span><br />";
			$results = grabNewChecklist();
			$listed_problems = " "; // this will just be a string of all the problems in the checklist on the DB
			while($row = mysql_fetch_assoc($results)) {
				$listed_problems .= $row['item'];
				print "<input type='checkbox' name='cl_" . $row[id] . "' value='" . $row[item] . "' ";
				
				if (strpos($all_problems,$row['item'])) {
					print " checked ";
				}
				
				print ">" . $row['item'] . "<br />";
			}
			
			$db = CreateConnection();
			$problems2 = mysql_query($sql2,$db) or print "Error getting your data. " . mysql_error();
			CloseConnection($db);
			$other_problem = "none";
			while ($row = mysql_fetch_assoc($problems2)) {
				if (strpos($listed_problems,$row['problem'])==0) { // if the 'problem' is not part of the checklist on the DB it must be an 'Other"
					$other_problem = "yes";
					print "<input type='checkbox' name='cl_other' value='" . $row['problem'] . "' checked/ > " . $row['problem'] . "<br />";
				}
			}
		if ($other_problem == "none") {
			print "Other: <input type='text' name='cl_other' maxlength='25' size='20' />";
		}
					
		print "</div>";
	}
	print "<p><input type='button' value='UPDATE RECORD' onClick='document.boxes.submit();' /></p>";
?>

</div>


</div>
</body>
</html>

