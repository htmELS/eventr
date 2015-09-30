<?php
include('../../../wp-blog-header.php');
auth_redirect();
include('wpframe.php');

// Export data as a CSV File
$event_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}eventr_event WHERE ID=%d", $_REQUEST['event']));


$all_attendee = $wpdb->get_results("SELECT A." . join(",A.", $_REQUEST['fields']) . " FROM `{$wpdb->prefix}eventr_attendee` AS A
										INNER JOIN `{$wpdb->prefix}eventr_event_attendee` AS EA ON attendee_ID=A.ID
										WHERE EA.event_ID=$_REQUEST[event] ORDER BY A.name", ARRAY_N);

$event_name = preg_replace('/\W/', '_', $event_name);
$event_name = preg_replace('/_{2,}/', '_', $event_name);

header("Content-type:text/octect-stream");
header("Content-Disposition:attachment;filename=$event_name.csv");

print '"' . join('","', $_REQUEST['fields']) . '"'. "\n";
foreach($all_attendee as $attendee) {
	print '"' . stripslashes(implode('","', $attendee)) . "\"\n";
}