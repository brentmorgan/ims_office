
<div id="controls">
<!--<div id="top_title">
	IMS ~ CLASSROOM SUPPORT CALL STATISTICS
</div> -->
<form name="ui_form" id="ui_form" method="post">
	<div id="div_dates_and_calls_by">
		<p>
		Date Range:
		<select name="st_mo">
		<?php
			$months = array("zero","January","February","March","April","May","June","July","August","September","October","November","December");
			$i = 1; // a counter....
			foreach ($months as $row) {
				print "<option value='" . $i . "'";
				if ($_POST['st_mo']==$i || (!$_POST && $i==1)) {
					print " selected";
				}
				print ">" . $months[$i++] . "</option>";
			}
		?>
		</select>
	<!--	<select name="st_da">
		<?php 
		/*	for ($i=1; $i <= 31; $i++) {
				print "<option value='" . $i . "'";
				if ($_POST['st_da']==$i || (!$_POST && $i==1)) {
					print " selected";
				}
				print ">" . $i . "</option>";
			} */
		?>
		</select> -->
		<input type="hidden" name="st_da" value="1" />
		<select name="st_ye">
		<?php
			for ($i=2009; $i <= date("Y"); $i++) {
				print "<option value='" . $i . "'";
				if ($_POST['st_ye']==$i || (!$_POST && $i=='2010')) {
					print " selected";
				}
				print ">" . $i . "</option>";
			}
		?>
		</select>
		TO
		<select name="en_mo">
		<?php
			$months = array("zero","January","February","March","April","May","June","July","August","September","October","November","December");
			$i = 1;
			foreach ($months as $row) {
				print "<option value='". $i . "'";
				if ($_POST['en_mo']==$i || (!$_POST && $i == date("n"))) {
					print " selected";
				}
				print ">" . $months[$i++] . "</option>";
			}
		?>
		</select>
	<!--	<select name="en_da">
		<?php 
		/*	for ($i=1; $i <= 31; $i++) {
				print "<option value='" . $i . "'";
				if ($_POST['en_da'] == $i || (!$_POST && $i == date("j"))) {
					print " selected";
				}
				print ">" . $i . "</option>";
			}
		*/
		?>
		</select>
	-->	<input type="hidden" name="en_da" value="31" />

		<select name="en_ye">
		<?php
			for ($i=2009; $i <= date("Y"); $i++) {
				print "<option value='" . $i . "'";
				if ($_POST['en_ye'] == $i || (!$_POST && $i == date("Y"))) {
					print " selected";
				}
				print ">" . $i . "</option>";
			}
		?>
		</select>
	</p>
	<p>
	Calls By
		<select name="calls_by" id="calls_by" onChange="checkCallsByValue(this.value)">
			<option value="all_calls" <?php if ($_POST['calls_by']=='all_calls') { print "selected"; } ?>>All Calls</option>
			<option value="cs_calls" <?php if ($_POST['calls_by']=='cs_calls') { print "selected"; } ?>>Classroom Support Calls</option>
			<option value="other_calls" <?php if ($_POST['calls_by']=='other_calls') { print "selected"; } ?> >Other Calls</option>
			<option value="by_location" <?php if ($_POST['calls_by']=='by_location') { print "selected"; } ?> >By Location</option>
		</select>
<!--	Group By
		<select name="group_by" id="group_by" onChange="form.submit()">
			<option value="by_month" <?php if ($_POST['group_by']=='by_month') { print "selected"; } ?>>Month</option>
			<option value="by_week" <?php if ($_POST['group_by']=='by_week') { print "selected"; } ?>>Week</option>
			<option value="by_year" <?php if ($_POST['group_by']=='by_year') { print "selected"; } ?>>Year</option>
		</select>
-->
		<select name="location" id="location" style="display: <?php if ($_POST['calls_by']=='by_location') { print ''; } else { print 'none'; } ?> ">
			<option value=""> --- choose --- </option>
			<?php 
				$buildings = grabBuildings();
				while ($row = mysql_fetch_assoc($buildings)) {
					print "<option value = '$row[id]' ";
					if ($_POST['location'] == $row['id']) {
						print "selected";
					}
					print" >" . $row['building'] . "</option>";
				}
			?>			
		</select>

		<span id="span_more_info" <?php if ($_POST['calls_by']=='cs_calls' || $_POST['calls_by']=='other_calls'){print " style='display:none'";} ?> ><input type="checkbox" name="more_info" id="more_info" <?php if ($_POST['more_info']=='on') { print "checked"; } ?> />More Info</span>
	</p>
</div> <!-- end of div_dates_and_calls_by -->
<div id="div_pie_or_lines">
	<a href="lines.php"><img src="http://imsdev.wesleyan.edu/ims/images/call_tracking_line.png" /></a>
	<a href="pies.php"><img src="http://imsdev.wesleyan.edu/ims/images/call_tracking_pie.png" /></a>
</div> <!-- end of div_pie_or_lines -->
<div id="div_submit_button">
	<input type="submit"> <!-- <input type="button" value="Submit" onClick="drawFirstPie()" /> -->
</div> <!-- end of div_submit_button -->
<div id="top_title">
	IMS - CLASSROOM SUPPORT CALL STATISTICS
</div>
</form>

</div>
