<?php   /* ********************* call.php ********************* */
session_start();
include('../../checklist/includes/loggedin.php');
include('newcall.inc.php');
//include('../../checklist/mobile_device_detect.php');
//mobile_device_detect('http://imsdev.wesleyan.edu/checklist/iphone/',true,true,true,false,false);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Classroom Support Call Tracking</title>
	<style type="text/css">
		.boxy { -moz-border-radius:10px; -webkit-border-radius:10px; background-color:#eee; border:1px solid #000; padding:20px; margin:20px; }
		.spacer { background-color:#666; border:0 }
	</style>
	<link rel="stylesheet" type="text/css" href="../../checklist/css/classroom.css" />
	<script type="text/javascript" src="../../checklist/js/prototype.js"></script>
	<script type="text/javascript" src="../../checklist/js/call.js"></script>
	<script type="text/javascript" src="../../checklist/js/scriptaculous.js"></script>

</head>

<body>
	<div id="content" style="width:100%; margin-right:auto; margin-left:auto;">

		<?php include('../../checklist/includes/menu.inc.php'); 

		include ('../../checklist/includes/calls_logo.php'); ?>

		<div id="container" style="position: relative; top:-85px; width:1250px; padding:10px; margin: auto; background-color:#666; border:1px solid #000; -moz-border-radius:10px; -webkit-border-radius: 10px;">
			<table width="100%">
				<tr width="100%" height="500px">
				<!-- ********************************* FIRST BOX ************************************ -->
					<td width="32%" class="boxy">
						<div id="buttons" style="margin-left:auto; margin-right:auto; position:relative; top:-120px;">
							<div class="directions">
								When the phone rings you should answer it. Then click one of these buttons:
							</div>
							<button name="real_call" id="real_call" onClick="document.getElementById('form_real_calls').style.display=''; document.getElementById('form_other_calls').style.display='none'; document.boxes.support.value='yes';">Classroom Support Call</button>
				 			&nbsp; &nbsp; 
							<button name="fake_call" id="fake_call" onClick="document.getElementById('bottom_submit_button').style.display=''; document.getElementById('form_other_calls').style.display=''; document.getElementById('form_real_calls').style.display='none'; log_supportcall='no'; document.boxes.support.value='no'; document.getElementById('equipment_checkboxes').style.display='none';">Other Call</button>


							<form name="boxes" method="post" action="newcalllist.php">
							<input type="hidden" name="support" value="ofofofof" />  <!-- value of this hidden field updates when one of the buttons is clicked: Classroom Support Call *or* Other Call --> 
							<div id='form_real_calls' style='display:none; padding:10px;'>
								<?php 
								$building_list = grabBuildings();
								print "<select name='buildings' id='buildings' onChange='FFFFchooseBuilding();'>";
								print "<option value='error' selected='selected'>Select a Building</option>";
								while($row = mysql_fetch_assoc($building_list)){
									print "<option value='$row[id]'>".$row['building']."</option>";
								}
								print "</select>";
								?>

								<div id="form_two" style="padding:10px;" >
								</div> <!-- this will display the updated list of rooms, depending on which building was chosen -->
							</div>

							<div id="form_other_calls" style="display:none; padding:10px;"> 
								<select name="otherCalls" id="otherCalls" onChange="log_otherreason=(this.value);">
									<option value="Not Classroom Support" selected="selected">NOT A CLASSROOM SUPPORT CALL</option>
									<?php $reasonList = getReasons();
									foreach($reasonList as $key => $val){
										print "<option value='$val[item]'>".$val['item']."</option>";
									}
									?>
								</select>
							</div>
						</div>
					</td>
					<td width="2%" class="spacer">&nbsp;</td>
				<!-- ************************************** SECOND BOX ************************************** -->
					<td width="32%" class="boxy">
						<div id="equipment_checkboxes" style="display:none; position:absolute; top:20px; text-align:left;">
							<p>
								Was the issue resolved over the phone?<br />
								<input type="radio" name="radio_resolved" value="yes"> Yes <input type="radio" name="radio_resolved" value="no" checked> No
		
							</p>
							<p>General type of problem:<br />
								<input type="radio" name="radio_usererror" value="yes" onClick="Effect.SlideDown('checkbox')" > User Error <input type="radio" name="radio_usererror" value="no" onClick="Effect.SlideDown('checkbox')" /> Equipment Failure 
								<br /><input type="radio" name="radio_usererror" value="software" onClick="document.getElementById('checkbox').style.display='none'" /> Software Issue <input type="radio" name="radio_usererror" value="unsure" checked onClick="document.getElementById('checkbox').style.display='none'" /> Not Sure	
							</p>
	
							<p>
								Sending a Technician to the room?<br />
								 <input type="radio" name="radio_tech" value="office" onClick="Effect.SlideDown('tech')" /> Yes, from Office <input type="radio" name="radio_tech" value="lab" onClick="Effect.SlideDown('tech')" /> Yes, from Lab <input type="radio" name="radio_tech" value="no" checked onClick="document.getElementById('tech').style.display='none'" /> No
							</p>
							<p id="tech" style="display:none;">
								Did the Technician Solve the Problem?<br />
								<input type="radio" name="radio_tech_solved" value="yes" /> Yes <input type="radio" name="radio_tech_solved" value="no" /> No <input type="radio" name="radio_tech_solved" value="unsure" checked /> Don't Know Yet<br /><input type="radio" name="radio_tech_solved" value="magic" /> Fixed Before Tech Arrived
							</p>
							<p>
								Creating / Adding to a ticket in <a href="http://rt.wesleyan.edu" target="_blank">RT</a>?<br />
								<input type="radio" name="radio_rt" value="yes" onClick="document.getElementById('ticket').style.display=''" /> Yes <input type="radio" name="radio_rt" value="no" checked onClick="document.getElementById('ticket').style.display='none'" /> No<br />
								<span id="ticket" style="display:none;">
									RT Ticket #: 
									<input type="text" name="radio_rt_ticket" value="" />
								</span>
							</p>
						</div>
					</td>
					<td width="2%" class="spacer">
						&nbsp;
					</td>
				<!-- ******************************************** THIRD BOX *************************************** -->
					<td width="32%" class="boxy">
						<div id="checkbox" style="text-align:left; display:none;">
							<span class="directions">
								Check the problem items:
							</span>
							<br />
							<?php   
							$results = grabNewChecklist();
							while($row = mysql_fetch_assoc($results)){
								print "<input type='checkbox' name='cl_".$row[id]."' value='".$row[item]."' >".$row['item']."<br />";
							}
							?>
							Other: <input type="text" name="cl_other" size="20" maxlength="25" />
						</div>
					</td>
				</tr>
			</table>
		<!-- ******************************************* SUBMIT BUTTON ***************************** -->
			<div id="bottom_submit_button_div" style="position:; padding:10px;">
				<input type="button" id="bottom_submit_button" value="LOG CALL" onClick="document.boxes.submit();" style="display:none" />
			</div>

		</div> <!-- ****************** END CONTAINER ************* -->
	</form>

</body>
</html>


