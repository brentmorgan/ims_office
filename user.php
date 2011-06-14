<?php
/*
include("../../phpIncludes/connect.php");

$fart = openDBConnection();
echo "openDBConnection: ";
foreach ($fart as $key => $value) {
	echo $key." : ".$value;
}
echo "<br />";
*/
//$sql = "SELECT * FROM rooms WHERE 1";
//$result = mysql_query($sql,$fart);
//print_r ($result);

$dbConnection = OCILogon("aclc", "diggler", "CURL");
if (!$dbConnection) {
	print ("aaaaaaak!");
	die();
}
//echo $dbConnection;
$result = mysql_query($sql,$dbConnection);
print_r ($result);



function VerifyWesUser($username, $password) {

$adcontroller['0'] = "ldaps://landroval.wesad.wesleyan.edu/";
$adcontroller['1'] = "ldaps://landroval.wesad.wesleyan.edu/";

$adusername = $username . "@wesad.wesleyan.edu";
$ds = ldap_connect($adcontroller[rand(0,1)]);

if (!ds) {
return 0;
}

$ldapbind = @ldap_bind($ds,$adusername,$password);

if ($ldapbind) {
ldap_unbind($ds);
return 1;
} else {
ldap_unbind($ds);
return 0;
}
return 0;
}


$trala = VerifyWesUser('bmorgan', '');

echo $trala; //1 if verified, 0 if not!

?>
