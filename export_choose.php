<?php
include('wpframe.php');
?>
<div class="wrap">
<h2><?php e("Export Data") ?></h2>

<form action="<?php echo $GLOBALS['wpframe_plugin_folder'] ?>/export.php" method="post">
<p><?php e("Select the fields you want to export...") ?></p>

<?php
showOption('id', 'ID');
showOption('name', 'Name');
showOption('url', 'Site URL');
showOption('email', 'Email');
showOption('phone', 'Phone');
?>

<p class="submit">
<input type="hidden" id="event" name="event" value="<?php echo (int) $_REQUEST['event'] ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Export') ?>" style="font-weight: bold;" />
</p>

</form>

</div>

<?php
function showOption($option, $title) {
?>
<input type="checkbox" name="fields[]" value="<?php echo $option?>" id="<?php echo $option ?>" checked="checked" />
<label for="<?=$option?>"><?php echo($title) ?></label><br />

<?php
}