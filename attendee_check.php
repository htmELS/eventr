<?php
include('../../../wp-blog-header.php');
include('wpframe.php');

$name = $_REQUEST['name'];
if($name) {
	$details = $wpdb->get_results("SELECT ID,description, url FROM {$wpdb->prefix}eventr_attendee WHERE name='$name'");
	
	$attendee_info = array();
	foreach($details as $attendee) {
		$attendee_info[] = array(
			'id'	=> $attendee->ID,
			'name'	=> $name,
			'description'=>$attendee->description,
			'url'	=> $attendee->url
		);
	}
	
	$error = false;
	if(!count($attendee_info)) $error = true;
	print json_encode(array(
		"error"		=> $error,
		"success"	=> $attendee_info
	));
	exit;
	
}
print json_encode(array(
	"error"		=> t("No name given"),
	"success"	=> false
));

