<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

if($_REQUEST['message'] == 'updated') {
	wpframe_message('Event Updated', 'updated');
} elseif($_REQUEST['message'] == 'new_event') {
	wpframe_message('Event Added', 'updated');
}

if($_REQUEST['action'] == 'delete') {
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}eventr_event WHERE ID='$_REQUEST[event]'");
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}eventr_event_attendee WHERE event_ID='$_REQUEST[event]'");
	wpframe_message('Event Deleted', 'updated');
}
?>

<div class="wrap">
<h2><?php e("Manage Events"); ?></h2>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;"><?php e("#") ?></div></th>
		<th scope="col"><?php e("Title") ?></th>
		<th scope="col"><?php e("Number Of Attendees") ?></th>
		<th scope="col"><?php e("Event Date") ?></th>
		<th scope="col"><?php e("Status") ?></th>
		<th scope="col" colspan="3"><?php e("Action") ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
// Retrieve the eventes
$all_event = $wpdb->get_results("SELECT E.ID,E.name,E.event_date,E.status,(SELECT COUNT(*) FROM {$wpdb->prefix}eventr_event_attendee WHERE event_ID=E.ID) AS attendee_count
									FROM `{$wpdb->prefix}eventr_event` AS E");

if (count($all_event)) {
	$status = array(t('Inactive'), t('Active'));
	foreach($all_event as $event) {
		$class = ('alternate' == $class) ? '' : 'alternate';
		print "<tr id='event-{$event->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?=$event->ID ?></th>
		<td><?=stripslashes($event->name)?></td>
		<td><?=$event->attendee_count ?></td>
		<td><?=($event->event_date == '0000-00-00') ? '' : date(get_option('date_format'), strtotime($event->event_date)) ?></td>
		<td><?=$status[$event->status]?></a></td>
		<td><a href='edit.php?page=eventr/attendees.php&amp;event=<?=$event->ID?>' class='edit'><?=t('Manage Attendees')?></a></td>
		<td><a href='edit.php?page=eventr/event_form.php&amp;event=<?=$event->ID?>&amp;action=edit' class='edit'><?= t('Edit'); ?></a></td>
		<td><a href='edit.php?page=eventr/events.php&amp;action=delete&amp;event=<?=$event->ID?>' class='delete' onclick="return confirm('<?=addslashes(sprintf(t("You are about to delete '%s'. This will delete all the attendees within this event. Press 'OK' to delete and 'Cancel' to stop."), $event->name))?>');"><?=t('Delete')?></a></td>
		</tr>
<?php
		}
	} else {
?>
	<tr>
		<td colspan="8"><?php e('No events found.') ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>

<a href="edit.php?page=eventr/event_form.php&amp;action=new"><?php e("Create New Event") ?></a>
</div>
