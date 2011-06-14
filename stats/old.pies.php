<?php // ob_start('ob_gzhandler')
session_start();

include("../../../checklist/includes/db.inc.php");


if ($_POST) {
	$start_date = date("Y-m-d H:i:s", mktime(0,0,0,$_POST['st_mo'],$_POST['st_da'],$_POST['st_ye']));
	$end_date = date("Y-m-d H:i:s", mktime(23,59,59,$_POST['en_mo'],$_POST['en_da'],$_POST['en_ye']));

	$_SESSION['start_date'] = $start_date;
	$_SESSION['end_date'] = $end_date;	// We're going to need to access these dates in various places, its probably easiest to just make them session variables
}

if ($_POST['calls_by']=='problems') { // ******************** PROBLEMS IN CLASSROOMS
	/* WE NEED TO ADD UP A COUPLE DIFFERENT THINGS HERE. WE NEED THE TOTAL FOR EACH LISTED TYPE OF PROBLEM - EX: PC PROBLEMS
	/	WE ALSO NEED TO FIND THE NUMBER OF PROBLEMS SUBMITTED UNDER THE 'OTHER' CATTEGORY
	/	AND WE NEED TO KNOW THE TOTAL NUMBER OF PROBLEMS LOGGED (FOR THE GIVEN TIME PERIOD) IN ORDER TO CALCULATE PERCENTAGES
	/ 	SO FIRST WE GET $LIST WHICH IS ALL THE PROBLEM TYPES LIKE PC OR PROJECTOR
	/	THEN WE LOOP OVER THAT $LIST AND ADD UP THE NUMBER OF TIMES EACH ITEM HAS BEEN A PROBLEM. WE ALSO CONTINUALLY ADD UP A GRAND TOTAL, WHICH WE THEN COMPARE
	/		AGAINST A QUERY OF THE ENTIRE GRAND TOTAL, AND THIS GIVES US THE NUMBER OF 'OTHER' SUBMISSIONS
	*/

	$pie_title = "Equipment Problems in Classrooms";

	$db = CreateConnection();
	$sql_list = "SELECT * FROM `list`"; // `list` is all the typical problems listed in the DB
	$list = mysql_query($sql_list,$db);

	$non_other_total = 0; // this will count how many "listed" items there are - we can subtract from grand total of items to find the number of "other"s
	$i = 0;

	while ($row = mysql_fetch_assoc($list)) {
		$sql = "SELECT problems.id FROM `problems`, `calls` WHERE problem = '$row[item]' AND problems.call_id = calls.id AND datecreated >= '" . $start_date . "' AND datecreated <= '" . $end_date . "' AND building_id != '22'"; // ***** 22 is the TEST BUILDING
		$result = mysql_query($sql,$db) or print "Didn't work. " . mysql_error();
		$totals[$i++] = mysql_num_rows($result);
		$non_other_total += mysql_num_rows($result); // keep adding up the grand total of everything EXCEPT the "other" cattegory
	}
	$sql = "SELECT problems.id FROM `problems`, `calls` WHERE problems.call_id = calls.id AND datecreated >= '" . $start_date . "' AND datecreated <= '" . $end_date . "' AND building_id != '22'"; // 22 is the TEST building
	$result = mysql_query($sql,$db);
	$all_problems = mysql_num_rows($result); // count of all problems, including listed items and 'other's
	$list = mysql_query($sql_list,$db);

	CloseConnection($db);

	$i = 0;
	while ($row = mysql_fetch_assoc($list)) {
		$arr[$i][name] = $row['item'];
		$arr[$i][total] = $totals[$i];
		$arr[$i][percent] = (($totals[$i++]/$all_problems)*100);
	}
	$arr[$i][name] = "Other";
	$arr[$i][total] = ($all_problems - $non_other_total);


	$arr[$i][percent] = (($arr[$i][total]/$all_problems)*100);


	$pie_totals = "";
	$pie_labels = "";

	for ($i=0; $i <= (sizeof($arr)+1); $i++) {
		if ($arr[$i][total] == 0) {
			// do nothing		unset($arr[$i]);
			;
		} else {
			$pie_totals .= $arr[$i][total] . ",";
			$pie_labels .= "'" . $arr[$i][name] . " (" . (round($arr[$i][percent],2)) . "%)',";
		}
	}
	$pie_totals = substr($pie_totals,0,-1); // remove trailing commas
	$pie_labels = substr($pie_labels,0,-1);

} else if ($_POST['calls_by']=='building') { // *********************** CALLS FROM PARTICULAR BUILDINGS *****************************************************************************************************************************************************************************************************************************************

	$pie_title = "Calls by Building";

	$db = CreateConnection();
	$sql = "SELECT id, building FROM lu_buildings WHERE active = 'Y'";
	$result = mysql_query($sql,$db) or print "Error getting the list of buildings. " . mysql_error();
	$i = 0;
	$grand_total = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$sql = "SELECT id FROM calls WHERE building_id = '$row[id]' AND datecreated >= '" . $start_date . "' AND datecreated <= '" . $end_date . "' AND building_id != '22'"; // ** 22 is TEST BUILDING
		$new_result = mysql_query($sql,$db) or print "Error getting info for that building. " . mysql_error();
		$totals[$i][total] = mysql_num_rows($new_result);
		$totals[$i++][building] = $row['building'];
		$grand_total += mysql_num_rows($new_result);
	}
	$pie_totals = "";
	$pie_labels = "";
	for ($i=0; $i <= (sizeof($totals)+1); $i++) {
		if ($totals[$i][total] > 0) {
			$totals[$i][percent] = (($totals[$i][total]/$grand_total)*100);
			$pie_totals .= $totals[$i][total] . ",";
			$pie_labels .= "'" . $totals[$i][building] . " (" . (round($totals[$i][percent],2)) . "%)',";
		}
	}
	$pie_totals = substr($pie_totals,0,-1);
	$pie_labels = substr($pie_labels,0,-1);
	
} else if ($_POST['calls_by']=='error') { // ***************************** SHOWS EQUIPMENT FAIL VS. USER ERROR **********************************************************************************************************************************************************************************************************************************

	$pie_title = "User Error vs. Equipment Failure";

	$option = array("yes","no","unsure");

	$db = CreateConnection();
	$i = 0;
	$grand_total = 0;
	foreach ($option as $val) {
		$sql = "SELECT user_error FROM calls where user_error = '$val' AND datecreated >= '" . $start_date . "' AND datecreated <= '" . $end_date . "' AND building_id != '22'"; // ** 22 is the TEST BUILDING
		$result = mysql_query($sql,$db) or print "Error getting the " . $val . "data from the calls list. " . mysql_error();
		$totals[$i][total] = mysql_num_rows($result);

		if ($val == "yes") {
			$totals[$i][name] = "User Error";
		}

		if ($val == "no") {
			$totals[$i][name] = "Equipment Failure";
		}

		if ($val == "unsure") {
			$totals[$i][name] = "Unsure";
		}
		$i++;


//		$totals[$i++][name] = $val;
		$grand_total += mysql_num_rows($result);
	}
	$pie_totlas = "";
	$pie_labels = "";
	for ($i=0; $i <= (sizeof($totals)); $i++) {
		if ($totals[$i][total] > 0) {
			$totals[$i][percent] = (($totals[$i][total]/$grand_total)*100);
			$pie_totals .= $totals[$i][total] . ",";
			$pie_labels .= "'" . $totals[$i][name] . " (" . (round($totals[$i][percent],2)) . "%)',";
		}
	}
	$pie_totals = substr($pie_totals,0,-1);
	$pie_labels = substr($pie_labels,0,-1);

} else if ($_POST['calls_by']=='all_rooms') { // ************************** SINGLE PIE SHOWING ROOMS WITH HIGHEST CALL VOLUMES, FROM ALL BUILDINGS *************************************************** 
/* ****************************************************************************************************************************** */

	$pie_title = "Rooms with Highest Call Volume, Campus-Wide";
	// get the info about that building specificallyi
	$db = CreateConnection();
	$sql = "SELECT field FROM lu_rooms WHERE 1";
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
	$sql = "SELECT room_id FROM calls WHERE datecreated >= '" . $_SESSION['start_date'] . "' AND datecreated <= '" . $_SESSION['end_date'] . "'";
	$building_problems = mysql_query($sql,$db) or print "Problem looking up the building problems. " . mysql_error();
	CloseConnection($db);
	while ($row = mysql_fetch_assoc($building_problems)) {
		foreach($room_count_array as $room => $val) {
			if ($room == $row['room_id']) {
				$room_count_array[$row['room_id']]++;
			}
		}
	}
	$pie_totals = "";
	$pie_labels = "";
	foreach ($room_count_array as $row => $val) {
		if ($val >= 9) {        /* SET A LIMIT HERE FOR THE MINIMUM # OF CALLS FOR A ROOM TO APPEAR IN THE PIE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
		$pie_totals .= $val . ",";
		$pie_labels .= "'" . $row . " (" . $val . " calls)',"; 		
		}
	}
	$pie_totals = substr($pie_totals,0,-1); // remove trailing comma
	$pie_labels = substr($pie_labels,0,-1);

}

// ********************************************************************************************************************************************************************************************** 
// **********************************************************************************************************************************************************************************************
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
    <title>Call Tracking Pie Chart</title>
    <link rel="stylesheet" href="website.css" type="text/css" media="screen" />
  <!--  <link rel="icon" type="image/png" href="/favicon.png"> -->
	<link rel="stylesheet" href="stats.css" type="text/css" media="screen" />

	<script src="extraFunctions.js"></script>    
    <script src="RGraph.common.js" ></script>
    <script src="RGraph.pie.js" ></script>
	<script src="../../../checklist/js/prototype.js" type="text/javascript"></script>
	<script src="../../../checklist/js/scriptaculous.js" type="text/javascript"></script>

    <!--[if IE]><script src="../excanvas/excanvas.compressed.js"></script><![endif]-->

    <script>
	
	/* CLEARS THE AREA OF THE OLD PIE SO THE NEW PIE CAN BE DRAWN WITHOUT SEEING THE OLD TEXT STILL AROUND IT */
	function clearPie(whichPie) {
		var pie = document.getElementById(whichPie);
		var context = pie.getContext('2d');
		context.fillStyle = "rgb(255,255,255)";
		context.fillRect(0,0,5000,5000); // JUST DRAWS A BIG WHITE RECTANGE OVER THE OLD PIE
	}

        window.onload = function ()
      {
            /**
            * These are not angles - these are values. The appropriate angles are calculated
            */
            var pie1 = new RGraph.Pie('pie1', [<?php print $pie_totals ?>]); // [41,37,16,3,3]); // Create the pie object
            pie1.Set('chart.labels', [<?php print $pie_labels ?>]); // ['PC (41%)', 'Mac (37%)', 'DVD (16%)', 'Microphones or audio system (3%)', 'LCD projector (3%)']);
            pie1.Set('chart.gutter', 100);  // 30
            pie1.Set('chart.title', "<?php print $pie_title; ?>");
            pie1.Set('chart.shadow', false);

            if (!document.all) {
                pie1.Set('chart.tooltip.effect', 'expand');
                pie1.Set('chart.tooltips', [ <?php print $pie_labels; ?>
                                           ]
                                          );
                pie1.Set('chart.highlight.style', '3d'); // Defaults to 3d anyway; can be 2d or 3d
                pie1.Set('chart.zoom.hdir', 'center');
                pie1.Set('chart.zoom.vdir', 'center');
                
                pie1.Set('chart.labels.sticks', true);
                pie1.Set('chart.contextmenu', [['Zoom in', RGraph.Zoom]]);
            }

            pie1.Draw();
          }

	function drawSecondPie(response) {

		clearPie('pie2'); // CLEAR AWAY THE OLD PIE IF THERE IS ONE, SO WE DON'T SEE THE TEXT AROUND THE NEW PIE

		//alert(response);
		var vars = response.split("&");
		var room_list = []; // will show next to each slice of pie
		var tooltip_list = []; // will pop up when you click on the slice
		var freq_list = [];
		var percent_list = [];
		for (var i=0; i<vars.length; i++) {
			var pair = vars[i].split("=");
			room_list.push(pair[0]);
			tooltip_list.push(pair[0]);
			freq_list.push(pair[1]);
		}
		var tot = 0;
		for (var i=freq_list.length-1; i>=0; --i) {
			tot = tot + parseInt(freq_list[i]);
		}
//		alert(tot);
		for (var i=freq_list.length-1; i>=0; --i) {
			percent_list[i] = ((parseInt(freq_list[i])/tot)*100);
			room_list[i] += " (" + Math.round(percent_list[i]) + "%)";
			tooltip_list[i] += " (" + freq_list[i] + " calls)";
		}
//		alert(freq_list);
//		room_list = room_list.substr(0,room_list.length-1); // remove trailing comma
//		freq_list = freq_list.substr(0,freq_list.length-1); // trailing comma
		
//	alert (freq_list + " ... " + room_list);
		var pie2 = new RGraph.Pie('pie2', percent_list);
		pie2.Set('chart.labels', room_list);
		pie2.Set('chart.gutter',100); //30
		pie2.Set('chart.title', "Calls by Room");
		pie2.Set('chart.shadow', false);
//alert('yo!');

            if (!document.all) {
                pie2.Set('chart.tooltip.effect', 'expand');
                pie2.Set('chart.tooltips', tooltip_list );
                pie2.Set('chart.highlight.style', '3d'); // Defaults to 3d anyway; can be 2d or 3d
                pie2.Set('chart.zoom.hdir', 'center');
                pie2.Set('chart.zoom.vdir', 'center');
                
                pie2.Set('chart.labels.sticks', true);
                pie2.Set('chart.contextmenu', [['Zoom in', RGraph.Zoom]]);
}

		pie2.Draw();

		//var pie2 = new RGraph.Pie('pie2',
		
	}

                /**
                * Make the first pie chart fade in
                */
         //       pie1.opacity = 1;
                
           //     fadeFunc = function ()
          //      {
          //          pie1.Draw();

            //        var context  = pie1.context;
            //        var gutter   = pie1.gutter;

              //      if (pie1.opacity > 0.1) {
              //          context.beginPath();
             //           context.fillStyle = 'rgba(255,255,255,' + pie1.opacity + ')';
             //           context.fillRect(0, pie1.Get('chart.gutter'),400,300);
             //           pie1.opacity -= 0.1;
                    
             //           setTimeout(fadeFunc, 50);
           //         }
          //      }
                
          //      fadeFunc();


function dddddddddddddddddddddrawSecondPie(response) {


		var vars = response.split("&");
		var room_list = [];
		var freq_list = [];
		for (var i=0; i<vars.length; i++) {
			var pair = vars[i].split("=");
			room_list.push(pair[0]);
			freq_list.push(pair[1]);
		}
	//	room_list = room_list.substr(0,room_list.length-1); // remove trailing comma
	//	freq_list = freq_list.substr(0,freq_list.length-1); // trailing comma
		





            var pie2 = new RGraph.Pie('pie2', freq_list); // Create the pie object
	
            pie2.Set('chart.gutter', 45);
            pie2.Set('chart.title', "Some data (context, annotatable)");
            pie2.Set('chart.linewidth', 1);
            pie2.Set('chart.strokestyle', '#333');
            pie2.Set('chart.shadow', true);
            pie2.Set('chart.shadow.blur', 3);
            pie2.Set('chart.shadow.offsetx', 3);
            pie2.Set('chart.shadow.offsety', 3);
            pie2.Set('chart.shadow.color', 'rgba(0,0,0,0.5)');
            pie2.Set('chart.colors', ['red', 'pink', '#6f6', 'blue', 'yellow']);
            pie2.Set('chart.contextmenu', [['Clear', function () {RGraph.Clear(pie2.canvas); pie2.Draw();}]]);
            pie2.Set('chart.labels', room_list);
            pie2.Set('chart.key.background', 'white');
            pie2.Set('chart.key.shadow', true);

            if (!document.all) {
//                pie2.Set('chart.annotatable', true);
		;  
          }

          //  pie2.Set('chart.align', 'left');
            pie2.Draw();
           }



/* 
            var pie3 = new RGraph.Pie('pie3', [46,37,16,3,3]);
            pie3.Set('chart.labels', ['MSIE 7', 'MSIE 6', 'Firefox', 'Safari', 'Other']);
            pie3.Set('chart.title', "Browser market share: July '08 (with tooltips)");
            pie3.Set('chart.colors', ['red', 'rgb(0,255,0)', 'blue', 'yellow', 'pink']);

            if (!document.all) {
                pie3.Set('chart.tooltips', ['Internet Explorer 7','Internet Explorer 6','Firefox','Safari','Other']);
            }

            pie3.Draw();
            
            var pie4 = new RGraph.Pie('pie4', [5,6,7,9,4,3,2,5]);
            pie4.Set('chart.labels', ['Fred', 'Barney', 'Cartman', 'Kevin', 'Cynthia', 'Manji', 'Rhubarb', 'Custard']);
            pie4.Set('chart.shadow', true);

            if (!document.all) {
                pie4.Set('chart.zoom.mode', 'thumbnail');
            }

            pie4.Draw();
            
            var pie5 = new RGraph.Pie('pie5', [5,6,7,9,4,3,2,5]);
            pie5.Set('chart.labels', ['Fred', 'Barney', 'Cartman', 'Kevin', 'Cynthia', 'Manji', 'Rhubarb', 'Custard']);

            if (!document.all) {
                pie5.Set('chart.tooltips', ['Fred', 'Barney', 'Cartman', 'Kevin', 'Cynthia', 'Manji', 'Rhubarb', 'Custard']);
            }

            pie5.Set('chart.title', 'A pie chart with tooltips');
            pie5.Set('chart.linewidth', 3);
            pie5.Set('chart.strokestyle', 'white');
            pie5.Set('chart.gutter', 45);
            pie5.Set('chart.highlight.style', '2d');
            pie5.Set('chart.border', true);
            pie5.Set('chart.border.color', 'rgba(255,255,255,0.5)');
            pie5.Draw();

            var pie6 = new RGraph.Pie('pie6', [15,31,21,23,32]);
            pie6.Set('chart.key', ['Bob', 'Gerry', 'Rick', 'Charles', 'Bob']);
            pie6.Set('chart.key.position', 'graph');
            pie6.Set('chart.key.shadow', true);
            pie6.Set('chart.gutter', 20);
            pie6.Set('chart.align', 'left');
            pie6.Set('chart.strokestyle', 'rgba(0,0,0,0)');
            pie6.Set('chart.title', 'A left aligned pie chart');

            if (!document.all) {
                pie6.Set('chart.tooltips', ['Bob', 'Gerry', 'Rick', 'Charles', 'Bob']);
            }

            pie6.Draw();
        }   */
    </script>
</head>
<body>
<div id="controls">
<form method="post">
<p>
	Date Range:
	<select name="st_mo">
		<?php 
			$months = array("zero","January","February","March","April","May","June","July","August","September","October","November","December");
			$i = 1; // a counter....
			foreach ($months as $row) {
				print "<option value='" . $i . "'";
				if ($_POST['st_mo']==$i || (!$_POST && $i==1)) {
					print " selected";
				}
				print ">" . $months[$i++] . "</option>";
			}
		?>
	</select>
	<select name="st_da">
		<?php 
			for ($i=1; $i <= 31; $i++) {
				print "<option value='" . $i . "'";
				if ($_POST['st_da']==$i || (!$_POST && $i==1)) {
					print " selected";
				}
				print ">" . $i . "</option>";
			}
		?>
	</select>
	<select name="st_ye">
		<?php
			for ($i=2009; $i <= date("Y"); $i++) {
				print "<option value='" . $i . "'";
				if ($_POST['st_ye']==$i || (!$_POST && $i=='2010')) {
					print " selected";
				}
				print ">" . $i . "</option>";
			}
		?>
	</select>
	TO
	<select name="en_mo">
		<?php
			$months = array("zero","January","February","March","April","May","June","July","August","September","October","November","December");
			$i = 1;
			foreach ($months as $row) {
				print "<option value='". $i . "'";
				if ($_POST['en_mo']==$i || (!$_POST && $i == date("n"))) {
					print " selected";
				}
				print ">" . $months[$i++] . "</option>";
			}
		?>
	</select>
	<select name="en_da">
		<?php 
			for ($i=1; $i <= 31; $i++) {
				print "<option value='" . $i . "'";
				if ($_POST['en_da'] == $i || (!$_POST && $i == date("j"))) {
					print " selected";
				}
				print ">" . $i . "</option>";
			}
		?>
	</select>
	<select name="en_ye">
		<?php
			for ($i=2009; $i <= date("Y"); $i++) {
				print "<option value='" . $i . "'";
				if ($_POST['en_ye'] == $i || (!$_POST && $i == date("Y"))) {
					print " selected";
				}
				print ">" . $i . "</option>";
			}
		?>
	</select>
</p>
<p>
	Calls By
	<select name="calls_by" id="calls_by" onChange="form.submit()">

		<option value="building" <?php if ($_POST['calls_by']=='building') { print "selected"; } ?>>Building</option>
		<option value="error" <?php if ($_POST['calls_by']=='error') { print "selected"; } ?>>User Error</option>
		<option value="problems" <?php if ($_POST['calls_by']=='problems') { print "selected"; } ?> >Equipment Problem</option>
		<option value="all_rooms" <?php if ($_POST['calls_by']=='all_rooms') { print "selected"; } ?> >All Rooms</option>

	</select> <a href="lines.php">Go to LINES</a>
</p>
<p>
	<input type="submit"> --> <!-- <input type="button" value="Submit" onClick="drawFirstPie()" /> -->

</p>
</form>

</div>

<!--
<div id="breadcrumb">
    <a href="../index.html">RGraph: HTML5 canvas graph library</a>
    >
    <a href="./index.html">Examples</a>
    >
    Pie chart
</div>

            more difficult. If this is the case, you may want to consider a bar chart for example.
        </p>
        
        <p>
            The colours can of course be customised, as can the borders (using the same color as the background and a line width of
            about 5 gives the effect of segment separation. The chart can also have a drop shadow if you want one, but this shouldn't
            be used in conjunction with segment seperation.
        </p>
        
        <p>
            The first pie chart has a fade-in effect. This not part of the RGraph library but is very easy to implement.
        </p>
    </div>

    <div>
        <ul>
            <li><a href="../docs/pie.html">Pie chart API documentation</a></li>
        </ul>
    </div>
    -->
    <div style="text-align: center">
        <canvas id="pie1" width="800" height="600">[No canvas support]</canvas> <!-- original width:440 height:300 -->
        <canvas id="pie2" width="800" height="600">[No canvas support]</canvas> <!--
        <canvas id="pie3" width="450" height="300">[No canvas support]</canvas>
        <canvas id="pie4" width="450" height="300">[No canvas support]</canvas>
        <canvas id="pie5" width="400" height="300">[No canvas support]</canvas>
        <canvas id="pie6" width="370" height="280" style="border: 1px dotted gray">[No canvas support]</canvas>
-->   </div>
	<!--
	<div id ="second_pie" style="text-align:center">&nbsp;</div> --> <!-- this will be filled with additional info once they click on a pie slice -->

</body>
</html>
