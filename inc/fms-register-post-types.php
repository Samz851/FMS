<?php defined("ABSPATH") or die("Access denied.") ?>
<?php
	
	// **********************************************************************************************************************************
	// 		Register the post listing types and taxonomies
	// **********************************************************************************************************************************
	
	function fms_accessor_register_post_types() {
		
		// CMB2 is not available during init so we have to be a little clever here.
		// This is definitely not ideal, but necessary until we can use fms_get_option (cmb2_get_option) in the init phase.
		
		$options = get_option("fms_accessor_options", array()); 
		
		$archive_slug 	= (!empty($options["listing_archive_slug"]) 	? $options["listing_archive_slug"] 	: "listings");
		$categoty_slug	= (!empty($options["listing_category_slug"]) 	? $options["listing_category_slug"] : "listing-category");
		
		// Wordpress labels for the listing post objects
		$labels = array(
			
			"name"						=> __( "Listings", 						"firstlook" ),
			"singular_name"				=> __( "Listing", 						"firstlook" ),
			"add_new"					=> __( "Add New Listing", 				"firstlook" ),
			"add_new_item"				=> __( "Add New Listing", 				"firstlook" ),
			"edit_item"					=> __( "Edit Listing", 					"firstlook" ),
			"new_item"					=> __( "New Listing", 					"firstlook" ),
			"view_item"					=> __( "View Listing", 					"firstlook" ),
			"search_items"				=> __( "Search Listings", 				"firstlook" ),
			"not_found"					=> __( "No listings found", 			"firstlook" ),
			"not_found_in_trash"		=> __( "No listings found in trash", 	"firstlook" ),
			"parent_item_colon"			=> __( "Parent Listing:", 				"firstlook" ),
			"all_items"					=> __( "All Listings", 					"firstlook" ),
			"archives"					=> __( "Listings Archive", 				"firstlook" ),
			"insert_into_item"			=> __( "Insert into listing", 			"firstlook" ),
			"uploaded_to_this_item" 	=> __( "Uploaded to this listing", 		"firstlook" ),
			"featured_image" 			=> __( "Featured Image", 				"firstlook" ),
			"set_featured_image"		=> __( "Set featured image", 			"firstlook" ),
			"remove_featured_image"		=> __( "Remove featured image", 		"firstlook" ),
			"use_featured_image"		=> __( "Use featured image", 			"firstlook" ),
			"menu_name"					=> __( "Listings", 						"firstlook" ),
			"filter_items_list" 		=> __( "Listings", 						"firstlook" ),
			"items_list_navigation" 	=> __( "Listings", 						"firstlook" ),
			"items_list" 				=> __( "Listings", 						"firstlook" ),
			"name_admin_bar"			=> __( "Listing", 						"firstlook" ),
		);
		
		// Parameters for the listing post type objects
		$args = array(
			
			"labels"             	=> $labels,
			"capability_type"    	=> "post",
			
			"public"             	=> true,
			"publicly_queryable" 	=> true,
			"show_ui"            	=> true,
			"show_in_menu"       	=> true,
			"query_var"          	=> true,
			"has_archive"        	=> true,
			"hierarchical"       	=> false,
			"menu_position"      	=> null,
			
			"rewrite"            	=> array("slug" => $archive_slug, "with_front" => false),
			"supports" 				=> array("title", "editor", "thumbnail"),
			
			"menu_icon"				=> "", // This gets set in CSS
		);
		
		// Register the listing post type
		register_post_type("fms_listing", $args);
		
		// Wordpress labels for the listing category taxonomy
		$labels = array(
			
			"name"							=> __( "Listing Categories", 					"firstlook" ),
			"singular_name"					=> __( "Listing Category", 						"firstlook" ),
			"menu_name"						=> __( "Listing Categories", 					"firstlook" ),
			"all_items"						=> __( "All Listing Categories", 				"firstlook" ), 
			"edit_item"						=> __( "Edit Listing Category", 				"firstlook" ),
			"view_item"						=> __( "View Listing Category", 				"firstlook" ), 
			"update_item" 					=> __( "Update Listing Category", 				"firstlook" ),
			"add_new_item"					=> __( "Add New Listing Category", 				"firstlook" ), 
			"new_item_name"					=> __( "New Listing Category Name", 			"firstlook" ),
			"parent_item"					=> __( "Parent Listing Category", 				"firstlook" ),
			"parent_item_colon"				=> __( "Parent Listing Category:", 				"firstlook" ),
			"search_items"					=> __( "Search Listing Categories", 			"firstlook" ),
			"popular_items"					=> __( "Popular Listing Categories", 			"firstlook" ),
			"separate_items_with_commas"	=> __( "Separate categories with commas", 		"firstlook" ),
			"add_or_remove_items"			=> __( "Add or remove listing categories", 		"firstlook" ),
			"choose_from_most_used"			=> __( "Choose from the most used categories", 	"firstlook" ),
			"not_found"						=> __( "No Listing Categories found.", 			"firstlook" ),
		);
		
		// Parameters for the listing category taxonomy
		$args = array(
			
			"labels"             	=> $labels,
			
			"public"             	=> true,
			"publicly_queryable" 	=> true,
			"show_ui"            	=> true,
			"show_admin_column" 	=> true,
			"show_in_menu"       	=> true,
			"query_var"          	=> true,
			"has_archive"        	=> true,
			"hierarchical"       	=> true,
			"menu_position"      	=> null,

			"rewrite"            	=> array("slug" => $categoty_slug, "with_front" => false),
		);
		
		// Register a new default taxonomy for listing posts
		register_taxonomy("fms_listing_category", "fms_listing", $args);		
		register_taxonomy_for_object_type("fms_listing_category", "fms_listing");
	}
	add_action("init", "fms_accessor_register_post_types");

?>