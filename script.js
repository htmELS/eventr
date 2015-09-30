// See if we have the record of this attendee.
function checkAttendees() {
	var name = this.value;
	var home = document.getElementById("site_home").value;
	if(home) {
		jQuery.ajax({
			"url": home + "/wp-content/plugins/eventr/attendee_check.php?name=" + escape(name),
			"error": ajaxError,
			"success": checkAttendeeHistory,
			"dataType":"json"
		});
	}
}

function checkAttendeeHistory(data) {
	if(data.error) return;
	
	people = data.success; //Global
	
	var html = ["Found earlier attendee with the name '" + people[0].name + "'. If you find your profile in the list below, click on the 'Use Profile' button, and we'll use those details. If your profile is not there in the below list, just ignore this message and continue filling out the form." ];
	var len = people.length;
	for(var i=0; i<len; i++) {
		html.push("Name: " + people[i].name);
		html.push("Url: " + people[i].url);
		html.push("Bio: " + people[i].description);
		html.push("<input type='button' onclick='useProfile("+people[i].id+");' value='Use Profile' /><br />");
		if(i < len-1) html.push("<hr />");
	}
	jQuery("#profile-search").append(html.join("<br />")).show();
}

function useProfile(id) {
	jQuery("#profile-details").hide();
	document.getElementById("attendee_id").value = id;
}

function ajaxError() {
	//No biggie - let the user continue what he's doing.
}

function init() {
	jQuery("#attendee_name").change(checkAttendees);
}

jQuery(document).ready(init); 
