<?php

if ($_POST['action'] == "search") {

	$db = CreateConnection();
	$sql = "SELECT event_id FROM `eqp_inorout` WHERE barcode = '$_POST[search_field]' AND inorout = 'out'";
	$result = mysql_query($sql,$db) or print "You have been bad. " . $sql . mysql_error();
	$show = "";
	while ($row = mysql_fetch_assoc($result)) {
	
		$show .= $row['event_id'] . " ";

	}
}

function enterEquipmentItems($num) {
//print "<h1>". $num . "</h1>";
	$db = CreateConnection();
	$row = "barcode_" . $num;
	if (isset($_POST[$row])) {                  // $row])) {
//var_dump($_POST);
//print "Printing $ROW: " . $_POST[$row] . "<br/>";
		$bc = "barcode_" . $num;
		$desc = "description_" . $num;	
		$first_sql = "SELECT barcode, event_id FROM `eqp_inorout` WHERE barcode = '$_POST[$bc]' AND inorout='out'";
		$first_result = mysql_query($first_sql,$db) or print "It has gone bad. " . $first_sql . mysql_error();
		if (mysql_num_rows($first_result) > 0) {
			print "<h3>WARNING: Barcode " . $_POST[$bc] . " is already out, so is now added to multiple event listings.</h2>";
		//	exit();
		}
		$sql = "INSERT INTO `eqp_inorout` (event_id, inorout, billed, barcode, description) VALUES ('$_SESSION[eventID]','out','no','$_POST[$bc]','$_POST[$desc]')";
		$result = mysql_query($sql,$db) or print "your sql is BAAAAD: " . $sql;
		$num++;
		enterEquipmentItems($num);

	}
//	CloseConnection($db);
}

if ($_POST['action'] == "new event") {

	$db = CreateConnection();
	$sql = "INSERT INTO `eqp_events` (inorout, event_name, billed) VALUES ('out', '$_POST[form_event_name]','no')";
	$result = mysql_query($sql,$db);
	$_SESSION['eventID'] = mysql_insert_id();
	CloseConnection($db);
	enterEquipmentItems(0);

}

if ($_POST['action'] == "add items") {
	
	$_SESSION['eventID'] = $_POST['event_id'];
	$_POST['row'] = 0;
	enterEquipmentItems(0);


}

if ($_POST['action'] == "return items") {

	$db = CreateConnection();
	$i = 0;
	foreach($_POST as $key => $val) {
		if ($key != "action") {
			$item = explode("_", $key);
			$sql_where = " SET inorout='in' WHERE event_id = '" . $item[0] . "' AND item_id = '" . $item[1] . "'";
			$sql = "UPDATE `eqp_inorout` " . $sql_where;
			$result = mysql_query($sql,$db) or print "Problem, dude. " . $sql . mysql_error();
			$listed_events[$i++] = $item[0]; // make a list of all the events we are removing items from, so we can check if ALL the items have been returned
		}
	}
	// now checking if the entire event has been returned
	foreach ($listed_events as $row) {

		$sql = "SELECT * from `eqp_inorout` WHERE event_id = '" . $row . "'";
		$result = mysql_query($sql,$db);
		$not_finished = 0;
		while ($ozzy = mysql_fetch_assoc($result)) {
			if ($ozzy['inorout'] == 'out') {
				$not_finished = 1;
			}
		}
		if (!$not_finished) { // event is completely checked back in, so take the event itself off the OUT list
			$sekwil = "UPDATE `eqp_events` SET inorout = 'in' WHERE event_id = '" . $row . "'";
			$finish = mysql_query($sekwil,$db);
		}
	}
	CloseConnection($db);
}

if ($_POST['action']=='submitBilling') { // *********************************************************** BILLING PAGE HAS BEEN SUBMITTED ************* 

	$list = ""; // WILL BE A LIST OF ALL ITEM IDS TO REMOVE
	$i = 0; // a counter
	
	foreach ($_POST as $key => $val) {

		if ($val == "on") { // if it is "on" then it is a checkbox which was checked. so these are the ones thatshould be removed.
			$numbers = explode("_",$key);
			$list .= " item_id = '" . $numbers[1] . "' OR ";
			$event_ids[$i++] = $numbers[0];
		}
	}
	$list = substr($list,0,-3); // REMOVE THE TRAILING SPACE AND 'OR'

	$sql = "UPDATE `eqp_inorout` SET billed = 'yes' WHERE " . $list . " ";
	
	$db = CreateConnection();
	$result = mysql_query($sql,$db) or print "Error with submitting the biliing info. " . $sql . mysql_error();
	
	// checking if ALL the items for each event have been returned, and if so list the event itself as billed
	foreach ($event_ids as $row) {

		$sql = "SELECT * FROM `eqp_inorout` WHERE event_id = '" . $row . "'";
		$result = mysql_query($sql,$db) or print "Error with select. " . $sql . mysql_error();
		$not_finished = 0;
		while ($wow = mysql_fetch_assoc($result)) {
			if ($wow['billed'] == 'no') {
				$not_finished = 1;
			}
		}
		if (!$not_finished) { // if event is all billed we list the event itself as billed
			$sekwil = "UPDATE `eqp_events` SET billed = 'yes' WHERE event_id = '" . $row . "'";
			$finish = mysql_query($sekwil,$db) or print "Error updating billing for Events list. " . $sekwil . mysql_error();
		}
	}

	CloseConnection($db);
	
}
?>

<div id="links" style="padding-bottom: 20px">
	<a href="newindex.php">LIST ALL</a> &bull; <a href="#"  onClick='whichDiv("new_event"); addRow(0);'>NEW EVENT CHECKOUT</a> &bull; <a href="#" onClick='getBillingInfo();whichDiv("billing");'>BILLING</a> &bull; <a href="#" onClick='whichDiv("search")'>SEARCH</a>
</div>
<div id="list_all" style="margin:auto; width:800px;">
	<table style="border:1px solid #000; padding:5px; width:100%;">
		<tr>
			<th>Event Name</th>
			<th>Date Out</th>
		</tr>
		<?php
			$db = CreateConnection();
			$sql = "SELECT *, DATE_FORMAT(datecreated,'%W %b %e, %l %p') as date FROM eqp_events WHERE inorout = 'out'";
			$result = mysql_query($sql,$db) or print "Error getting the list of events. " . mysql_error();
			$i = 0;
			print "<form name='returns' method='post'><input type='hidden' name='action' value='return items' />";
			while ($row = mysql_fetch_assoc($result)) {
				$new_sql = "SELECT * FROM `eqp_inorout` WHERE event_id = '$row[event_id]' AND inorout = 'out'";
				$new_result = mysql_query($new_sql,$db) or print "Error getting the items from that event. " . mysql_error();
				print "<tr><td><a href='#' onClick='document.getElementById(\"sneaky_" . $row['event_id'] . "\").style.display=\"\"'>" . $row['event_name'] . "</a></td><td>" . $row['date'] . "</td></tr>";
				print "<tr id='sneaky_" . $row['event_id'] . "' style='display:";
	/*			if ($row['event_id'] != $show) {
					print "none"; // this is for when they SEARCH for something. it will have the searched-for thing un-hiddden by default
				}    */
				$id = '/' . $row['event_id'] . '/';
				if (!preg_match($id, $show)) {
					print "none";
				}
				print "'><td colspan='2'>";
					print "<table style='width:100%; border:1px solid #000;'>";
					while ($item = mysql_fetch_assoc($new_result)) {
						print "<tr><td><input type='checkbox' name='" . $item['event_id'] . "_" . $item['item_id'] . "' /> Return this Item</td>";
						print "<td>Barcode: <b>" .$item['barcode'] . "</b></td> ";
						print "<td>Description: <b>" . $item['description'] . "</b></td></tr>";
					}
				print "<tr><td colspan='3'><span style='margin-left:310px'><a href='newindex.php?add_to=" . $row['event_id'] . "'>ADD MORE ITEMS</a></span></td></tr>";
				print "</table>";
			}
			CloseConnection($db);
		?>
	</table>
	<input type="button" style="margin-top:20px;" value="Return Selected Items" onClick="document.returns.submit()" />
	</form>
</div>

<div id="new_event" style="display:none">
	<form name="form_new_event" method="post">
		<input type="hidden" name="action" value="new event" />
		Event Name: <input type="text" name="form_event_name" /> 
		<div id="button">
			<input type="button" onClick="addRow(1)" value="+ ADD ROW +" />
		</div>
		<div id="div_for_divs"> </div>
		<input type="button" onClick="validate()" value="CHECK OUT" />
	</form>
</div>


<div id="billing" style="display:none; width:800px; margin:auto;">

</div>

<div id="search" style="display:none">
	<form name="search_form" method="post">
	<input type="hidden" name="action" value="search" />
	<p>
		Barcode: <input type="text" size="8" name="search_field" />
	</p>
	<input type="button" value="SEARCH" onClick="document.search_form.submit()" />
	</form>

</div>
