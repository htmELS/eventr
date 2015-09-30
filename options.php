<?php
include('wpframe.php');

if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {
	$fields = array('bio', 'url', 'email', 'phone', 'image');
	$options = array('use_captcha', 'moderation');
	foreach($fields as $f) {
		$options[] = $f.'_show';
		$options[] = $f.'_mandatory';
		$options[] = $f.'_list';
	}
	
	foreach($options as $opt) {
		if(isset($_POST[$opt])) update_option('eventr_' . $opt, 1);
		else update_option('eventr_' . $opt, 0);
	}
	wpframe_message("Options updated");
}
?>
<div class="wrap" id="poststuff">
<h2><?php e("Eventr Settings") ?></h2>

<form action="" method="post">

<div class="postbox">
<h3 class="hndle"><span><?php e('Settings') ?></span></h3>
<div class="inside">
<?php showOption('use_captcha', 'Use Captcha'); ?>
<?php showOption('moderation', 'Enable Moderation'); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Fields') ?></span></h3>
<div class="inside">

<div class="postbox">
<h3 class="hndle"><span><?php e('Bio/Description') ?></span></h3>
<div class="inside">
<?php showOption('bio_show', 'Show Bio Field'); ?>
<?php showOption('bio_mandatory', 'Bio must be entered(An error will be show if this is left empty)'); ?>
<?php showOption('bio_list', 'List Bio field in the list in the attendees page'); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Phone Number') ?></span></h3>
<div class="inside">
<?php showOption('phone_show', 'Show Phone Field'); ?>
<?php showOption('phone_mandatory', 'Phone Number must be entered'); ?>
<?php showOption('phone_list', 'List phone field in the list in the attendees page'); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Email') ?></span></h3>
<div class="inside">
<?php showOption('email_show', 'Show Email Field'); ?>
<?php showOption('email_mandatory', 'Email must be entered'); ?>
<?php showOption('email_list', 'List  field in the list in the attendees page'); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Site URL') ?></span></h3>
<div class="inside">
<?php showOption('url_show', 'Show the Website URL Field'); ?>
<?php showOption('url_mandatory', 'A URL must be entered'); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Image Upload') ?></span></h3>
<div class="inside">
<?php showOption('image_show', 'Show the Image Upload Field'); ?>
<?php showOption('image_mandatory', 'Make sure that the user uploads an image'); ?>
<?php showOption('image_list', 'List image field in the list in the attendees page'); ?>
</div></div>

</div></div>

<p class="submit">
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save Options') ?>" style="font-weight: bold;" />
</p>

</form>

</div>

<?php
function showOption($option, $title) {
?>
<input type="checkbox" name="<?=$option?>" value="1" id="<?=$option?>" <?php if(get_option('eventr_'.$option)) print " checked='checked'"; ?> />
<label for="<?=$option?>"><?php e($title) ?></label><br />

<?php
}
