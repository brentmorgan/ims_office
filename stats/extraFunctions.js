/* ****************** ERASES AN OLD PIE SO WE CAN DRAW THE NEW ONE ***************** */
/*
function clearPie(whichPie) {
	var pie = document.getElementById(whichPie);
	var context = pie.getContext('2d');
	context.fillStyle = "rgb(225,225,225)";
	context.fillRect(30,30,50,50);
}

function nonsense() {
	alert("pffffft");
}
*/

/*
WHENEVER SOMEONE CLICKS ON A PIE SLICE THIS WILL CHECK WEATHER OR NOT IT WAS A SLICE FROM THE "CALLS BY BUILDING" PIE. IF IT WAS IT WILL DISPLAY ADDITINOAL DATA ABOUT THAT BUILDING IN ANTOTHER PIE. IF IT WAS SOME OTHER PIE THAT WAS CLICKED ON THEN IT WONLT DO ANYTHING.
*/

//function checkForBuildingPie(tooltip) {
function shouldWeDrawAnotherPie(tooltip,pie_id) { // This function is called from within the RGraph.pies file
	//alert('hi! '+pie_id);

	if (pie_id == "pie1") { // if they clicked on pie 1 we need to draw pie 2. if they clicked on pie 2 we don't do anything.
		pie_type = document.getElementById('calls_by').value;
		label = tooltip.substr(0, tooltip.indexOf("(")-1);
	//	alert("Building name is *"+label+"*");

		url = 'pies.inc.php';
		data = 'action=whatDidTheyClick&pie_type='+pie_type+'&slice='+label;
		var HTTPRequest = new Ajax.Request(url,{method:'post', parameters: data, onSuccess: shouldWeDrawAnotherPieCallBack});
	}
}

function shouldWeDrawAnotherPieCallBack(oReq) {
	response = oReq.responseText; 
	//response = response.substr(1); // take off the "&" at the front // ********************This line isn't necessary for the "new" method, with the bar graphs
										// ************* But it we go back to the second pie as before this will be needed
	//alert(response[0]);
	drawSecondPie(response);

}


