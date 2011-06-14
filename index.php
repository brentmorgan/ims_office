<?php
header('location:newindex.php');
session_start();
include ('../../checklist/includes/db.inc.php');
include ('../../checklist/includes/loggedin.php');
include ('index.inc.php');

if (!isset($_SESSION['client'])) {
	$_SESSION['client'] = "";
}

?>

<html>
<head>

<title>Barcode Lookup / Checkin / Checkout</title>
<link rel="stylesheet" type="text/css" href="../../checklist/css/classroom.css" />
</head>


<body onLoad="document.getElementById('barcode_field').focus();">
<?php include("../../checklist/includes/menu.inc.php"); ?>

<h2>Equipment Checkout / Checkin</h2>
<form action="index.php" method="post">

<?php 
//  echo "Action: ".$_POST['bc_action']."<br />"; // ***** PART OF TESTING

if (!$_POST) { // NO POST DATA YET - Form  NOT SUBMITTED SO STARTING FROM SCRATCH
	print "Barcode: <input type='text' name='barcode_field' id='barcode_field'  /> ".
	"<input type='hidden' name='bc_action' value='lookup' />".
	" <input type='submit' value='look it up' /> ".
	" or <a href='allbarcodes.php'>Browse All Checked Out Items</a>";

} else { // BARCODE HAS BEEN SUBMITTED

	$_POST['barcode_field'] = strtoupper($_POST['barcode_field']);

	if (is_numeric($_POST['barcode_field'])) {
		print "Invalid Barcode.  Be sure to enter all LETTERS and NUMBERS.";
		print "<p><a href='javascript:history.back(1);'>BACK</a></p>";
	} else
	if ($_POST['bc_action'] == 'lookup') {

		$current_bc = barcode_lookup($_POST['barcode_field']);
		
			if ($current_bc == "") { // BARCODE WAS NOT FOUND 
			print "<p>Barcode <b>".$_POST['barcode_field']."</b> has not been checked out. </p>".
				"<p>Event Name, Technician or Client: <input type='text' name='checkout_person' value='".$_SESSION['client']."' /> <br />Checked Out By: <input type='text' name='checkout_dispatcher' value='".$_SESSION['user']."' /> <br />Item Description: <input type='text' name='checkout_item' /></p>".
				"<input type='submit' value='Checkout Barcode' /> ".
				" or <a href='index.php'>LOOK UP ANOTHER BARCODE</a>".
				"<input type='hidden' name='barcode_field' value='".$_POST['barcode_field']."' />".
				"<input type='hidden' name='bc_action' value='checkout' />";
			} else { // BARCODE WAS FOUND
				print $current_bc;
				print "<p>To RETURN THIS ITEM confirm your username: <input type='text' name='checkin_dispatcher' value='".$_SESSION['user']."' /> and <input type='submit' value='SUBMIT' /> <input type='hidden' name='barcode_field' value='".$_POST['barcode_field']."' /> <input type='hidden' name='bc_action' value='return' />".
				"<p>Or <a href='http://imsdev.wesleyan.edu/ims/office'>LOOK UP ANOTHER BARCODE</a></p>";
			}

	} elseif ($_POST['bc_action'] == 'checkout') {
		print "<p>".$_POST['checkout_item']."  Checked Out</p>".
			"<p><a href='http://imsdev.wesleyan.edu/ims/office/index.php'>Start Over</a></p>";
				 // ENTER THE SUBMITTED CHECKOUT INFO TO DB
			$bar = strtoupper($_POST['barcode_field']);
			$db = CreateConnection();
			$sql = "INSERT INTO eqp_out (barcode,person,dispatcher_out,item) VALUES ('$bar','$_POST[checkout_person]','$_POST[checkout_dispatcher]','$_POST[checkout_item]')";
			$result = mysql_query($sql,$db);

			CloseConnection($db);

			$_SESSION['client'] = $_POST['checkout_person']; // IF CHECKING OUT A LOT OF ITEMS FOR ONE PERSON WE CAN AUTOFILL THE PERSON'S NAME USING THIS

	} elseif ($_POST['bc_action'] == 'return') {
		print "<p>".$_POST['barcode_field']." returned.</p>".
			"<p><a href='http://imsdev.wesleyan.edu/ims/office/'>Start Over</a></p>";

		$db = CreateConnection();
		$sql = "INSERT INTO eqp_in (barcode,person,dispatcher_out,time_out,item,dispatcher_in) VALUES ('$_POST[barcode_field]','$_POST[checkout_person]','$_POST[checkout_dispatcher]','$_POST[checkout_time]','$_POST[checkout_item]','$_POST[checkin_dispatcher]')";
		$result = mysql_query($sql,$db);

		$sql2 = "DELETE fROM eqp_out WHERE barcode = '$_POST[barcode_field]'";
		$result2 = mysql_query($sql2,$db);

		CloseConnection($db);

	}

}

?>

</form>

<div id="corner_logo" style="z-index:0; position:absolute; top:20px; left:20px;">
	<img id="corner_pic" src="../../checklist/images/ims-logo-phone-transparent.png" />
</div>
</body>
</html>
