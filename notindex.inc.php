<?php

if ($_POST['action'] == "getEvents") {

	$info = file_get_contents('http://lolaexchange.org/cgi-bin/classroomspro/edupdate.cgi?function=f302');
	echo $info;

}


?>

