<?php
require_once('wpframe.php');
global $wpdb;
$GLOBALS['wpframe_plugin_name'] = basename(dirname(__FILE__));
$GLOBALS['wpframe_plugin_folder'] = $GLOBALS['wpframe_home'] . '/wp-content/plugins/' . $GLOBALS['wpframe_plugin_name'];

$current_user = wp_get_current_user();

////////////////////////////////////////////////////// Library Functions ///////////////////////////////////////
// I put all the functions at the top so that the 'if(function_exists())' will work. If its at the bottom, the functions inside the blocks will be inacessable.
if(!function_exists('upload')) {
/**
 * A function for easily uploading files. This function will automatically generate a new 
 *        file name so that files are not overwritten.
 * Arguments:     $file_id - The name of the input field contianing the file.
 *                $folder  - The folder to which the file should be uploaded to - it must be writable. OPTIONAL
 *                $types   - A list of comma(,) seperated extensions that can be uploaded. If it is empty, anything goes OPTIONAL
 * Returns  : This is somewhat complicated - this function returns an array with two values...
 *                The first element is randomly generated filename to which the file was uploaded to.
 *                The second element is the status - if the upload failed, it will be 'Error : Cannot upload the file 'name.txt'.' or something like that
 */
function upload($file_id, $folder="", $types="") {
	if(!$_FILES[$file_id]['name']) return array('','No file specified');

	$file_title = $_FILES[$file_id]['name'];
    //Get file extension
	$ext_arr = split("\.",basename($file_title));
    $ext = strtolower($ext_arr[count($ext_arr)-1]); //Get the last extension

    //Not really uniqe - but for all practical reasons, it is
    $uniqer = substr(md5(uniqid(rand(),1)),0,5);
    $file_name = $uniqer . '_' . $file_title;//Get Unique Name

    $all_types = explode(",",strtolower($types));
    if($types) {
    	if(!in_array($ext,$all_types)) {
            $result = "'".$_FILES[$file_id]['name']."' is not a valid file."; //Show error if any.
            return array('',$result);
        }
    }

    //Where the file must be uploaded to
    if($folder) $folder .= '/';//Add a '/' at the end of the folder
    $uploadfile = $folder . $file_name;

    $result = '';
    //Move the file from the stored location to the new location
    if (!move_uploaded_file($_FILES[$file_id]['tmp_name'], $uploadfile)) {
        $result = "Cannot upload the file '".$_FILES[$file_id]['name']."'"; //Show error if any.
        if(!file_exists($folder)) {
        	$result .= " : Folder don't exist.";
        } elseif(!is_writable($folder)) {
        	$result .= " : Folder not writable.";
        } elseif(!is_writable($uploadfile)) {
        	$result .= " : File not writable.";
        }
        $file_name = '';
        
    } else {
        if(!$_FILES[$file_id]['size']) { //Check if the file is made
            @unlink($uploadfile);//Delete the Empty file
            $file_name = '';
            $result = "Empty file found - please use a valid file."; //Show the error message
        } else {
            chmod($uploadfile,0777);//Make it universally writable.
        }
    }

    return array($file_name,$result);
}
}

if(!function_exists('sanitizeInput')) {
//To remove possible evil scripts - XSS etc.
	function sanitizeInput($text) {
		$allowed_tags = 'a|br|em|strong|i|b';
		$text = preg_replace("/<(\/?)($allowed_tags)([^>]*?)>/i", "&lesser;$1$2$3&greater;", $text);
	$text = strip_tags($text); //Remove all tags
	$cleaned = preg_replace("/&lesser;(\/?)($allowed_tags)( [^>]*?)?&greater;/i", "<$1$2$3>", $text);
	$cleaned = preg_replace('/<a [^>]*href=([^>]+)[^>]*>/i',"<a href=$1 rel=\"nofollow\">",$cleaned);
	
	str_replace(
		array('&'),//< and > are already covered in sanitizeInput()
		array('&amp;'),
		$text); //Convert many text reference to their HTML eqvalents.

	//Get an url and links it automatically - if it does not have a =,' or " char at the beginning. This is to prevent already linked HTML to be linked again
	$text = preg_replace(
		'/([^\'\"\=\/]|^)(http:\/\/|(www.))([\w\.\-\/\\\=\?\%\+\&]+?)([\.\?])?(\s|$)/',
		"$1<a rel='nofollow' href='http://$3$4'>$3$4</a>$5$6",
		$text);
	//That regexp will match all the following URLS
	// http://www.google.com/
	// www.google.com
	// www.google.com?option=some&other=thing
	//It is clever enough to not match
	// <a href="http://www.google.com/">
	// In case of 'www.google.com.' it will only match 'www.google.com' - the ending . is ignored

	$text=nl2br($text);
	
	return $cleaned;
}
}

if(!function_exists('joinPath')) {
/**
 * Takes one or more file names and combines them, using the correct path separator for the 
 * 		current platform and then return the result.
 * Arguments: The parts that make the final path.
 * Example: joinPath('/var','www/html/','try.php'); // returns '/var/www/html/try.php'
 */
function joinPath() {
	$path = '';
	$arguments = func_get_args();
	$args = array();
	foreach($arguments as $a) if($a) $args[] = $a;//Removes the empty elements
	
	$arg_count = count($args);
	$path_count = 0;
	for($i=0; $i<$arg_count; $i++) {
		$folder = $args[$i];
		if(isset($args[$i+1]) and strpos($args[$i+1], $folder) !== false) continue; //If the current folder is a part of the next folder.
		
		if($path_count != 0 and $folder[0] == DIRECTORY_SEPARATOR) $folder = substr($folder,1); //Remove the first char if it is a '/' - and its not in the first argument
		if($i != $arg_count-1 and substr($folder,-1) == DIRECTORY_SEPARATOR) $folder = substr($folder,0,-1); //Remove the last char - if its not in the last argument
		
		$path .= $folder;
		if($i != $arg_count-1) $path .= DIRECTORY_SEPARATOR; //Add the '/' if its not the last element.
		$path_count++;
	}
	return $path;
}
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$event = $wpdb->get_row($wpdb->prepare("SELECT ID,name,description,event_date, maximum_attendees,landing_page,status FROM {$wpdb->prefix}eventr_event WHERE ID=%d", $event_id));

// Cache the options.
$options = array();
$plugin_options = array('use_captcha','moderation', 'bio_show', 'bio_mandatory', 'url_show', 'url_mandatory', 'email_show', 'email_mandatory', 'phone_show', 'phone_mandatory', 'image_show', 'image_mandatory');
foreach($plugin_options as $opt) {
	$options[$opt] = get_option('eventr_' . $opt);
}

$valid_event = true;

if(!$event) e("Invalid Event ID given");
elseif($event->event_date != '0000-00-00' and $event->event_date < date('Y-m-d')) { // Event date is in the past - event is over - don't allow registrations
e("Te laat, het evenement is al geweest!");
$valid_event = false;

} elseif($event->status == 0) { // Event is inactive
	e("Registratie voor dit evenement is gesloten.");
	$valid_event = false;
	
} elseif($event->maximum_attendees) { // Event we have reached the max number attendees for this event.
	$current_attendee_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}eventr_event_attendee WHERE event_ID=%d", $event_id));
	if($event->maximum_attendees <= $current_attendee_count) {
		e("We zitten vol! Registratie is gesloten.");
		$valid_event = false;
	}
}

if($event and $valid_event) {
	$errors = array();
if(isset($_POST['action']) and $_POST['action'] and $_POST['event_id'] == $event->ID) { // Submitted Registration
	$attendee_id = 0;

	// ReCaptcha Code
	if($options['use_captcha']) {
		require_once('recaptchalib.php');
		$privatekey = "6LegSwQAAAAAAJFFvsZ_84gXcx_OFn_nK_N9-FNT";
		$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
	}

	if(!$_POST['attendee_id']) { // New attendee
		// Data validation...
		if (!$resp->is_valid and $options['use_captcha']) { //Captcha Error.
			$errors[] ="The anti spam key you have entered is incorrect...";
		}
		if(!$_POST['attendee_name']) $errors[] = 'Please provide a name';
		if($_POST['email'] and !preg_match('/^[\w\-\.]+\@[\w\-\.]+\.[a-z\.]{2,5}$/', $_POST['email']))  $errors[] = 'Please provide a valid email address';
		
		$image_file = '';
		if($options['image_show']) {
			if($_FILES['picture']['name']) {
				$upload_path = get_option('upload_path');
				$upload_folder = joinPath(ABSPATH, $upload_path, 'eventr/');
				
				if(strpos($upload_path, ABSPATH) !== false) { //If the full path of the upload folder is there, remove it.
					$upload_path_small = str_replace(ABSPATH, '', $upload_path);
					$image_folder = joinPath($GLOBALS['wpframe_home'], $upload_path_small, 'eventr/');
				} else {
					$image_folder = joinPath($GLOBALS['wpframe_home'], $upload_path, 'eventr/');
				}
				
				list($image, $result) = upload('picture', $upload_folder, "jpg,jpeg,png,gif,bmp"); //Upload Image
				$image_file = joinPath($image_folder, $image);
				
				if(!$image) $errors[] = 'Image upload failed: ' . $result; //Upload Failed
				else { //Valid image - try to resize it.
					if(function_exists('imagecreatefrompng')) {
						require(joinPath(WP_PLUGIN_DIR, 'eventr/includes/Image.php'));
						$image_file_absolute = joinPath($upload_folder, $image);
						$img = new Image($image_file_absolute);
						$img->resize(0, 200, false);
						$new_file = preg_replace('/\.(\w{2,5})$/', "_small.$1", $image_file_absolute);
						$img->save($new_file);
						
						if(file_exists($new_file)) {
							$image_file = preg_replace('/\.(\w{2,5})$/', "_small.$1", $image_file);
						}
					}
				}
			} elseif($options['image_mandatory']) {
				$errors[] = t('Please upload your image');
			}
		}
		
		$url = '';
		$phone = '';
		$email = '';
		$description = '';
		
		if($options['url_show'] and $_POST['site_url'] != 'http://') $url = strip_tags($_POST['site_url']);
		if($options['phone_show'])	$phone = strip_tags($_POST['phone']);
		if($options['email_show'])	$email = strip_tags($_POST['email']);
		if($options['bio_show'])	$description = sanitizeInput($_POST['description']);

		// Custom Validations
		if($options['url_show']		and $options['url_mandatory']	and !$url) 
			$errors[] = t('Please provide the URL of your site');
		if($options['phone_show']	and $options['phone_mandatory']	and !$phone) 
			$errors[] = t('Please provide your phone number');
		if($options['email_show']	and $options['email_mandatory']	and !$email) 
			$errors[] = t('Please provide your email address');
		if($options['bio_show']		and $options['bio_mandatory']	and !$description) 
			$errors[] = t('Please provide a brief description of yourself in the Bio field.');
		
		$status = ($options['moderation']) ? 0 : 1; // If moderation is on, give disabled status to all.
		
		if(!count($errors)) { // No errors - insert the thingy
			//Insert the guy.
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}eventr_attendee(name, description, url, email, phone, picture, status) "
				. " VALUES(%s, %s, %s, %s, %s, '$image_file', '$status')", strip_tags($_POST['attendee_name']), $description, $url, $email, $phone));
			$attendee_id = $wpdb->insert_id;
		}
	} else { //This attendee has attended some other function before this.
		$attendee_id = $_POST['attendee_id'];
	}
	
	if($attendee_id) {
		// First, check to see if this guy has already registered.
		if($wpdb->get_row($wpdb->prepare("SELECT added_on FROM {$wpdb->prefix}eventr_event_attendee WHERE event_ID=%d AND attendee_ID=%d", $_POST['event_id'], $attendee_id))) {
			printf(t("Je staat al op de lijst voor %s"), stripslashes($event->name));
			
		} else {
			// Now, add the guy and show a success message.
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}eventr_event_attendee(event_ID, attendee_ID, added_on) VALUES(%d, %d, NOW())", $_POST['event_id'], $attendee_id));
			
			if($event->landing_page) {
				wp_redirect($event->landing_page);
				print "<script type='text/javascript'>location.href='{$event->landing_page}';</script>";
				exit;
			}
			
			printf(t("'%s' staat op de lijst voor %s."), $_POST['attendee_name'], stripslashes($event->name));
		}
		
	} else { // Some error has happend - show the message.
		print "<div class='error-message'>" . t("Registeration failed...") . "<br />" . implode('<br />', $errors) . '</div><br />';
	}
}

if(!$_POST['action'] or $errors) { // Show The From.

	if(!isset($GLOBALS['eventr_client_includes_loaded'])) {
		?>
		<link type="text/css" rel="stylesheet" href="<?=$GLOBALS['wpframe_plugin_folder']?>/registration.css" />
		<script type="text/javascript" src="<?=$GLOBALS['wpframe_home']?>/wp-includes/js/jquery/jquery.js"></script>
		<script type="text/javascript" src="<?=$GLOBALS['wpframe_plugin_folder']?>/script.js"></script>

		<?php
	$GLOBALS['eventr_client_includes_loaded'] = true; // Make sure that this code is not loaded more than once.
}

?>

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Inschrijven voor <?=$event->name?></h3>
	</div>
	<div class="panel-body">
		<?php if(is_user_logged_in()) { ?>
		<form role="form" action="" method="post" class="event-registration-form form-horizontal" id="event-<?=$event_id?>" enctype="multipart/form-data">
			<div id="profile-details">
				<div class="form-group">
					<label for="attendee_name" class="col-sm-2 control-label">Naam:</label><input class="form-control" type="text" name="attendee_name" id="attendee_name" value="<?=$_POST['attendee_name'] . $current_user->user_login?>" />
				</div>

				<?php if($options['bio_show']) { ?>
				<div class="form-group">
					<label for="description" class="col-sm-2 control-label">Opmerking:</label>
					<textarea name="description" id="description" class="form-control" rows="1"><?=$_POST['description']?></textarea>
				</div>
				<?php } ?>

				<?php if($options['email_show']) { ?>
				<div class="form-group">
					<label for="email"><?php e('Email'); if($options['email_mandatory']) e(''); ?></label>
					<input type="text" name="email" id="email" class="form-control" value="<?=$_POST['email'] . $current_user->user_email?>"/>
				</div>
				<?php } ?>
			</div>
			<input type="submit" name="action" id="action-button" class="btn btn-default" value="<?php e('Inschrijven!') ?>"  />
			<input type="hidden" name="event_id" value="<?=$event_id?>" />
			<input type="hidden" id="attendee_id" name="attendee_id" value="0" />
			<input type="hidden" name="site_home" id="site_home" value="<?=$GLOBALS['wpframe_home']?>" />
			
		</form>
		<?php } ?>

	</div>
</div>

<?php }
}
