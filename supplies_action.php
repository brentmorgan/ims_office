<?php
include('supplies.inc.php');
?>

<html>
<head>
	<title>
		Submitted!
	</title>
	
	<script type="text/JavaScript">

		timer = setTimeout("location.href = 'supplies.php';",3000);

		function waitHere() {
			clearTimeout(timer);
			document.getElementById('link_p').innerHTML = "<a href='supplies.php'>Check-out Page</a>";
		}

	</script>

</head>

<body>
<p id="link_p">
<a href="javascript:waitHere();">Wait Here</a>
</p>
<?php 
	// ************************************************ Subtract whatever was taken from inventory, and keep a record of it
	$warning = updateRecords($_POST);
	print $warning; // if it worked correctly it won't print anything.
			// if there was an error with the query it will print that.

	// ************************************************ Display the current supply levels

	$all_supplies = getAllSupplies();

	print "<br />";

	foreach ($all_supplies as $key => $val) {
		print $key . ": " . $val['prod_num'] . " (" . $val['prod_printer'] . " " . $val['prod_alt_printer'] . ") ";
		if ($val['prod_amount'] <= $val['prod_alert_level']) {
			print "<span style='color:red'>";
		}
		print " Quantity: " . $val['prod_amount'];
		if ($val['prod_amount'] <= $val['prod_alert_level']) {
			print "</span>";
		}
		print " <br/>";
	}
?>
</body>

</html>
