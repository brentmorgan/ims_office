<?php
//include('../../checklist/checklist.inc.php');
include('supplies.inc.php');
?>

<html>
	<head>
		<title>IMS Labs Supplies</title>
		
		<script src="../../checklist/js/prototype.js" type="text/javascript"></script>
		<script src="../../checklist/js/scriptaculous.js" type="text/javascript"></script>

		<link rel="stylesheet" type="text/css" href="supplies.css"/>

	</head>

	<body>
		<div id="content">
			<div id="headline">Lab Supplies Check-Out</div>
			<h3>choose your destination:</h3>

			<div id="destinations">
				<?php
					$locations = getLocations();
					foreach ($locations as $row) {
						print "<a class='loc_btn' href='?loc=".$row['loc_id']."'";
						if ($_GET['loc'] == $row['loc_id'])
						{
							print " id='lit_up' ";
						}
						print ">".$row['loc_loc']."</a>";
					}
				?>
			</div>
			<form name="supplies_form" method="post"  action="supplies_action.php">
			<input type="hidden" name="lab" value="<?php print $_GET['loc']; ?>" />
			<div id="options">
				<?php
					if ($_GET) 
					{ // ********************  if one of the LOCATIONS links was clicked
						print "<h3>the things you take with you:</h3>";
						
						print "<div class='paper-toner'>TONER</div>"; // ********************** TONER
						$products = getProducts($_GET['loc']);
					
						foreach ($products as $row) 
						{
							print "<div class='product_listing' style='background-color:" . $row['prod_color']; // using inline style here, cause if depends on the php variable. not sure how to include that in the style sheet??????
							if ($row['prod_color'] == "black") {
								print "; color:white; border-top: 1px solid white; ";
							}
							print "'>" . $row['prod_num'] . ": ";
							print "<select name='" .$row['prod_id'] . "'>";
							for ($i=0; $i<=5; $i++) 
							{
								print "<option value='" . $i . "'>" . $i . "</option>";
							}
							print "</select>";
							print "</div>";
						}
						print "<div class='paper-toner'>PAPER</div>"; // ********************* PAPER
						print "<div class='product_listing' style='background-color:green'>LETTER (8.5 x 11\") Boxes: ";
						print "<select name='letter_box'>";
						for ($i=0; $i<=5; $i++)
						{
							print "<option value='" . $i . "'>" . $i . "</option>";
						}
						print "</select> Reams: ";
						print "<select name='letter_ream'>";
						for ($i=0; $i<=10; $i++)
						{
							print "<option value='" . $i . "'>" . $i . "</option>";
						}
						print "</select>";
						print "</div>";

						if ($_GET['loc'] == 19 || $_GET['loc'] == 31) // ***************** tabloid size paper
						{
							print "<div class='product_listing' style='background-color:brown'>TABLOID (11 x 18\") Boxes: ";
							print "<select name='tabloid_box'>";
							for ($i=0; $i<=5; $i++)
							{
								print "<option value='" . $i . "'>" . $i ."</option>";
							}
							print "</select> Reams: ";
							print "<select name='tabloid_ream'>";
							for ($i=0; $i<=10; $i++)
							{
								print "<option value='" . $i . "'>" . $i . "</option>";
							}
							print "</select>";
							print "</div>";
						}
					
					print "<h3>you must submit:</h3>"; // ***************************************** SUBMIT
					print "<input id='youmust' type='submit' value='YOUMUST' />";
					
					}
				?>
			</div>

			</form>
		</div>

	<!--	<a class="loc_btn" onClick="document.getElementById('instructions').style='display:;'" title="WTF?" id="wtf">?</a>
		<div id="instructions">
			<h3>Yo MoFo</h3>
			<p>this will give some instructions about what to do in case you can't figure it out.</p>
			<a onClick="document.getElementById('instructions').style='display:none'">CLOSE</a>
		</div> 
	-->
	</body>
</html>
