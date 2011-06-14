<script language="javascript">

	function DELETEMEupdateRadio(whichOne) {

		document.getElementById(whichOne).innerHTML = "<input name='name_"+whichOne+"' type='text' /><input type='button' onClick='alert(\"hey!\")' value='GO' />";

	}

</script>

<div id="content">

<h2>Radios</h2>
<table style="border:1px solid #000; padding:10px; width:800px; margin:auto;">
	<tr>
		<th>Tech</th>
		<th>Golf Cart</th>
		<th>Prodigal</th>
		<th>Radio</th>
		<th>HAS</th>
		<th>Office Manager</th>
	</tr>
	<tr>

	<?php

		$radio_list = array("tech","golf","prod","radio","has","om");

		$db = CreateConnection();
		$sql1 = "SELECT * FROM radios_"; // "radioName "
		$sql3 = " ORDER BY id DESC LIMIT 1";

		foreach ($radio_list as $row) { // we need to do a query for each radio table
			$sql = $sql1 . $row . $sql3;

			$result = mysql_query($sql,$db);

			while ($row = mysql_fetch_assoc($result)) {
				if ($row['name'] == "IN") {
					$color = "000";
				} else {
					$color = "f00";
				}
				print "<td style='width:20%; padding:5px; text-align:center; color:#" . $color . "'>" . $row['name'] . "</td>";	
			}
		}
	?>

	</tr>
	<tr>

	<?php

		$radio_list = array("tech","golf","prod","radio","has","om");

		$db = CreateConnection();
		$sql1 = "SELECT id, DATE_FORMAT(datecreated,'%l:%i %p - %W %b %e') as date FROM radios_";
		$sql3 = " ORDER BY id DESC LIMIT 1";

		foreach ($radio_list as $row) {
			$sql = $sql1 . $row. $sql3;

			$result = mysql_query($sql,$db) or print "OH NO! " . mysql_error();

			while ($row = mysql_fetch_assoc($result)) {
				print "<td style='width:20%; padding:5px; text-align:center;'>" . $row['date'] . "</td>";
			}
		}

	?>

	</tr>
</table>
<div style="padding-top:20px">
	<form name="radio_checkout_form" method="post">
		<input type="hidden" id="which_button" name="which_button" value="neither" /> <!-- the value of this will change when a button is clicked -->
		<input type="hidden" name="action" value="radios" />
		<select name="which_radio">
			<option value="">Choose a Radio</option>
			<option value="tech">Tech</option>
			<option value="golf">Golf Cart</option>
			<option value="prod">Prodigal</option>
			<option value="radio">Radio</option>
			<option value="has">HAS</option>
			<option value="om">Office Manager</option>
		</select>
		<div style="padding-top:20px">
			<input type="button" value="Check IN" onClick="document.getElementById('which_button').value='in';radio_checkout_form.submit();" />
		</div>
		<p>OR</p>
		<p>Check out to: <input type="text" name="name" /></p>
		<input type="button" value="Check OUT" onClick="document.getElementById('which_button').value='out';radio_checkout_form.submit();" />
	</form>
</div>
</div>





