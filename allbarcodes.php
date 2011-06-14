<?php
session_start();
include ('../../checklist/includes/db.inc.php');
include ('../../checklist/includes/loggedin.php');
?>
<html>
<head><title>All Items Currently Checked Out</title>
<script type="text/javascript" language="javascript">

function goToRecord(bc){
//	alert(bc);
	document.iAmAForm.elements["barcode_field"].value = bc;
	document.iAmAForm.elements["bc_action"].value = 'lookup';
	document.iAmAForm.submit();
}	

</script>
</head>

<body>
<form action="index.php" method="post" name="iAmAForm">
<input type="hidden" name="barcode_field" value="" />
<input type="hidden" name="bc_action" value="" />
</form>

<?php

$db = CreateConnection();
$sql = "SELECT * FROM eqp_out WHERE 1";
$result = mysql_query($sql,$db);

if (mysql_num_rows($result)==0) {
	echo "<h3>No Items Currently Checked Out</h3>";
} else {
	echo "<h3>All Items Currently Checked Out:</h3>";

	while ($row = mysql_fetch_assoc($result)) {
		print "<a href='javascript:goToRecord(\"".$row['barcode']."\");'>".$row['barcode'] . "</a>  " . $row['item']."<br />";
	}
}

?>


</body></html>
