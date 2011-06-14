<?php
/* 	Tables we probably want to download are:
	calls, othercalls, problems
*/

include_once('../../../checklist/includes/db.inc.php');

//$tables = array("calls","othercalls","problems");

function _mysqldump_csv($table) {
	$db = CreateConnection();
	$sql = "SELECT * FROM `$table`;";
	$result = mysql_query($sql,$db) or print "Error with DB Dump. " . mysql_error();
	CloseConnection($db);

	$delimiter = ",";

	if ($result) {
		$num_rows = mysql_num_rows($result);
		$num_fields = mysql_num_fields($result);

		$i = 0;
		while ($i < $num_fields) {
			$meta = mysql_fetch_field($result, $i);
			echo($meta->name);
			if ($i < $num_fields-1) {
				echo "$delimiter";
			}
			$i++;
		}
		echo "\r\n";

		if ($num_rows > 0) {
			while ($row = mysql_fetch_row($result)) {
				for ($i=0; $i < $num_fields; $i++) {
					echo mysql_real_escape_string($row[$i]);
					if ($i < $num_fields -1) {
						echo "$delimiter";
					}
				}
				echo "\n";
			}
		}
	}
	mysql_free_result($result);
}

function downloadAsCsv($table) {
	$today = date("Ymd");

	ob_start("ob_gzhandler");

	header('Content-type: text/comma-separated-values');
	header('Content-Disposition: attachment; filename="Call_Tracking_' . $table . '_' . $today . '.csv";');

	_mysqldump_csv($table);

	header("Content-Length: " . ob_get_length());

	ob_end_flush();
}

// this is where stuff gets done
if ($_GET) {
	downloadAsCsv($_GET['tab']);
}

?>


<div id="dbDump">
	DB Dump: <a href="db_dump.php?tab=calls">CALLS TABLE</a> || <a href="db_dump.php?tab=othercalls">OTHER CALLS TABLE</a> || <a href="db_dump.php?tab=problems">PROBLEMS TABLE</a>
</div>
