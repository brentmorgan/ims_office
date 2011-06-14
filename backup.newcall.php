<?php   /* ********************* call.php ********************* */
session_start();
include('../../checklist/includes/loggedin.php');
include('newcall.inc.php');
include('../../checklist/mobile_device_detect.php');
mobile_device_detect('http://imsdev.wesleyan.edu/checklist/iphone/',true,true,true,false,false);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Classroom Support Call Tracking</title>
	<link rel="stylesheet" type="text/css" href="../../checklist/css/classroom.css" />
	<script type="text/javascript" src="../../checklist/js/prototype.js"></script>
	<script type="text/javascript" src="../../checklist/js/call.js"></script>
	<script type="text/javascript" src="../../checklist/js/scriptaculous.js"></script>

	<script type="text/javascript" language="javascript"> 

		function nothing(){
			alert('nothing');
		}

		function submitDat(){
			allData = '<?php foreach($_POST as $key => $value){
						$value = str_replace("\r\n", " ", $value);
						print "&".$key."=".htmlspecialchars(urlencode($value));
					}
				?>';
			url='newcall.inc.php';
			allData = allData.replace(/\n/g, " ");
			data="action=logNewCall"+allData;

			var HTTPRequest = new Ajax.Request(url,{method: 'post', parameters: data, onComplete: submitDatCallBack});
			document.getElementById('buttons').style.display='none';
			document.getElementById('form_real_calls').style.display='none';
			document.getElementById('form_other_calls').style.display='none';
			document.getElementById('equipment_checkboxes').style.display='none';	
		}

		function submitDatCallBack(oReq){
			response = oReq.responseText;
			window.location = '<?php $_SERVER[PHP_SELF]; ?>';
			$('log_call').innerHTML = response;
			reset = "<h3>Report Submitted.</h3><p><a href='<?php $_SERVER[PHP_SELF]; ?>'>START OVER</a></p>";
			$('click_to_begin').innerHTML = reset;
		}

		function editRow(rowNum) {
			
			edit_edit = "<a href='javascript:stopEditing("+rowNum+")'>Done</a>";
			edit_problem = "<select id='prob_"+rowNum+"'><option value='uk'>Unknown</option><option value='ef'>Equipment Failure</option><option value='ue'>User Error</option></select>";
			edit_solved = "<select id='sol_"+rowNum+"'><option value='unsure'>Unsure</option><option value='no'>No</option><option value='yes'>Yes</option><option value='magic'>Fixed Itself</option></select>";
			edit_rt = "<input type='text' size='10' id='rt_number_"+rowNum+"' />";
			document.getElementById('edit_'+rowNum).innerHTML = edit_edit;
			document.getElementById('problem_'+rowNum).innerHTML = edit_problem;
			document.getElementById('solved_'+rowNum).innerHTML = edit_solved;
			document.getElementById('rt_'+rowNum).innerHTML = edit_rt;

		}

		function stopEditing(rowNum) {
			problem = document.getElementById('prob_'+rowNum).value;
			if (problem == 'ue'){ problem = 'yes'; }
			else if (problem == 'ef') { problem = 'no'; }
			else { problem = 'unsure'; }
			solved = document.getElementById('sol_'+rowNum).value;
			rt = document.getElementById('rt_number_'+rowNum).value;
	//		alert (problem + " " + solved + " " + rt);
			allData = '&problem='+problem+'&solved='+solved+'&rt='+rt+'&rowNum='+rowNum;
			url='newcall.inc.php';
			data="action=updateRow"+allData;

			var HTTPRequest = new Ajax.Request(url,{method:'post', parameters: data, onComplete: stopEditingCallBack});
		}	
	
		function stopEditingCallBack(oReq) {
			window.location = '<?php $_SERVER[PHP_SELF]; ?>';
		}

	</script> 
</head>

<body>
<!--
	<div id="issues" style="z-index:100; display:none; position:fixed; top:270px; right:35px; width:250px; height:550px; border: 1px solid #000; background-color:#eef;">
		<h3>Previous Issues</h3>
		<div id="issues_text" style="text-align:left; overflow:auto; position:relative; left:15px; width:220px; height: 500px; border: 1px solid #000; background-color:#fff;">None reported.</div>
	</div>
-->
	<div id="content" style="width:100%; margin-right:auto; margin-left:auto;">

		<?php include('../../checklist/includes/menu.inc.php'); include ('../../checklist/includes/calls_logo.php'); ?>

		<div id='click_to_begin' style='position:relative; top:-85px;'>

			<?php
				if($_POST){
				//	$_POST['notes']=trim($_POST['notes']);
					// *********** 	CONFIRMATION MESSAGE BEFORE SUBMITTING *****************
					print"<h3>This is a confirmation page.  You must click the SUBMIT button to log your report.</h3>";
					
				//	if($_POST['support'] == 'no') {
				//		print "<b>support:</b> no<br />".
				//			$_POST['otherCalls']."<br />".
				//			"<b>notes:</b> ".$_POST['notes']."<br />";
				//	} else {

						foreach($_POST as $key => $value) {
					//		if($key == "radio_resolved"){
					//			$key = "Resolved";
					//		}
					//		if($key != "buildings") {
					//			if($key == "otherCalls" && $_POST['support'] == "no" || (preg_match('/cl_/', $key)))
					//				{ print $value."<br />"; } 
					//			else if($key != "otherCalls") {
								print "<b>".$key.":</b> ".$value."<br />";
					//			}
					//		}
					//	}
					}
					print "<button name='confirm_report' id='confirm_report' value='SUBMIT' onClick='submitDat()'>SUBMIT</button> &nbsp; <button name='redo_report' id='redo_report' value='BACK' onClick='history.go(-1)'>back</button>";			
					// 	END OF CONFIRMATION MESSAGE *********
				} else {
					print "When EXT 4959 rings <a href='#' onClick='document.getElementById(\"buttons\").style.display=\"\";'>CLICK HERE</a> to begin.";
				}
			?>
		</div>
		<div id="buttons" style="display:none; padding:10px; position:relative; top:-85px;">
				<button name="real_call" id="real_call" onClick="document.getElementById('form_real_calls').style.display=''; document.getElementById('form_other_calls').style.display='none'; document.boxes.support.value='yes';">Classroom Support Call</button>
				 &nbsp; &nbsp; 
				<button name="fake_call" id="fake_call" onClick="document.getElementById('form_other_calls').style.display=''; document.getElementById('form_real_calls').style.display='none'; document.getElementById('log_call').style.display=''; log_supportcall='no'; document.boxes.support.value='no'; document.getElementById('equipment_checkboxes').style.display='none';">Other Call</button>
		</div>

			<form name="boxes" method="post" />
			<input type="hidden" name="support" value="ofofofof" />  <!-- value of this hidden field updates when one of the buttons is clicked: Classroom Support Call *or* Other Call --> 
		<div id='form_real_calls' style='display:none; padding:10px; position:relative; top:-85px;'>
			<?php 
			$building_list = grabBuildings();
			print "<select name='buildings' id='buildings' onChange='FFFFchooseBuilding();'>";
			print "<option value='10000' selected='selected'>Select a Building</option>";
			while($row = mysql_fetch_assoc($building_list)){
				print "<option value='$row[id]'>".$row['building']."</option>";
			}
			print "</select>";
			?>

			<div id="form_two" style="padding:10px;" ></div>
		</div>
		<div id="form_other_calls" style="display:none; padding:10px; position:relative; top:-85px;"> 
				<select name="otherCalls" id="otherCalls" onChange="log_otherreason=(this.value);">
					<option value="Not Classroom Support" selected="selected">NOT A CLASSROOM SUPPORT CALL</option>
					<?php $reasonList = getReasons();
						foreach($reasonList as $key => $val){
						print "<option value='$val[item]'>".$val['item']."</option>";
						}
					?>
				</select>
		</div>

		<div id="equipment_checkboxes" style="display:none; padding:10px; text-align:left; position:relative; top:-85px; margin-left:auto; margin-right:auto; width:400px;">
			<p>
			Was the issue resolved over the phone?<br />
			<input type="radio" name="radio_resolved" value="yes"> Yes <input type="radio" name="radio_resolved" value="no" checked> No
		
			</p>
			<p>General type of problem:<br />
			<input type="radio" name="radio_usererror" value="yes" > User Error <input type="radio" name="radio_usererror" value="no" /> Equipment Failure <input type="radio" name="radio_usererror" value="unsure" checked /> Not Sure	
			</p>
	
			<p>
			Sending a Technician to the room?<br />
			 <input type="radio" name="radio_tech" value="office" onClick="document.getElementById('tech').style.display=''" /> Yes, from Office <input type="radio" name="radio_tech" value="lab" onClick="document.getElementById('tech').style.display=''" /> Yes, from Lab <input type="radio" name="radio_tech" value="no" checked onClick="document.getElementById('tech').style.display='none'" /> No
			</p>
			<p id="tech" style="display:none;">
			Did the Technician Solve the Problem?<br />
			<input type="radio" name="radio_tech_solved" value="yes" /> Yes <input type="radio" name="radio_tech_solved" value="no" /> No <input type="radio" name="radio_tech_solved" value="unsure" checked /> Don't Know Yet<br /><input type="radio" name="radio_tech_solved" value="magic" /> Fixed Before Tech Arrived
			</p>
			<p>
			Creating / Adding to a ticket in <a href="http://rt.wesleyan.edu" target="_blank">RT</a>?<br />
			<input type="radio" name="radio_rt" value="yes" onClick="document.getElementById('ticket').style.display=''" /> Yes <input type="radio" name="radio_rt" value="no" checked onClick="document.getElementById('ticket').style.display='none'" /> No<br />
			<span id="ticket" style="display:none;">RT Ticket #: <input type="text" name="radio_rt_ticket" value="" /></span>
			</p>
		<!--	<span class="directions">Check the applicable boxes.</span><br />
			<?php /*   
				$results = grabChecklist();
					while($row = mysql_fetch_assoc($results)){
					print "<input type='checkbox' name='cl_".$row[id]."' value='".$row[item]."' >".$row['item']."<br />";
				}
				*/
			 ?>
		-->
			<div id="bottom_submit_button_div" style="position: relative; left:150px; padding:10px;">
				<input type="button" id="bottom_submit_button" value="LOG CALL" onClick="document.boxes.submit();" />
			</div>
		</div>
	</div>

	<!-- LEAVE THIS AT THE BOTTOM TO KEEP IT OUT OF THE WAY ::: ITS THE HOVERING 'LOG CALL' AREA AT THE LEFT  -->

<div id="log_call" style="display:none; position:fixed; top:270px; left:35px; width:250px; height:550px; border:1px solid #000; background-color:#eef;">
<!--	<h3> 
		<input type="button" id="top_submit_button" value="LOG CALL" onClick="log_notes=document.getElementById('notes').value; document.boxes.submit();" />
	</h3>
	<textarea rows="30" cols="26" name="notes" id="notes" onFocus="if(this.value=='Notes:'){this.value=''};">Notes:</textarea> 
-->
</div>

</form>

<div id="call_list" style="width:90%; margin:auto;">
	<?php 
		listCalls();
		echo $list;
	?>
</div>


</body>
</html>


