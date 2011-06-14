

<?php

	$db = CreateConnection();
	$sql = "SELECT event_name FROM eqp_events WHERE event_id = '$_GET[add_to]'";
	$result = mysql_query($sql,$db);
	while ($row = mysql_fetch_assoc($result)) {
		$event_name = $row['event_name'];
	}
	$sql2 = "SELECT barcode, description FROM eqp_inorout WHERE event_id = '$_GET[add_to]'";
	$result2 = mysql_query($sql2,$db);
	$list = "";
	while ($row = mysql_fetch_assoc($result2)) {
		$list .= "<tr><td>" . $row['barcode'] . "</td><td>" . $row['description'] . "</td></tr>";
	}

?>

<div id="link" style="padding-bottom:20px">
	<a href="newindex.php">LIST ALL</a>
</div>

<div id="add_items" style="margin:auto; width:880px;">
	<form name="form_add_items" method="post" action="newindex.php">
		<input type="hidden" name="action" value="add items" />
		<input type="hidden" name="event_id" value="<?php print $_GET['add_to']; ?>" />
		Event Name: <b><?php print $event_name; ?></b>
		<table style="border:1px solid #000; padding:5px; width:100%">
			<tr>
				<th>Barcode</th>
				<th>Description</th>
			</tr>
			<?php print $list; ?>
		</table>
			
		<div id="button" style="margin:15px">
			<input type="button" onClick="addRow(1)" value="+ ADD ROW +" />
		</div>
		<div id="div_for_divs"> </div>
		<input type="button" onClick="document.form_add_items.submit()" value="ADD THESE ITEMS" />

	</form>
</div>
