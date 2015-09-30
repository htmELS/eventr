<?php
include('../../../wp-blog-header.php');
auth_redirect();
if($wp_version >= '2.6.5') check_admin_referer('eventr_create_edit_event');
include('wpframe.php');

// I could have put this in the event_form.php - but the redirect will not work.

if(isset($_REQUEST['submit'])) {
	$status = ($_REQUEST['status']) ? 1 : 0;
	if($_REQUEST['action'] == 'edit') { //Update goes here
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}eventr_event SET name=%s,description=%s,"
			. " event_date=%s, maximum_attendees=%d, landing_page=%s, status=%s WHERE ID=%d",
			$_REQUEST['name'],$_REQUEST['content'], $_REQUEST['event_date'], $_REQUEST['maximum_attendees'], $_REQUEST['landing_page'], $status, $_REQUEST['event']));
		
		wp_redirect($wpframe_home . '/wp-admin/edit.php?page=eventr/events.php&message=updated');
	
	} else {
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}eventr_event(name,description,event_date,maximum_attendees,landing_page, status,added_on) VALUES(%s,%s,%s,%s,%s,%s, NOW())",
			$_REQUEST['name'], $_REQUEST['content'], $_REQUEST['event_date'],$_REQUEST['maximum_attendees'], $_REQUEST['landing_page'], $status));
		$event_id = $wpdb->insert_id;
		wp_redirect($wpframe_home . '/wp-admin/edit.php?page=eventr/events.php&message=new_event&event='.$event_id);
	}
}

exit;
