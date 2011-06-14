<?php   /* ********************* call.php ********************* */
header('location:newcall.php');

//  THIS WHOLE PAGE SHOULD JUST BE REMOVED 
//  KEEPING IT HERE FOR NOW IN CASE THERE ARE ANY  OTHER PAGES THAT STILL LINK TO CALL.PHP INSTEAD OF NEWCALL.PHP


//session_start();
//include('../../checklist/includes/loggedin.php');
//include('call.inc.php');
//include('../../checklist/mobile_device_detect.php');
//mobile_device_detect('http://imsdev.wesleyan.edu/checklist/iphone/',true,true,true,false,false);
?>

<?php 
/*
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
			url='call.inc.php';
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
			$('log_call').innerHTML = response;
			reset = "<h3>Report Submitted.</h3><p><a href='<?php $_SERVER[PHP_SELF]; ?>'>START OVER</a></p>";
			$('click_to_begin').innerHTML = reset;
		}
	</script> 
</head>

<body>
	<div id="issues" style="z-index:100; display:none; position:fixed; top:270px; right:35px; width:250px; height:550px; border: 1px solid #000; background-color:#eef;">
		<h3>Previous Issues</h3>
		<div id="issues_text" style="text-align:left; overflow:auto; position:relative; left:15px; width:220px; height: 500px; border: 1px solid #000; background-color:#fff;">None reported.</div>
	</div>
	<div id="content" style="width:100%; margin-right:auto; margin-left:auto;">

		<?php include('../../checklist/includes/menu.inc.php'); include ('../../checklist/includes/calls_logo.php'); ?>

		<div id='click_to_begin' style='position:relative; top:-85px;'>

			<?php
				if($_POST){
					$_POST['notes']=trim($_POST['notes']);
					// *********** 	CONFIRMATION MESSAGE BEFORE SUBMITTING *****************
					print"<h3>This is a confirmation page.  You must click the SUBMIT button to log your report.</h3>";
					
					if($_POST['support'] == 'no') {
						print "<b>support:</b> no<br />".
							$_POST['otherCalls']."<br />".
							"<b>notes:</b> ".$_POST['notes']."<br />";
					} else {

						foreach($_POST as $key => $value) {
							if($key == "radio_resolved"){
								$key = "Resolved";
							}
							if($key != "buildings") {
								if($key == "otherCalls" && $_POST['support'] == "no" || (preg_match('/cl_/', $key)))
									{ print $value."<br />"; } 
								else if($key != "otherCalls") {
								print "<b>".$key.":</b> ".$value."<br />";
								}
							}
						}
					}
					print "<button name='confirm_report' id='confirm_report' value='SUBMIT' onClick='submitDat()'>SUBMIT</button> &nbsp; <button name='redo_report' id='redo_report' value='back' onClick='history.go(-1)'>back</button>";			
					// 	END OF CONFIRMATION MESSAGE *********
				} else {
					print "When EXT 4959 rings <a href='#' onClick='document.getElementById(\"buttons\").style.display=\"\";'>CLICK HERE</a> to begin.";
				}
			?>
		</div>
		<div id="buttons" style="display:none; padding:10px; position:relative; top:-85px;">
				<button name="real_call" id="real_call" onClick="document.getElementById('form_real_calls').style.display=''; document.getElementById('form_other_calls').style.display='none'; document.getElementById('log_call').style.display=''; log_supportcall='yes'; document.getElementById('issues').style.display=''; document.boxes.support.value='yes';">Classroom Support Call</button>
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
			<span>
			<input type="radio" name="radio_resolved" value="yes"> Issue was resolved over phone <input type="radio" name="radio_resolved" value="no" checked> Not resolved
			</span>
		<br />	
			<span class="directions">Check the applicable boxes.</span><br />
			<?php   
				$results = grabChecklist();
					while($row = mysql_fetch_assoc($results)){
					print "<input type='checkbox' name='cl_".$row[id]."' value='".$row[item]."' >".$row['item']."<br />";
				}
			 ?>
			<div id="bottom_submit_button_div" style="position: relative; left:150px; padding:10px;">
				<input type="button" id="bottom_submit_button" value="LOG CALL" onClick="log_notes=document.getElementById('notes').value; document.boxes.submit();" />
			</div>
		</div>
	</div>

	<!-- LEAVE THIS AT THE BOTTOM TO KEEP IT OUT OF THE WAY ::: ITS THE HOVERING 'LOG CALL' AREA AT THE LEFT  -->

<div id="log_call" style="display:none; position:fixed; top:270px; left:35px; width:250px; height:550px; border:1px solid #000; background-color:#eef;">
	<h3> 
		<input type="button" id="top_submit_button" value="LOG CALL" onClick="log_notes=document.getElementById('notes').value; document.boxes.submit();" />
	</h3>
	<textarea rows="30" cols="26" name="notes" id="notes" onFocus="if(this.value=='Notes:'){this.value=''};">Notes:</textarea> 
</div>

</form>


</body>
</html>

*/ 
?>
