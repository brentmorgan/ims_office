<?php
session_start();
include ('../../checklist/includes/db.inc.php');

if ($_POST['action']=='getBilling') {

	if (($_SESSION['user'] == "bmorgan") || ($_SESSION['user'] == "hchang01") || ($_SESSION['user'] == "hflores")) {
		$db = CreateConnection();
		$sql = "SELECT event_id, datecreated, event_name FROM eqp_events WHERE billed = 'no' ORDER BY datecreated DESC";
		$result = mysql_query($sql,$db);
	
		$list = "<form name='billing_form' method='post'><table style='margin:auto; width:800px; border:1px solid #000;'>";
		$list .="<input name='action' value='submitBilling' type='hidden' />";

		while ($events = mysql_fetch_assoc($result)) {
		
			$list .= "<tr><th colspan='2'>" . $events['event_name'] . " &bull; Entered " . $events['datecreated'] . "</th></tr>";

			$new_sql = "SELECT description, item_id FROM eqp_inorout WHERE event_id = '$events[event_id]' AND billed = 'no'";
			$new_result = mysql_query($new_sql,$db);
			while ($items = mysql_fetch_assoc($new_result)) {
		
				$list .= "<tr><td>" . $items['description'] . "</td><td>Billing is Completed <input type='checkbox' name='" . $events['event_id'] . "_" . $items['item_id'] . "' /></td></tr>";
			
			}
		}
			$list .= "</table>";
	
			$list .= "<div id='button_div' style='margin:10px'>";
			$list .= "<input type='button' value='SUBMIT' onClick='document.billing_form.submit();' />";
			$list .= "</div></form>";
	
		CloseConnection($db);

		print $list;
	} else {
		print $_SESSION['user'] . " - You are not authorized to submit billing information.";
	}
	
}
?>
