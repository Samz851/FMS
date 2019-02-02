<?php defined("ABSPATH") or die("Access denied.") ?>
<?php

	/*
	Plugin Name: 	Listing Retriever By Firstlook
	Plugin URI:  	http://myfirstlook.ca/retriever-plugin
	Description: 	CREA DDF®, Firstlook Magic. Canadian MLS® listing automation made easy.
	Version:     	1.1.0
	Author:      	Firstlook Media Solutions INC.
	Author URI: 	http://myfirstlook.ca
	License:     	GPLv2 or later
	License URI: 	http://www.gnu.org/licenses/gpl-2.0.html
	Domain Path:	
	Text Domain: 	firstlook
	*/
	
	
	// **********************************************************************************************************************************
	// 		Constant definitiions
	// **********************************************************************************************************************************
	
	ini_set("max_execution_time", 300);
	ini_set("memory_limit", "500M");
	
	define ("FMS_DIR", dirname(__FILE__));
	define ("FMS_MAIN_FILE", __FILE__);
	define ("FMS_META_PRE", "_fms_");
	
	
	// **********************************************************************************************************************************
	// 		Library
	// **********************************************************************************************************************************
	
	require ("lib/cmb2/init.php");	// Custom metabox generation
	
	
	// **********************************************************************************************************************************
	// 		Utility Classes
	// **********************************************************************************************************************************
	
	require ("inc/fms-listing-statuses.php");
	
	
	// **********************************************************************************************************************************
	// 		Plugin Options
	// **********************************************************************************************************************************
	
	require ("inc/fms-accessor-options.php");
	
	
	// **********************************************************************************************************************************
	// 		Inclusions
	// **********************************************************************************************************************************
	
	require ("inc/fms-register-post-types.php");	
	require ("inc/fms-register-editor-meta.php");
	require ("inc/fms-auto-sync-scheduler.php");
	
	
	// **********************************************************************************************************************************
	// 		Installed Accessors
	// **********************************************************************************************************************************
	
	require ("inc/fms-accessor.php");
	foreach (glob(FMS_DIR ."/accessors/*/*.php") as $accessor_class) require_once($accessor_class);
	

	// **********************************************************************************************************************************
	// 		API Stuff
	// **********************************************************************************************************************************
	
	require ("inc/fms-listing-setup.php");
	require ("inc/fms-accessor-api-functions.php");
	require ("inc/fms-shortcodes.php");
	
	
	// **********************************************************************************************************************************
	// 		Listing SEO and Social
	// **********************************************************************************************************************************
	
	require ("inc/fms-seo-support.php");
	

	// **********************************************************************************************************************************
	// 		Initialization
	// **********************************************************************************************************************************
	
	function fms_initialize_retriever() {
		
		// 
		define("FMS_URL", plugin_dir_url(__FILE__));

		//
		$uploads 	= wp_upload_dir();
		$home		= get_home_url();
	
		if (!strstr($uploads["baseurl"], $home)) {
			
			$uploads["baseurl"] = $home . $uploads["baseurl"];
		}
	
		define ("FMS_PHOTO_DIR", $uploads["basedir"]."/fms-photos");
		define ("FMS_PHOTO_URL", $uploads["baseurl"]."/fms-photos");

		// Start a session to hold DDF Search data
		session_start();
		
		// Make sure our photo directory exists!
		if (!is_dir(FMS_PHOTO_DIR)) wp_mkdir_p(FMS_PHOTO_DIR);
	}
	add_action("init", "fms_initialize_retriever");
	
	
	// **********************************************************************************************************************************
	// 		Scripts and styles (admin)
	// **********************************************************************************************************************************
	
	function fms_enqueue_retirever_admin_scripts() {

		// jQuery stuff
		wp_enqueue_script("jquery");
		wp_enqueue_script("jquery-ui-core");
		wp_enqueue_script("jquery-effects-core");
		//wp_enqueue_script("jquery-ui", "//code.jquery.com/ui/1.12.0-rc.1/jquery-ui.min.js", array(), "1.12.0", false);
		
		// Make sure we have access to the media library
		wp_enqueue_media();
		
		// Styles for the admin interface
		wp_enqueue_style("fontawesome-icons", "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css", array(), "4.5.0", "screen");
		wp_enqueue_style("fms-admin-ui-css", FMS_URL."admin-ui/css/fms-admin-ui.css", array(), "1.0", "screen");		
	}
	add_action("admin_enqueue_scripts", "fms_enqueue_retirever_admin_scripts");
	
	
	// **********************************************************************************************************************************
	// 		Scripts and styles (front-end)
	// **********************************************************************************************************************************
	
	function fms_enqueue_retriever_public_scripts() {
		
		// jQuery stuff
		wp_enqueue_script("jquery");
		wp_enqueue_script("jquery-ui-core");
		wp_enqueue_script("jquery-effects-core");
		
		// Styles for the public interface
		wp_enqueue_style("fontawesome-icons", "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css", array(), "4.5.0", "screen");
		wp_enqueue_style("fms-font-icons", FMS_URL."lib/fonts/firstlook-regular/stylesheet.css", array(), "1.0", "screen");
		wp_enqueue_style("fms-public-ui-css", FMS_URL."public-ui/css/fms-public-ui.css", array(), "1.1", "screen");
	}
	add_action("wp_enqueue_scripts", "fms_enqueue_retriever_public_scripts");
	
	
	// **********************************************************************************************************************************
	// 		Widgets
	// **********************************************************************************************************************************

	function fms_register_retriever_widgets() {
		
		include ("widgets/fms-widget-national-search.php");
		
		register_widget("fms_widget_national_search");
	}
	add_action("widgets_init", "fms_register_retriever_widgets");

?>