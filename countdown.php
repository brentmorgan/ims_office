<?php

$comm = date("z", mktime(0, 0, 0, 5, 22, 2011));
$now = date("z");
$days_left = $comm - $now;
/*
echo "now is " . $now . ", commencement is " . $comm . ", days until C is " . ($comm - $now);
*/
echo "<h3>" . $days_left . " days until Commencement 2011</h3>";

?>
