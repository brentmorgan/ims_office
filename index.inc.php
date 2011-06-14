<?php

function barcode_lookup($bc) {	

	$db = CreateConnection();
	$sql = "SELECT * FROM eqp_out WHERE barcode='".$bc."'";
	$result = mysql_query($sql,$db);

	while ($row = mysql_fetch_array($result)) {
		$output = $row['barcode']."<br />". 
			"Checked out to: ".$row['person']."<br />".
			"Checked out by: ".$row['dispatcher_out']."<br />".
			"Out Since: ".$row['time_out']."<br />".
			"Item: ".$row['item'].
			"<input type='hidden' name='checkout_person' value='".$row['person']."' />".
			"<input type='hidden' name='checkout_dispatcher' value='".$row['dispatcher_out']."' />".
			"<input type='hidden' name='checkout_time' value='".$row['time_out']."' />".
			"<input type='hidden' name='checkout_item' value='".$row['item']."' />";
	}

	return $output;
}









?>
