<?php
session_start();
include ('../../checklist/includes/db.inc.php');
include ('../../checklist/includes/loggedin.php');
include ('index.inc.php');

if (!isset($_SESSION['client'])) {
	$_SESSION['client'] = "";
}

//******* FOR CHECKING IN OR OUT THE RADIOS ****** 

if (($_POST['action'] == "radios" && $_POST['which_radio'] !== "") && ($_POST['which_button'] == "in" || ($_POST['which_button'] == "out" && $_POST['name'] != ""))) {

	$db = CreateConnection();
	if ($_POST['which_button'] == "in") {
		$sql = "INSERT INTO `radios_" . $_POST['which_radio'] . "` (name) VALUES ('IN')";
	} elseif ($_POST['which_button'] == "out") {
		$sql = "INSERT INTO `radios_" . $_POST['which_radio'] . "` (name) VALUES ('" . $_POST['name'] . "')";
	}
	$result = mysql_query($sql,$db);
	CloseConnection($db);
}

?>

<html>
<head>

<title>Barcode Lookup / Checkin / Checkout</title>
<link rel="stylesheet" type="text/css" href="../../checklist/css/classroom.css" />
<style type="text/css">
	.sneaky { display:none; }
</style>
<script src="../../checklist/js/prototype.js" type="text/javascript"></script>
<script src="../../checklist/js/scriptaculous.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function addRow(row) {
	var newdiv = document.createElement("div");
	document.getElementById('div_for_divs').appendChild(newdiv);
	newdiv.id = "div_"+row;
	document.getElementById("div_"+row).innerHTML="Barcode: <input type='text' name='barcode_"+row+"' size='10' /> Description: <input type='text' name='description_"+row+"' size='50' />";
	document.getElementById("button").innerHTML = "<input type='button' onClick='addRow("+(row+1)+")' value='ADD ROW +' />";
	}
function whichDiv(me) {
	document.getElementById('list_all').style.display='none';
	document.getElementById('new_event').style.display='none';
	document.getElementById('billing').style.display='none';
	document.getElementById('search').style.display='none';
	document.getElementById(me).style.display='';

}

function validate() {

	if (!document.form_new_event.form_event_name.value) {
		alert('You need to enter an event name!');
		return;
	} 

	if (!document.form_new_event.barcode_0.value) {
		alert('You need to enter at least one barcode!');
		return;
	}
	if (!document.form_new_event.description_0.value) {
		alert('You ned to enter at least one description!');
		return;
	}
	document.form_new_event.submit();

}

function getBillingInfo() {
	url = 'billing.php';
	data = 'action=getBilling';
	var HTTPRequest = new Ajax.Request(url,{method: 'post', parameters: data, onComplete: getBillingInfoCallBack});
}
function getBillingInfoCallBack(oReq) {
	response = oReq.responseText;
	$('billing').innerHTML = response;
}

</script>

</head>


<body <?php if (isset($_GET['add_to'])) {
	print "onLoad='addRow(0)'";
	}
	?> >
<?php include("../../checklist/includes/menu.inc.php"); ?>

<h2>Equipment Checkout / Checkin</h2>

<?php 
	if (isset($_GET['add_to'])) {
		include("add.php"); // THIS IS USED TO ADD STUFF TO AN ALREADY EXISTING EVENT
	} else if ((isset($_GET['page'])) && ($_GET['page']=='radios')) {
		include("radios.php"); // SEE WHO THE RADIOS ARE CHECKED OUT TO
	} else {
		include("eqp.php"); // THIS IS THE NORMAL PAGE NOT THE "ADD STUFF TO AN EXISTING EVENT" PAGE 
	}

?>

<div id="corner_logo" style="z-index:0; position:absolute; top:20px; left:20px;">
	<img id="corner_pic" src="../../checklist/images/ims-logo-phone-transparent.png" />
</div>
</body>
</html>
