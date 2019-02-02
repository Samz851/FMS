<?php defined('ABSPATH') or die("Access denied.") ?>
<?php

	// **********************************************************************************************************************************
	// 		Add raw data display metabox
	// **********************************************************************************************************************************
	
	function fms_add_raw_data_metabox() {
		
		add_meta_box("fms-raw-data-metabox", "Raw Data", "fms_raw_data_metabox", "fms_listing", "normal", "low");
	}
	add_action("add_meta_boxes", "fms_add_raw_data_metabox");


	// **********************************************************************************************************************************
	// 		TEST: Generate metabox to show raw listing data
	// **********************************************************************************************************************************
	
	function fms_raw_data_metabox($post, $metabox) {
	
		echo "<pre>"; 
		print_r(get_post_meta($post->ID, FMS_META_PRE."openhouse_group", true)); 
		echo "</pre>";
	
		
		echo "<pre>"; 
		print_r($post); 
		echo "</pre>";
		
		echo "<pre>"; 
		print_r(get_post_meta($post->ID)); 
		echo "</pre>";
	}
	
?>