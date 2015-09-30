<?php
/*
Plugin Name: Eventr
Plugin URI: http://www.bin-co.com/tools/wordpress/plugins/eventr/
Description: Eventr Plugin lets you create events and provides your visitors with a form that lets them register for the event. There is also an option to show the entire attendee list to blog vistors as well.
Version: 2.02.1
Author: Binny V A, modified by S Kamoen - htmELS
Author URI: http://www.binnyva.com/
*/

/**
 * Add a new menu item under 'Tools'
 */
add_action( 'admin_menu', 'eventr_add_menu_links' );
function eventr_add_menu_links() {
	global $wp_version;
	$view_level= 2;
	$page = 'edit.php';
	if($wp_version >= '2.7') $page = 'tools.php';
	
	add_submenu_page($page, __('Manage Events', 'eventr'), __('Manage Events', 'eventr'), $view_level, 'eventr/events.php');
	global $_registered_pages;
	$code_pages = array('attendee_form.php','attendees.php', 'event_form.php', 'export.php', 'export_choose.php', 'event_action.php');
	foreach($code_pages as $code_page) {
		$hookname = get_plugin_page_hookname("eventr/$code_page", '' );
		$_registered_pages[$hookname] = true;
	}
}

/// Initialize this plugin. Called by 'init' hook.
add_action('init', 'eventr_init');
function eventr_init() {
	load_plugin_textdomain($GLOBALS['wpframe_plugin_name'], 'wp-content/plugins/eventr/lang' );
}

/// Add an option page for Eventr.
add_action('admin_menu', 'eventr_option_page');
function eventr_option_page() {
	add_options_page(__('Eventr Settings', 'eventr'), __('Eventr Settings', 'eventr'), 8, basename(__FILE__), 'eventr_options');
}
function eventr_options() {
	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) die(t('Cheatin&#8217; uh?'));
	if (! user_can_access_admin_page()) wp_die( __('You do not have sufficient permissions to access this page.', 'eventr') );

	require(ABSPATH. '/wp-content/plugins/eventr/options.php');
}


/**
 * This will scan all the content pages that wordpress outputs for our special code. If the code is found, it will replace the requested quiz.
 */
add_shortcode( 'EVENTR', 'eventr_shortcode' );
function eventr_shortcode( $attr ) {
	$action = $attr[0];
	$event_id = $attr[1];
	if(!is_numeric($event_id)) return '';
	
	$contents = '';
	ob_start();
	if($action == 'REGISTRATION') {
		include(ABSPATH . 'wp-content/plugins/eventr/show_registeration_form.php');
		
	} elseif($action == 'ATTENDEES') {
		include(ABSPATH . 'wp-content/plugins/eventr/show_attendee_list.php');
	}
	$contents = ob_get_contents();
	ob_end_clean();
	
	return $contents;
}

/**
 * Creates tables and upload folder on activation.
 */
register_activation_hook(__FILE__,'eventr_activate');
function eventr_activate() {
	global $wpdb;
	
	// Initial options.
	add_option('eventr_use_captcha', 1);
	add_option('eventr_moderation', 0);
	add_option('eventr_bio_show', 1);
	add_option('eventr_url_show', 1);
	add_option('eventr_email_show', 1);
	add_option('eventr_phone_show', 1);
	add_option('eventr_image_show', 1);
	add_option('eventr_bio_mandatory', 0);
	add_option('eventr_url_mandatory', 0);
	add_option('eventr_email_mandatory', 0);
	add_option('eventr_phone_mandatory', 0);
	add_option('eventr_image_mandatory', 0);
	add_option('eventr_bio_list', 1);
	add_option('eventr_url_list', 1);
	add_option('eventr_email_list', 0);
	add_option('eventr_phone_list', 0);
	add_option('eventr_image_list', 1);
	
	//Create the folder to which the images will be uploaded to...
	$upload_path = get_option('upload_path');
	if(strpos($upload_path, ABSPATH) === false) { // The home is NOT in the upload path.
		$upload_path = ABSPATH . $upload_path;
	}
	
	$old_umask = umask(0); // Or the folder will not get write permission for everybody.
	if ( ! file_exists($upload_path) && ! mkdir($upload_path, 0777) ) {
		print '<div id="message" class="updated error"><p>';
		printf(__("Cannot create the uploads folder '%s'. Please create it using FTP and give it 777 permission", 'eventr'), $upload_path);
		print '</p></div>';
	}

	$images_folder = $upload_path . '/eventr/';
	if ( ! file_exists($images_folder) && ! mkdir($images_folder, 0777) ) {
		print '<div id="message" class="updated error"><p>';
		printf(__("Cannot create the uploads folder '%s'. Please create it using FTP and give it 777 permission", 'eventr'), $images_folder);
		print '</p></div>';
	}
	umask($old_umask);
	
	$database_version = '4';
	$installed_db = get_option('eventr_db_version');
	
	if($database_version != $installed_db) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		// Create the table structure
		$sql = "CREATE TABLE {$wpdb->prefix}eventr_attendee (
					ID int(11) unsigned NOT NULL auto_increment,
					name varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					description mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					url varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					email varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					phone varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					picture varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					status enum('1','0') NOT NULL default '1',
					extra MEDIUMTEXT NOT NULL,
					PRIMARY KEY  (ID)
				);
				CREATE TABLE {$wpdb->prefix}eventr_event (
					ID int(11) unsigned NOT NULL auto_increment,
					name varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					description mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					event_date date NOT NULL,
					maximum_attendees int(5) NOT NULL default '0',
					landing_page varchar(200) NOT NULL,
					added_on datetime NOT NULL,
					status enum('1','0') NOT NULL default '1',
					extra MEDIUMTEXT NOT NULL,
					PRIMARY KEY  (ID)
				);
				CREATE TABLE {$wpdb->prefix}eventr_event_attendee (
					event_ID int(11) unsigned NOT NULL,
					attendee_ID int(11) unsigned NOT NULL,
					added_on datetime NOT NULL,
					guests int(2) NOT NULL default '0',
					extra MEDIUMTEXT NOT NULL,
					KEY event_ID (event_ID,attendee_ID)
				);";
		dbDelta($sql);
		update_option( "eventr_db_version", $database_version );
	}
}
