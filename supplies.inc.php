<?php
include("../../checklist/includes/db.inc.php");

// **********************************************************************************

function getLocations() {

	$db = CreateConnection();
	$sql = "SELECT * FROM inv_loc";

	$result = mysql_query($sql,$db) or print "Could not get list of LOCATIONS. " . mysql_error();

	$list = array();

	while ($row = mysql_fetch_assoc($result)) {
		array_push ($list,$row);
	}

	CloseConnection($db);

	return $list;
}

// **********************************************************************************

function getProducts($loc) {

	$db = CreateConnection();
	$sql = "SELECT prod_id FROM inv_loc_prod WHERE loc_id = '" . $loc . "'";

	$result = mysql_query($sql,$db) or print "Could not get the list of PRODUCTS. " . mysql_error();

	$prod_list = array();

	while ($row = mysql_fetch_assoc($result)) {
		array_push($prod_list,$row);
	}

	$big_list = array();
	foreach ($prod_list as $val) {
		$sql = "SELECT * FROM inv_prod WHERE prod_id = '" . $val['prod_id'] . "'";
		$result = mysql_query($sql,$db) or print "Could not get Product Info. " . mysql_error();
		while ($row = mysql_fetch_assoc($result)) {
			array_push($big_list,$row);
		}
	}

	CloseConnection($db);

	return $big_list;

}

// **************************************************************************************

function getAllSupplies() {

	$db = CreateConnection();
	$sql = "SELECT * FROM inv_prod";

	$result = mysql_query($sql,$db) or print "Could not get the list of all SUPPLIES. " . mysql_error();

	$list =array();

	while ($row = mysql_fetch_assoc($result)) {
		array_push($list, $row);
	}

	CloseConnection($db);
	
	return $list;

}

// **************************************************************************************

function updateRecords($info) {

	$db = CreateConnection($db);
	
	foreach($info as $key => $val) {
		$flag = "good";
		$sql = "UPDATE `inv_prod` ";
		$sql2 = "INSERT INTO `inv_out` (out_lab, out_prod_id, out_amount) VALUES ('" . $info['lab'] . "',";

		switch($key) {
			case 'lab':
				$flag = "bad";
				break; // set flag to BAD will cause the query below not to run
			case 'letter_box':
				$val = $val *10; // 10 reams per box of paper
				$sql .= " SET prod_amount = (prod_amount - " . $val . ") WHERE prod_id = 7"; // 7 is paper
				$sql2 .= " '7', '" . $val . "')";
				break;
			case 'letter_ream':
				$sql .= " SET prod_amount = (prod_amount - " . $val . ") WHERE prod_id = 7";
				$sql2 .= " '7', '" . $val . "')";
				break;
			case 'tabloid_box':
				$val = $val *10; // 10 reams per box
				$sql .= " SET prod_amount = (prod_amount - " . $val . ") WHERE prod_id = 10";
				$sql2 .= " '10', '" . $val . "')";
				break;
			case 'tabloid_ream':
				$sql .= " SET prod_amount = (prod_amount - " . $val . ") WHERE prod_id = 10";
				$sql2 .= " '10', '" . $val . "')";
				break;
			default:
				$sql .= " SET prod_amount = (prod_amount - " . $val . ") WHERE prod_id = " . $key;
				$sql2 .= " '" . $key . "', '" . $val . "')";
		}

		if ($flag == "good") {
			mysql_query($sql,$db) or print "<p>Oh no! " . mysql_error() . "</p>";
			if ($val >=1) { // only run the query for the products tha twere removed (amount is not zero)
				mysql_query($sql2,$db) or print "<p>Problem with the second query!!! " . mysql_error() . "</p>";
			}
		}
	}
	CloseConnection($db);
}












?>
