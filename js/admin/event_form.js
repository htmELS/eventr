var field_count = 1;
var field_template;
function addField() {
	field_count++;
	var field_html = field_template.replace(/_1_/g, "_" + field_count + "_");
	
	jQuery("#extra_field_area").append(field_html);
}

function showTypeOptions(e) {
	var field_number = this.id.replace(/\D/g,'');//Get just the number.
	if(this.value == "select") jQuery("#extra_"+field_number+"_select_option").show();
	else jQuery("#extra_"+field_number+"_select_option").hide();
}
 
function init() {
	jQuery("#add_field").click(addField);
	field_template = jQuery("#field_details_1").html();
	jQuery("#extra_1_type").change(showTypeOptions)
}
jQuery(document).ready(init);
