<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

$event = array();
if($action == 'edit') {
	$event = $wpdb->get_row("SELECT name,description,event_date,maximum_attendees,landing_page,status FROM {$wpdb->prefix}eventr_event WHERE ID = $_REQUEST[event]");
}

?>

<div class="wrap">
<h2><?php printf(t("%s Event"), ucfirst($action)); ?></h2>

<?php if($action == 'edit') { ?>
<p><?php printf(t('To add the registeration form to your blog, insert the code [EVENTR REGISTRATION %d] into any post.'), $_REQUEST['event']) ?></p>
<?php } ?>

<input type="hidden" id="title" name="ignore_me" value="This is here as a workaround for an editor bug" />

<?php
wpframe_add_editor_js();
?>
<script type="text/javascript" src="<?=$wpframe_plugin_folder?>/js/admin/event_form.js"></script>

<form name="post" action="<?=$wpframe_plugin_folder?>/event_action.php" method="post" id="post">
<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<div class="postbox">
<h3 class="hndle"><span><?php e('Event Name') ?></span></h3>
<div class="inside">
<input type='text' name='name' value='<?php echo $event->name; ?>' />
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Description') ?></span></h3>
<div class="inside">
<?php the_editor($event->description); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Event Date') ?></span></h3>
<div class="inside">
<input type='text' name='event_date' value='<?php echo $event->event_date; ?>' /><br />
<?php e('Use YYYY-MM-DD format. This is an optional field - if you set a date, the event will not accept further registerations after the date.') ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Maximum Number of Attendees') ?></span></h3>
<div class="inside">
<input type='text' name='maximum_attendees' value='<?php echo ($event->maximum_attendees == NULL) ? 0 : $event->maximum_attendees ?>' /><br />
<?php e('Set this as 0 to allow unlimited registerations.') ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Landing page') ?></span></h3>
<div class="inside">
<input type='text' name='landing_page' value='<?php echo $event->landing_page ?>' /><br />
<?php e('Users will be redirected to this page when the registeration is done') ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Status') ?></span></h3>
<div class="inside">
<label for="status"><?php e('Active') ?></label> <input type="checkbox" name="status" value="1" id="status" <?php if($event->status or $action=='new') print " checked='checked'"; ?> />
</div></div>

<?php
/*
<div class="postbox">
<div class="handlediv" title="Click to toggle"></div>
<h3 class="hndle"><span><?php e('Additional Fields') ?></span></h3>
<div class="inside">

<div id="field_details_1">
<label for="extra_1_title">Title</label>
<input type="text" name="extra_1_title" id="extra_1_title" value="" /><br />

<label for="extra_1_type">Type</label>
<select name="extra_1_type" id="extra_1_type">
<option value="text">Text</option>
<option value="checkbox">Checkbox</option>
<option value="select">Select</option>
</select><br />

<div id="extra_1_select_option" style="display:none;">
<label for="extra_1_select_option">Select Options(one per line)...</label><br />
<textarea name="extra_1_select_option" id="extra_1_select_option" rows="5" cols="20"></textarea><br />
</div>

<label for="extra_1_is_required">Required?</label>
<input type="checkbox" name="extra_1_is_required" id="extra_1_is_required" value="1" /><br />
<hr />
</div>

<div id="extra_field_area">
</div>

<input id="add_field" type="button" value="Add New Field" />

</div></div>
*/
?>

</div>

<p class="submit">
<?php wp_nonce_field('eventr_create_edit_event'); ?>
<input type="hidden" name="action" value="<?php echo $action; ?>" />
<input type="hidden" name="event" value="<?php echo $_REQUEST['event']; ?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save') ?>" style="font-weight: bold;" tabindex="4" />
</p>

</div>
</form>

</div>
