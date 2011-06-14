<?php
session_start();
include ("../../checklist/includes/loggedin.php");
include ("call.inc.test.php");
?>

<html>
<head>
<title>Stop Calling Me!!!</title>

<link rel="stylesheet" type="text/css" href="../../checklist/css/classroom.css" />

</head>
<body>
<?php include("../../checklist/includes/menu.inc.php"); ?>
<div id="whole_thing" style="width:100%; margin-right:auto; margin-left:auto;">
<?php include ("../../checklist/includes/calls_logo.php"); ?>
<div id="call_tables" style="width:90%; margin-left:auto; margin-right:auto; position:relative; top:-80px;">
<?php
listCalls();
echo $list;
?>
</div>

</div>

</body>
</html>
