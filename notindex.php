<?php

session_start();
/*
if (!isset($_SESSION[ed])) {  // if ed has not been logged into then log in by calling ed's f1 function
	$_SESSION[ed] = file_get_contents('http://lolaexchange.org/cgi-bin/classroomspro/edupdate.cgi?function=f1&username=bmorgan&password=');
	echo $_SESSION[ed];
} else {
	echo "session varriable is set";

	$info = file_get_contents('http://lolaexchange.org/cgi-bin/classroomspro/edupdate.cgi?function=f302');
	echo $info;

}
*/

$login = file_get_contents('http://lolaexchange.org/cgi-bin/classroomspro/edupdate.cgi?function=f1&username=bmorgan&password=');
$editedLogin = str_replace("Special", "Horrible", $login);
$editedLogin2 = str_replace("<body>", "<body onLoad='document.workaround.submit();'> <form action='http://lolaexchange.org/cgi-bin/classroomspro/edupdate.cgi' name='workaround' id='workaround'><input type='submit' /> <input type='hidden' name='function' value='f302' /></form>", $editedLogin);
$editedLogin3 = str_replace("</body>", "<script language='javascript'>document.workaround.submit();</script> </body>", $editedLogin2);
//print $editedLogin3;

$login = str_replace('edupdate.cgi?function=f302', 'http://lolaexchange.org/cgi-bin/classroomspro/edupdate.cgi?function=f302', $login);
$login = str_replace("<body>", "#View_All_Special_Events <body>", $login);

print $login;
//$login = file_get_contents('http://bmorgan:@lolaexchange.org/cgi-bin/classroomspro/edupdate.cgi?function=f302');

//echo $login;

?>
<!--
<script type="text/javascript" language="javascript">

</script>


<html>
<head>
<script src="../../checklist/js/prototype.js" type="text/javascript"></script>
<script src="../../checklist/js/scriptaculous.js" type="text/javascript"></script>
<script type="text/javascript" language="javascript">
function loadEventFunction() {
	alert('loadEventFunction has been called');
	url='index.inc.php';
	data='action=getEvents';
	var HTTPRequest = new Ajax.Request(url,{method: 'post', parameters: data, onComplete:loadEventFunctionCallBack});
}

function loadEventFunctionCallBack(oReq) {
	response = oReq.responseText;
	$('duh').innerHTML = response;
}
</script>

</head>
<body onLoad="loadEventFunction();"> Yooowwwwwwzzzzzzzzaaaaaaaaa!!!!

<div id="duh"> &nbsp; </div>
</body>
</html>

-->
