<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$attendee = array();
if($_REQUEST['action'] == 'edit') {
	$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}eventr_attendee SET name=%s, description=%s, url=%s, email=%s, phone=%s,status=%s WHERE ID=%d", 
								strip_tags($_POST['attendee_name']), $_POST['content'], $_POST['url'], $_POST['email'], $_POST['phone'], 
								(isset($_POST['status']) ? 1 : 0), $_POST['attendee']));
	wpframe_message("Attendee details updated");
}
$attendee = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}eventr_attendee WHERE ID = $_REQUEST[attendee]");
?>

<div class="wrap">
<h2><?php e('View/Edit Attendee') ?></h2>

<input type="hidden" id="title" name="ignore_me" value="This is here as a workaround for an editor bug" />

<?php
wpframe_add_editor_js();
?>
<form name="post" action="" method="post" id="post">
<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<div class="postbox">
<h3 class="hndle"><span><?php e('Attendee Name') ?></span></h3>
<div class="inside">
<input type='text' name='attendee_name' value='<?php echo $attendee->name; ?>' />
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Bio') ?></span></h3>
<div class="inside">
<?php the_editor($attendee->description); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('URL') ?></span></h3>
<div class="inside">
<input type='text' name='url' value='<?php echo $attendee->url; ?>' />
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Email') ?></span></h3>
<div class="inside">
<input type='text' name='email' value='<?php echo $attendee->email; ?>' />
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Phone') ?></span></h3>
<div class="inside">
<input type='text' name='phone' value='<?php echo $attendee->phone; ?>' />
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Moderation Status') ?></span></h3>
<div class="inside">
<label for="status"><?php e('Active') ?></label> <input type="checkbox" name="status" value="1" id="status" <?php if($attendee->status or $action=='new') print " checked='checked'"; ?> />
</div></div>

</div>
</div>

<p class="submit">
<?php wp_nonce_field('attendeembr_create_edit_attendee'); ?>
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="attendee" value="<?php echo $_REQUEST['attendee']; ?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save') ?>" style="font-weight: bold;" tabindex="4" />
</p>

</div>
</form>

<a href="edit.php?page=eventr/attendees.php&amp;event=<?php echo $_REQUEST['event']; ?>">Back to listing page</a>

</div>
