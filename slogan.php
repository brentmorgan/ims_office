<?php

$slogan_page = file_get_contents("http://thesurrealist.co.uk/slogan.cgi?word=4959");
//echo $slogan_page;
$start_position = strpos($slogan_page, "h1a")+5;
//echo "start position: ".$start_position;
$slogan_page = substr($slogan_page, $start_position);
$end_position = strpos($slogan_page, "</a>");
//echo "<br />end position: ".$end_position;
$slogan = substr($slogan_page, "0", $end_position);
echo $slogan;

?>
