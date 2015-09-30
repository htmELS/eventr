<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

if($_REQUEST['action'] == 'delete' or $_REQUEST['action'] == t('Delete Selected')) {
	foreach ($_REQUEST['selected_rows'] as $id) {
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}eventr_event_attendee WHERE attendee_ID='%d'", $id));
	}
}
$event_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}eventr_event WHERE ID=%d", $_REQUEST['event']));
?>

<div class="wrap">
<h2><?php echo t("Attendees for ") . stripslashes($event_name); ?></h2>

<?php
wp_enqueue_script( 'listman' );
wp_enqueue_script( 'jquery' );
wp_print_scripts();
?>

<p><?php printf(t('To add the registeration form to your blog, insert the code [EVENTR REGISTRATION %d] into any post.'), $_REQUEST['event']) ?></p>
<p><?php printf(t('To show the attendee list, insert the code [EVENTR ATTENDEES %d] into any post.'), $_REQUEST['event']) ?></p>


<?php
$offset = 0;
$page = 1;
$total_items = 0;
$page_links = '';
$items_per_page = 10;
$items_per_page_choice = array(10,50,100,500);
if(isset($_REQUEST['items_per_page'])) $items_per_page = $_REQUEST['items_per_page'];

if(isset($_REQUEST['paged']) and $_REQUEST['paged']) {
	$page = intval($_REQUEST['paged']);
	$offset = ($page - 1) * $items_per_page;
}
// Retrieve the Attendees
$search = '';
if(isset($_REQUEST['search']) and $_REQUEST['search']) $search = "AND A.name LIKE '%$_REQUEST[search]%'";

$all_attendee = $wpdb->get_results("SELECT A.ID,A.name,A.url,A.email, EA.added_on, A.status FROM `{$wpdb->prefix}eventr_attendee` AS A
										INNER JOIN `{$wpdb->prefix}eventr_event_attendee` AS EA ON attendee_ID=A.ID
										WHERE EA.event_ID=$_REQUEST[event] $search ORDER BY A.name LIMIT $offset, $items_per_page");
$moderation_on = get_option('eventr_moderation');

?>
<form action="" method="post"><!-- Need to put this in 2 diff forms -or searching paging will be mixed with other paging -->
<input type="text" value="<?php echo $_REQUEST['search'] ?>" name="search" />
<input type="submit" value="Search" name="action" />
<input type="hidden" name="paged" value="1" />
</form>

<form action="" method="post">
<?php
if(count($all_attendee)) { 
print '<div class="tablenav">';
// Get total attendees - for paging purpose
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}eventr_attendee` AS A
										INNER JOIN `{$wpdb->prefix}eventr_event_attendee` AS EA ON attendee_ID=A.ID
										WHERE EA.event_ID=$_REQUEST[event] $search");
$total_pages = ceil($total_items / $items_per_page);
$page_links = paginate_links( array(
	'base' => add_query_arg( 'paged', '%#%' ),
	'format' => '',
	'total' => $total_pages,
	'current' => $page
));
if ( $page_links ) echo "<div class='tablenav-pages'>$page_links</div>";
?>

Items Per Page: <select name="items_per_page" id="items_per_page">
<?php
foreach($items_per_page_choice as $v) {
	print "<option value='$v'";
	if($_REQUEST['items_per_page'] == $v) print ' selected="selected"';
	print ">$v</option>";
}
?>
</select>
<br />
<?php } ?>


<table class="widefat">
	<thead>
	<tr>
		<th scope="col">&nbsp;</th>
		<th scope="col" style="text-align: center;">#</div></th>
		<th scope="col"><?php e('Attendee Name') ?></th>
		<th scope="col"><?php e('Email') ?></th>
		<th scope="col"><?php e('Registered On') ?></th>
		<th scope="col" colspan="2"><?php e('Action') ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
if (count($all_attendee)) {
	$attendee_count = $offset;
	foreach($all_attendee as $attendee) {
		$class = ('alternate' == $class) ? '' : 'alternate';
		$attendee_count++;
		?>
		<tr id='attendee-<?php echo $attendee->ID ?>' class='row <?php echo $class ?>' <?php 
			if($moderation_on == 1 and $attendee->status == 0) echo 'style="background-color:#ffd59d;"';?>>
		<th scope="row"><input type="checkbox" name="selected_rows[]" value="<?php echo $attendee->ID?>" class="row-selector" /></th>
		<th scope="row" style="text-align: center;"><?php echo  $attendee_count ?></th>
		<td><?php
		if($attendee->url) print "<a href='{$attendee->url}'>". stripslashes($attendee->name) . "</a>";
		else print stripslashes($attendee->name);
		?></td>
		<td><?php echo $attendee->email?></td>
		<td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($attendee->added_on)) ?></td>
		<td><a href='edit.php?page=eventr/attendee_form.php&amp;attendee=<?php echo $attendee->ID?>&amp;event=<?php echo $_REQUEST['event']?>&amp;paged=<?php echo isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1 ?>' 
			class='edit'><?php e('View/Edit Details')?></a></td>
		<td><a href='edit.php?page=eventr/attendees.php&amp;action=delete&amp;selected_rows[]=<?php echo $attendee->ID?>&amp;event=<?php echo $_REQUEST['event']?>&amp;paged=<?php echo isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1 ?>' 
			class='delete' onclick="return confirm('<?php e(addslashes("You are about to delete this Attendee. Press 'OK' to delete and 'Cancel' to stop."))?>');"><?php e('Delete')?></a></td>
		</tr>
<?php
		}
	} else {
?>
	<tr>
		<td colspan="6"><?php e('No attendeees found.') ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>

<?php if(count($all_attendee)) { ?>
<input type="submit" value="<?php e('Delete Selected') ?>" id="delete-selected-rows" name="action" /><br />
<a href="edit.php?page=eventr/export_choose.php&amp;event=<?php echo $_REQUEST['event']?>"><?php e('Export Attendee data for this event as a CSV file') ?></a>

<div class="tablenav">
<?php
if ( $page_links ) echo "<div class='tablenav-pages'>$page_links</div>";
?>

<?php e("Items Per Page"); ?>: <select name="items_per_page" id="items_per_page">
<?php
foreach($items_per_page_choice as $v) {
	print "<option value='$v'";
	if($_REQUEST['items_per_page'] == $v) print ' selected="selected"';
	print ">$v</option>";
}
?>
</select>
</div>
<input type="hidden" name="paged" value="<?php echo  isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1 ?>" />


<?php } ?>
</form>
<br />
</div>

<script type="text/javascript">
function init() {
	jQuery(".row").click(function(e){
		if(e.target.tagName == "INPUT") return;
		var input = this.getElementsByTagName("input")[0];
		input.checked = (input.checked) ? false : true;
	});
	
	
	jQuery("#delete-selected-rows").click(function(e) {
		var cancel = false;
		
		// Make sure that there is a few rows selected before attempting a delete.
		var selected_count = 0;
		jQuery(".row-selector").each(function() {
			if(this.checked) selected_count++;
		});
		if(!selected_count) {
			cancel = true;
			alert('<?php e('Please select some rows to delete') ?>');
		}
		
		// Show a confirmation message.
		if(!cancel && !confirm('<?php echo addslashes(t("You are about to delete this Attendee. Press 'OK' to delete and 'Cancel' to stop."))?>')) cancel = true;
		
		if(cancel) {
			e.preventDefault();
			e.stopPropagation();
		}
	});
	
	jQuery("#items_per_page").change(function(e) {
		var url = document.location.href;
		url = url.replace(/\&items_per_page\=\d+/,"");
		document.location.href = url +'&items_per_page='+this.value;
	});
}
jQuery(document).ready(init);
</script>
