<?php defined("ABSPATH") or die("Access denied.") ?>
<?php

	// MASTER LIST OF ALL THE META FIELDS ASSOCIATED WITH EACH LISTING
	/*
		"block_sync_all"
		"block_sync_photos"
		"block_sync_remarks"
		"mls_number"
		"data_source"
		"data_source_id"
		"status"
		"price"
		"closing_date"
		"last_updated"
		"taxes"
		"tax_year"
		"beds"
		"baths"
		"property_type"
		"dwelling_type"
		"building_style"
		"zoning"
		"year_built"
		"flooring"
		"heating"
		"heating_fuel"
		"ac_type"
		"square_footage"
		"lot_size"
		"parking"
		"parking_type"
		"garage_spaces"
		"inclusions"
		"exclusions"
		"lease_rate"
		"tenant_pays"
		"amenities"
		"condo_fee"
		"fee_includes"
		"neighborhood"
		"city"
		"province"
		"postal_code"
		"latitude"
		"longitude"
		"directions"
		"rooms_group"
		"openhouse_group"
		"media_videos_group"
		"media_tours_group"
		"media_links_group"
		"brokerage_code"
		"listing_agent_1"
		"listing_agent_2"
		"listing_agent_3"
		"presented_by"
	*/		
	
	include (FMS_DIR."/admin-ui/fms-metabox-photo-manager.php");	// Custom photo management metabox
	include (FMS_DIR."/admin-ui/fms-editor-notices.php");			// Notices for display on the admin panel

	// For testing only
	//include (FMS_DIR."/admin-ui/fms-metabox-raw-data.php");		// Custom raw data display metabox


	// LISTING POST SETUP TWEAKS
	
	// **********************************************************************************************************************************
	//
	// 		fms_change_listing_title_text
	//
	// 		- Called by: 	Filter, enter_title_here
	//		- Parameters: 	Default placeholder text for new post titles
	//		- Returns: 		Modified title placeholder
	//
	// 		Alter the default title placeholder text for new fms_listings to reflect what data should be entered.
	//
	// **********************************************************************************************************************************
	
	function fms_change_listing_title_text($title) {
	
		$screen = get_current_screen();
		if ($screen->post_type == "fms_listing") {
		
			$title = "Listing Address";
		}
		return $title;
	}
	add_filter("enter_title_here", "fms_change_listing_title_text");
	
	
	// **********************************************************************************************************************************
	//
	// 		fms_remove_listing_media_button
	//
	// 		- Called by: 	Action, admin_head
	//		- Parameters: 	None
	//		- Returns: 		None
	//
	// 		Removes the "add media" button from the listing content editor.
	//
	// **********************************************************************************************************************************
	
	function fms_remove_listing_media_button() {
		
		$screen = get_current_screen();
		if ($screen->post_type == "fms_listing") {
		
			remove_action("media_buttons", "media_buttons");
		}
	}
	add_action("admin_head", "fms_remove_listing_media_button");
	
	
	// **********************************************************************************************************************************
	//
	// 		fms_exclude_listings
	//
	// 		- Called by: 	Action, pre_get_posts
	//		- Parameters: 	Auto, current instance of WP_Query before posts are pulled
	//		- Returns: 		None, query is modified by reference
	//
	// 		Modify the running query before fms_listing posts are pulled from the DB (in search, tax, or type contexts).
	//		Exclude listing posts that fall into one of the blocking meta status categories.
	//
	// **********************************************************************************************************************************

	function fms_exclude_listings($query) {
	
		if (!is_admin() && defined("FMS_META_PRE")) {
			
			// Check the admin option:
			// We're either going to allow or disallow listings in the main homepage query
			// If the option is enabled, we'll mix posts and listings together
			
			if (!fms_get_option("hide_homepage_listings", false)) {
				
				if (is_home() && $query->is_main_query()) {
					
					$query->set("post_type", array("post", "fms_listing"));
				}
			}
			
			// Extract the post type from the query
			$pt = $query->get("post_type");
			
			$is_listing		= (($pt == "fms_listing" || (is_array($pt) && in_array("fms_listing", $pt))) ? true : false);
			$is_tax	 		= $query->is_tax("fms_listing_category");
			$is_search 		= $query->is_search();

			// If post type contains listings, is a tax query, or a search, apply some filters
			if ($is_listing || $is_tax || $is_search) {
				
				$meta_query = $query->get("meta_query");
											
				if (empty($meta_query["fms"])) {
					
					$meta_query["fms"] = true;
					
					
					
					$meta_query = array(

						"relation" => "OR",
						
						"normal_posts" => array(
							
							"key" 		=> FMS_META_PRE."status",
							"type"    	=> "NUMERIC",
							"compare" 	=> "NOT EXISTS",
						),
						
						"listing_exclusions" => array(
						
							"relation" => "AND",
							
							array(
								
								"key"		=> FMS_META_PRE."status",
								"type"    	=> "NUMERIC",
								"compare"	=> "EXISTS",
							),
							array(
							
								"key"		=> FMS_META_PRE."status",
								"value"		=> fms_listing_statuses::$NONE,
								"compare"	=> "!=",
							),		
							array(
							
								"key"		=> FMS_META_PRE."status",
								"value"		=> fms_listing_statuses::$EXPIRED,
								"compare"	=> "!=",
							),
							array(
							
								"key"		=> FMS_META_PRE."status",
								"value"		=> fms_listing_statuses::$CANCELLED,
								"compare"	=> "!=",
							),
						),
					);
					
					if (fms_get_option("hide_unavailable_listings", false)) {
						
						//$meta_query["fms"]["listing_exclusions"] = array_merge($meta_query["fms"]["listing_exclusions"], array(
						$meta_query["listing_exclusions"] = array_merge($meta_query["fms"]["listing_exclusions"], array(
							
							array(
							
								"key"		=> FMS_META_PRE."status",
								"value"		=> fms_listing_statuses::$SOLD,
								"compare"	=> "!=",
							),
							array(
							
								"key"		=> FMS_META_PRE."status",
								"value"		=> fms_listing_statuses::$LEASED,
								"compare"	=> "!=",
							),
						));
					}
					
					//$query->set("meta_key", FMS_META_PRE."status");
					$query->set("orderby", array("meta_value_num" => "ASC", "date" => "DESC"));
				}
				
			    $query->set("meta_query", $meta_query);
			}
		}	
	}
	add_action("pre_get_posts", "fms_exclude_listings");
	
	
	// **********************************************************************************************************************************
	//
	// 		fms_search_join, fms_search_where, fms_search_distinct
	//
	// 		- Called by: 	Filter, posts_join, posts_where, posts_distinct
	//		- Parameters: 	Various
	//		- Returns: 		Various
	//
	// 		Modify the raw SQL during search operations to also include the postmeta table.
	//		This allows the standard WP search functions to find listings based on their meta keys and values.
	//
	// **********************************************************************************************************************************

	function fms_search_join($join) {
		
	    global $wpdb;
	
	    if (is_search()) {
		    
	        $join .= (" LEFT JOIN ". $wpdb->postmeta ." AS fms_search ON ". $wpdb->posts .".ID = fms_search.post_id");
	    }
	    return $join;
	}
	add_filter("posts_join", "fms_search_join");

	function fms_search_where($where) {
		
	    global $wpdb, $wp;
	   
	    if (is_search()) {
			
			$where = preg_replace(
			"/\(\s*". $wpdb->posts .".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			"(". $wpdb->posts .".post_title LIKE $1) OR (fms_search.meta_value LIKE $1)", 
			$where
			);
	    }
	    return $where;
	}
	add_filter("posts_where", "fms_search_where");
	
	function fms_search_distinct($where) {
		
	    global $wpdb;
	
	    if (is_search()) {
		    
	        return "DISTINCT";
	    }
	    return $where;
	}
	add_filter("posts_distinct", "fms_search_distinct");
	
	
	// LISTING POST ADMIN TABLE
	
	// **********************************************************************************************************************************
	//
	// 		fms_listing_columns
	//
	// 		- Called by: 	Filter, manage_edit-fms_listing_columns
	//		- Parameters: 	Array of default editor columns
	//		- Returns: 		Modified column array for fms_listings
	//
	// 		Alter the column set shown in the WP editor for posts of type fms_listing
	//
	// **********************************************************************************************************************************
	
	function fms_listing_columns($columns) {
		
		$columns = array(
			
			// Built-in fields
			"cb" 		=> "<input type='checkbox' />",
			"title" 	=> __("Address"),
			
			// Custom fields
			"mls" 			=> __("MLS#",				"firstlook"),
			"blocked"		=> __("Updates Blocked",	"firstlook"),
			"status" 		=> __("MLS Status",			"firstlook"),
			"source"		=> __("Data Source",		"firstlook"),
			"updated" 		=> __("MLS Updated",		"firstlook"),
			"openhouse"		=> __("Open House",			"firstlook"),
			"listcat"		=> __("Categories",			"firstlook"),
		);
		
		return $columns;
	}
	add_filter("manage_edit-fms_listing_columns", "fms_listing_columns");
	
	
	// **********************************************************************************************************************************
	//
	// 		fms_manage_listing_columns
	//
	// 		- Called by: 	Action, manage_fms_listing_posts_custom_column
	//		- Parameters: 	Target column name, ID of current post
	//		- Returns: 		None ($meta text printed to stdout)
	//
	// 		Get and display the value that should appear for in each column of the admin data table for each fms_listing
	//
	// **********************************************************************************************************************************
	
	function fms_manage_listing_columns($column, $post_id) {
	
		if (defined("FMS_META_PRE")) {
	
			global $post;
			
			$meta = "";
			
			switch ($column) {
				
				// MLS NUMBER COLUMN
				case "mls":
				
					$meta = get_post_meta($post->ID, FMS_META_PRE."mls_number", true);
					
					if(empty($meta)) $meta = __("Exclusive", "firstlook");
					
 					break;
				
				// LISTING STATUS COLUMN
				case "status":
	
					switch (get_post_meta($post->ID, FMS_META_PRE."status", true)) {
						
						case fms_listing_statuses::$NONE:			$meta = __("None (hidden)", "firstlook"); break;
					    case fms_listing_statuses::$ACTIVE_SALE: 	$meta = __("For Sale", 		"firstlook"); break;
					    case fms_listing_statuses::$ACTIVE_LEASE:	$meta = __("For Lease", 	"firstlook"); break;
					    case fms_listing_statuses::$CONDITIONAL:	$meta = __("Conditional",	"firstlook"); break;
					    case fms_listing_statuses::$SOLD:   		$meta = __("Sold", 			"firstlook"); break;
					    case fms_listing_statuses::$LEASED:   		$meta = __("Leased", 		"firstlook"); break;
					    case fms_listing_statuses::$CANCELLED:		$meta = __("Cancelled",		"firstlook"); break;
					    case fms_listing_statuses::$EXPIRED:		$meta = __("Expired",		"firstlook"); break;
					}
					break;
				
				// FEED SOURCE COLUMN
				case "source":

					$meta = get_post_meta($post->ID, FMS_META_PRE."data_source", true);
					
					if(empty($meta)) $meta = __("Exclusive", "firstlook");
					
					break;
				
				// LAST UPDATED COLUMN
				case "updated":
				
					$last_updated = intval(get_post_meta($post->ID, FMS_META_PRE."last_updated", true), 10);
				
					if (!empty($last_updated)) $meta = date("m/d/Y", $last_updated);
					else $meta = __("Exclusive", "firstlook");
					
					break;
				
				// OPEN HOUSE COLUMN
				case "openhouse":
				
					$openhouses = get_post_meta($post->ID, FMS_META_PRE."openhouse_group", true);
					if (!empty($openhouses[0])) {
						
						$meta  = "";
						$dates = array();
						
						foreach ($openhouses as $openhouse) {
							$dates[] = date("m/d/Y", $openhouse["openhouse_start"]);
						}
						
						$meta = implode(", ", array_filter($dates));						
					}
					break;
				
				// BLOCKED UPDATES COLUMN
				case "blocked":
				
					$all = get_post_meta($post->ID, FMS_META_PRE."block_sync_all", 		true);
					$pic = get_post_meta($post->ID, FMS_META_PRE."block_sync_photos", 	true);
					$add = get_post_meta($post->ID, FMS_META_PRE."block_sync_address", 	true);
					$rem = get_post_meta($post->ID, FMS_META_PRE."block_sync_remarks", 	true);
					$sta = get_post_meta($post->ID, FMS_META_PRE."block_sync_status", 	true);
					
					if (!empty($all) || !empty($pic) || !empty($rem) || !empty($sta)) {
						
						if (!empty($all)) $meta = __("All", "firstlook");
						else {
							
							$pic = (!empty($pic) ? __("Photos", 		"firstlook") 	: "");
							$add = (!empty($add) ? __("Address", 		"firstlook") 	: "");
							$rem = (!empty($rem) ? __("Remarks", 		"firstlook") 	: "");
							$sta = (!empty($sta) ? __("MLS Status", 	"firstlook") 	: "");
							
							$meta .= implode(", ", array_filter(array( $pic, $add, $rem, $sta )));
						}
					}
					break;
					
				// LISTING CATEGORIES COLUMN
				case "listcat":

					$terms = wp_get_post_terms($post->ID, "fms_listing_category");
					$names = array();
					
					foreach ($terms as $term) $names[] = $term->name;

					$meta = implode(", ", array_filter($names));
					break;
			}
			
			printf(!empty($meta) ? $meta : "---");
		}	
	}
	add_action("manage_fms_listing_posts_custom_column", "fms_manage_listing_columns", 10, 2);
	
	
	// **********************************************************************************************************************************
	//
	// 		fms_sortable_listing_columns
	//
	// 		- Called by: 	Filter, manage_edit-fms_listing_sortable_columns
	//		- Parameters: 	Array of columns that are marked as "sortable"
	//		- Returns: 		Alterd list of columms to include some additional meta fields
	//
	// 		Make the admin table sortable on some of the new meta columns
	//
	// **********************************************************************************************************************************
	
	function fms_sortable_listing_columns($columns) {
		
		if (defined("FMS_META_PRE")) {
		
			$columns["mls"] 	= "mls";
			$columns["status"] 	= "status";
			$columns["source"] 	= "source";
			$columns["updated"] = "updated";		
		}
		return $columns;
	}
	add_filter("manage_edit-fms_listing_sortable_columns", "fms_sortable_listing_columns");
	
	
	// **********************************************************************************************************************************
	//
	// 		fms_listing_meta_orderby
	//
	// 		- Called by: 	Action, pre_get_posts
	//		- Parameters: 	Current post query object
	//		- Returns: 		None, query is modified by reference
	//
	// 		Alter the post query to sort the admin table when sorting is triggered through one of the new columns
	//
	// **********************************************************************************************************************************
	
	function fms_listing_meta_orderby($query) {
		
	    if (is_admin() && defined("FMS_META_PRE")) {
	 
		    $orderby = $query->get("orderby");
		 
			if ($orderby == "mls") {
			
				$key = FMS_META_PRE."mls_number";
				$query->set("orderby", "meta_value_num");
			}
			
			if ($orderby == "status") {
			
				$key = FMS_META_PRE."status";
				$query->set("orderby", "meta_value");
			}
			
			if ($orderby == "source") {
			
				$key = FMS_META_PRE."data_source";
				$query->set("orderby", "meta_value");
			}
			
			if ($orderby == "updated") {
			
				$key = FMS_META_PRE."last_updated";
				$query->set("orderby", "meta_value_num");
			}
		    
		    if (!empty($key)) {
		    
			    $query->set("meta_query", array(
				    
				    "relation" => "OR",
				    array (
					    
					    "key"		=> $key,
					    "compare"	=> "EXISTS",
				    ),
				    array (
					    
					    "key"		=> $key,
					    "compare"	=> "NOT EXISTS",
				    ),
				    
			    ));
		    }
	    }
	}
	add_action("pre_get_posts", "fms_listing_meta_orderby");
	
	
	// CMB2 BOXES
	
	// **********************************************************************************************************************************
	//
	// 		fms_create_listing_metaboxes
	//
	// 		- Called by: 	Action, cmb2_admin_init (from lib)
	//		- Parameters: 	None
	//		- Returns: 		None
	//
	// 		Use the CMB2 API to build all the custom meta boxes for our fms_listing posts
	//
	// **********************************************************************************************************************************
	
	function fms_create_listing_metaboxes() {
	
		if (defined("FMS_META_PRE")) {

			// BLOCK SYNC
			$cmb = new_cmb2_box(array(
				
				"title"         => __("Sync Block", "firstlook" ), 
				
		        "id"            => "sync_block_metabox",
		        "context"       => "side",
		        "priority"      => "default",
		        "show_names"    => false,
		        
		        "object_types"  => array("fms_listing"),
		    ));
			$cmb->add_field(array(
			
				"id"        	=> FMS_META_PRE."block_sync_all",
				"type"     	 	=> "checkbox",
				
				"desc"			=> __("Prevent the auto sync from overwriting any part of this listing.", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"        	=> FMS_META_PRE."block_sync_photos",
				"type"      	=> "checkbox",
				
				"desc"			=> __("Prevent the auto sync from overwriting the listing photos.", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"        	=> FMS_META_PRE."block_sync_address",
				"type"      	=> "checkbox",
				
				"desc"			=> __("Prevent the auto sync from overwriting the title (address) field.", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"        	=> FMS_META_PRE."block_sync_remarks",
				"type"      	=> "checkbox",
				
				"desc"			=> __("Prevent the auto sync from overwriting the content (public remarks) field.", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"        	=> FMS_META_PRE."block_sync_status",
				"type"      	=> "checkbox",
				
				"desc"			=> __("Prevent the auto sync from overwriting the listing status field.", "firstlook"),
			));
			
			// LISTING INFORMATION
			$cmb = new_cmb2_box(array(
				
				"title"         => __("Listing Information", "firstlook" ), 
				
		        "id"            => "listing_metabox",
		        "context"       => "normal",
		        "priority"      => "high",
		        "show_names"    => true,
		        
		        "object_types"  => array("fms_listing"),
		    ));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."mls_number",
				"type"      => "text",
				
				"name"      => __("MLS#", 		"firstlook"),
				"desc"		=> __("Read-Only", 	"firstlook"),
				"default"	=> __("N/A", 		"firstlook"),

				"save_field" => false,
				"attributes" => array(
					
					"readonly" 	=> "readonly",
					"disabled" 	=> "disabled",
				),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."data_source",
				"type"      => "text",
				
				"name"      => __("Data Source", 	"firstlook"),
				"desc"		=> __("Read-Only", 		"firstlook"),
				"default"	=> __("Exclusive", 		"firstlook"),
				
				"save_field" => false,
				"attributes" => array(
					
					"readonly" 	=> "readonly",
					"disabled" 	=> "disabled",
				),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."data_source_id",
				"type"      => "text",
				
				"name"      => __("Data Source ID", "firstlook"),
				"desc"		=> __("Read-Only", 		"firstlook"),
				"default"	=> __("fms_exclusive", 	"firstlook"),
				
				"save_field" => false,
				"attributes" => array(
					
					"readonly" 	=> "readonly",
					"disabled" 	=> "disabled",
				),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."listing_key",
				"type"      => "text",
				
				"name"      => __("Listing Key", 	"firstlook"),
				"desc"		=> __("Read-Only", 		"firstlook"),
				"default"	=> __("N/A", 			"firstlook"),

				"save_field" => false,
				"attributes" => array(
					
					"readonly" 	=> "readonly",
					"disabled" 	=> "disabled",
				),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."status",
				"type"       => "select",
				
				"name"       => __("Listing Status", "firstlook"),
				
				"options" => array(
					
			        fms_listing_statuses::$NONE				=> __("None (hidden)", 	"firstlook"),
			        fms_listing_statuses::$ACTIVE_SALE 		=> __("For Sale", 		"firstlook"),
			        fms_listing_statuses::$ACTIVE_LEASE 	=> __("For Lease", 		"firstlook"),
			        fms_listing_statuses::$CONDITIONAL 		=> __("Conditional", 	"firstlook"),
			        fms_listing_statuses::$SOLD    			=> __("Sold", 			"firstlook"),
			        fms_listing_statuses::$LEASED    		=> __("Leased", 		"firstlook"),
			        fms_listing_statuses::$CANCELLED 		=> __("Cancelled",		"firstlook"),
			        fms_listing_statuses::$EXPIRED 			=> __("Expired",		"firstlook"),
			    ),
			    
			    "default" => "none",
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."price",
				"type"      => "text",
				
				"name"      => __("Price", "firstlook"),
				"desc"		=> __("Sale or Lease price.", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."closing_date",
				"type"      => "text_date_timestamp",
				
				"name"      => __("Closing Date", "firstlook"),
				"desc"		=> __("Set an effective removal date for this listing.", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."last_updated",
				"type"      => "text_date_timestamp",
				
				"name"      => __("MLS Last Updated", "firstlook"),
				"desc"		=> __("Read-Only - The last time this listing was updated on MLS.", "firstlook"),
				
				"save_field" => false,
				"attributes" => array(
					
					"readonly" 	=> "readonly",
					"disabled" 	=> "disabled",
				),
			));
			
			// PROPERTY DETAILS
			$cmb = new_cmb2_box(array(
				
				"title"         => __("Property Details", "firstlook" ), 
				
		        "id"            => "property_metabox",
		        "context"       => "normal",
		        "priority"      => "high",
		        "show_names"    => true,
		        
		        "closed"		=> "true",
		        
		        "object_types"  => array("fms_listing"),
		    ));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."taxes",
				"type"      => "text",
				
				"name"      => __("Taxes", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."tax_year",
				"type"      => "text",
				
				"name"      => __("Tax Year", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."beds",
				"type"       => "text",
				
				"name"       => __("Bedroom Count", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."baths",
				"type"       => "text",
				
				"name"       => __("Bathroom Count", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."property_type",
				"type"       => "text",
				
				"name"       => __("Property Type", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."dwelling_type",
				"type"       => "text",
				
				"name"       => __("Dwelling Type", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."building_style",
				"type"       => "text",
				
				"name"       => __("Building Style", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."zoning",
				"type"       => "text",
				
				"name"       => __("Zoning", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."year_built",
				"type"       => "text",
				
				"name"       => __("Year Built", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."flooring",
				"type"       => "text",
				
				"name"       => __("Flooring", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."heating",
				"type"       => "text",
				
				"name"       => __("Heating", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."heating_fuel",
				"type"       => "text",
				
				"name"       => __("Heating Fuel", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."ac_type",
				"type"       => "text",
				
				"name"       => __("AC Type", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."square_footage",
				"type"       => "text",
				
				"name"       => __("Square Footage", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."lot_size",
				"type"       => "text",
				
				"name"       => __("Lot Size", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."parking",
				"type"       => "text",
				
				"name"       => __("Parking Style", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."parking_type",
				"type"       => "text",
				
				"name"       => __("Parking Type", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."garage_spaces",
				"type"       => "text",
				
				"name"       => __("Garage Spaces", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."inclusions",
				"type"       => "textarea",
				
				"name"       => __("Inclusions", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"         => FMS_META_PRE."exclusions",
				"type"       => "textarea",
				
				"name"       => __("Exclusions", "firstlook"),
			));

			// LEASE / RENTAL INFORMATION
			$cmb = new_cmb2_box(array(
				
				"title"         => __("Lease Information", "firstlook" ), 
				
		        "id"            => "lease_metabox",
		        "context"       => "normal",
		        "priority"      => "high",
		        "show_names"    => true,
		        
		        "closed"		=> "true",
		        
		        "object_types"  => array("fms_listing"),
		    ));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."lease_rate",
				"type"      => "text",
				
				"name"      => __("Lease Rate", "firstlook"),
				"desc"		=> __("The frequency that the tenant must pay (eg: /Month, /Year, /Sqft. etc).", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."tenant_pays",
				"type"      => "text",
				
				"name"      => __("Tenant Pays...", "firstlook"),
				"desc"		=> __("List everything that the tenant must pay in addition to the Lease Rate.", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."amenities",
				"type"      => "text",
				
				"name"      => __("Rental Amenities", "firstlook"),
			));

			// CONDO INFORMATION
			$cmb = new_cmb2_box(array(
				
				"title"         => __("Condo Information", "firstlook" ), 
				
		        "id"            => "condo_metabox",
		        "context"       => "normal",
		        "priority"      => "high",
		        "show_names"    => true,
		        
		        "closed"		=> "true",
		        
		        "object_types"  => array("fms_listing"),
		    ));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."condo_fee",
				"type"      => "text",
				
				"name"      => __("Condominium Fee", "firstlook"),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."fee_includes",
				"type"      => "text",
				
				"name"      => __("Condo Fee Includes", "firstlook"),
			));

			// LOCATION BOX
			$cmb = new_cmb2_box(array(
				
				"title"         => __("Location Details", "firstlook" ), 
				
		        "id"            => "location_metabox",
		        "context"       => "normal",
		        "priority"      => "high",
		        "show_names"    => true,
		        
		        "closed"		=> "true",
		        
		        "object_types"  => array("fms_listing"),
		    ));
			$cmb->add_field(array(
				
				"id"         => FMS_META_PRE."neighborhood",
		        "type"       => "text",
				
		        "name"       => __("Neighbourhood", "firstlook"),
		    ));
			$cmb->add_field(array(
				
				"id"         => FMS_META_PRE."city",
		        "type"       => "text",
				
		        "name"       => __("City", "firstlook"),
		    ));
			$cmb->add_field(array(
				
				"id"         => FMS_META_PRE."province",
		        "type"       => "text",
				
		        "name"       => __("Province", "firstlook"),
		    ));
		    $cmb->add_field(array(
				
				"id"         => FMS_META_PRE."postal_code",
		        "type"       => "text",
				
		        "name"       => __("Postal Code", "firstlook"),
		    ));
		    $cmb->add_field(array(
				
				"id"         => FMS_META_PRE."latitude",
		        "type"       => "text",
				
		        "name"       => __("Latitude", "firstlook"),
		    ));
			$cmb->add_field(array(
				
				"id"         => FMS_META_PRE."longitude",
		        "type"       => "text",
				
		        "name"       => __("Longitude", "firstlook"),
		    ));
		    $cmb->add_field(array(
				
				"id"         => FMS_META_PRE."directions",
		        "type"       => "textarea",
				
		        "name"       => __("Directions", "firstlook"),
		    ));

			// ROOMS GROUP
			$cmb = new_cmb2_box(array(
				
				"title"         => __("Rooms", "firstlook" ), 
				
		        "id"            => "rooms_metabox",
		        "context"       => "normal",
		        "priority"      => "high",
		        "show_names"    => true,
		        
		        "closed"		=> "true",
		        
		        "object_types"  => array("fms_listing"),
		    ));
			$cmb->add_field( array(
			
				"id"          => FMS_META_PRE."rooms_group",
				"type"        => "group",
				
				"options"     => array(
				    
				    "group_title"   => __("Room {#}", 		"firstlook"),
				    "add_button"    => __("Add A Room", 	"firstlook"),
				    "remove_button" => __("Remove Room", 	"firstlook"),
				    
				    "closed"		=> "true",
				),

				"fields" => array(
					
					array(
			
						"id"   => "room_name",
						"type" => "text",
						
						"name" => __("Room Name", "firstlook"),
					),
					array(
			
						"id"   => "room_level",
						"type" => "text",
					
						"name" => __("Room Level", "firstlook"),
					),
					array(
			
						"id"   => "room_dimensions",
						"type" => "text",
						
						"name" => __("Room Dimensions", "firstlook"),
					),
				),
			));

			// OPEN HOUSE GROUP
			$cmb = new_cmb2_box(array(
				
				"title"         => __("Open House Events", "firstlook" ), 
				
		        "id"            => "openhouse_metabox",
		        "context"       => "normal",
		        "priority"      => "high",
		        "show_names"    => true,
		        
		        "closed"		=> "true",
		        
		        "object_types"  => array("fms_listing"),
		    ));
			$cmb->add_field( array(
			
				"id"          => FMS_META_PRE."openhouse_group",
				"type"        => "group",
				
				"options"     => array(
				    
				    "group_title"   => __("Open House Group {#}", 		"firstlook"),
				    "add_button"    => __("Add An Open House Event", 	"firstlook"),
				    "remove_button" => __("Remove Event", 				"firstlook"),
				    
				    "closed"		=> "false",
				),
				
				"fields" => array(
					
					array(
			
						"id"   => "openhouse_start",
						"type" => "text_datetime_timestamp",
						
						"name" => __("Event Start", "firstlook"),
					),
					array(
			
						"id"   => "openhouse_end",
						"type" => "text_datetime_timestamp",
					
						"name" => __("Event End", "firstlook"),
					),
					array(
			
						"id"   => "openhouse_description",
						"type" => "textarea",
						
						"name" => __("Open House Description", "firstlook"),
					),
				),
			));
			
			// MEDIA
			$cmb = new_cmb2_box(array(
				
				"title"         => __("Multimedia", "firstlook" ), 
				
		        "id"            => "media_metabox",
		        "context"       => "normal",
		        "priority"      => "high",
		        "show_names"    => true,
		        
		        "closed"		=> "true",
		        
		        "object_types"  => array("fms_listing"),
		    ));
		    $cmb->add_field( array(
			
				"id"        => FMS_META_PRE."media_videos_group",
				"type"      => "group",
				
				"options"   => array(
				    
				    "group_title"   => __("Embedded Video {#}", 	"firstlook"),
				    "add_button"    => __("Add Embedded Video", 	"firstlook"),
				    "remove_button" => __("Remove Video", 			"firstlook"),
				    
				    "closed"		=> "false",
				),
				
				"fields" => array(
					
					array(
			
						"id"   => "video_name",
						"type" => "text",
						
						"name" => __("Video Name", "firstlook"),
					),
					array(
			
						"id"   => "video_url",
						"type" => "text",
					
						"name" => __("Video Embed URL", "firstlook"),
					),
				),
			));
			$cmb->add_field( array(
			
				"id"		=> FMS_META_PRE."media_tours_group",
				"type"      => "group",
				
				"options"   => array(
				    
				    "group_title"   => __("Embedded 3DTour {#}", 	"firstlook"),
				    "add_button"    => __("Add Embedded 3DTour", 	"firstlook"),
				    "remove_button" => __("Remove Tour", 			"firstlook"),
				    
				    "closed"		=> "false",
				),
				
				"fields" => array(
					
					array(
			
						"id"   => "tour_name",
						"type" => "text",
						
						"name" => __("3DTour Name", "firstlook"),
					),
					array(
			
						"id"   => "tour_url",
						"type" => "text",
					
						"name" => __("3dTour Embed URL", "firstlook"),
					),
				),
			));
			$cmb->add_field( array(
			
				"id"        => FMS_META_PRE."media_links_group",
				"type"      => "group",
				
				"options"   => array(
				    
				    "group_title"   => __("Media Link {#}", 		"firstlook"),
				    "add_button"    => __("Add Media Link", 		"firstlook"),
				    "remove_button" => __("Remove Link", 			"firstlook"),
				    
				    "closed"		=> "false",
				),
				
				"fields" => array(
					
					array(
			
						"id"   => "link_name",
						"type" => "text",
						
						"name" => __("Link Name", "firstlook"),
					),
					array(
			
						"id"   => "link_url",
						"type" => "text",
					
						"name" => __("Link URL", "firstlook"),
					),
				),
			));

			// AGENCY INFORMATION
			$cmb = new_cmb2_box(array(
				
				"title"         => __("Agency Information", "firstlook" ), 
				
		        "id"            => "agency_metabox",
		        "context"       => "normal",
		        "priority"      => "high",
		        "show_names"    => true,
		        
		        "closed"		=> "true",
		        
		        "object_types"  => array("fms_listing"),
		    ));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."brokerage_code",
				"type"      => "text",
				
				"name"      => __("Brokerage Code", "firstlook"),
				"desc"		=> __("Read-Only", "firstlook"),
				
				"save_field" => false,
				"attributes" => array(
					
					"readonly" 	=> "readonly",
					"disabled"	=> "disabled",
				),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."listing_agent_1",
				"type"      => "text",
				
				"name"      => __("Listing Agent 1", "firstlook"),
				"desc"		=> __("Read-Only", "firstlook"),
				
				"save_field" => false,
				"attributes" => array(
					
					"readonly" 	=> "readonly",
					"disabled" 	=> "disabled",
				),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."listing_agent_2",
				"type"      => "text",
				
				"name"      => __("Listing Agent 2", "firstlook"),
				"desc"		=> __("Read-Only", "firstlook"),
				
				"save_field" => false,
				"attributes" => array(
					
					"readonly" 	=> "readonly",
					"disabled" 	=> "disabled",
				),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."listing_agent_3",
				"type"      => "text",
				
				"name"      => __("Listing Agent 3", "firstlook"),
				"desc"		=> __("Read-Only", "firstlook"),
				
				"save_field" => false,
				"attributes" => array(
					
					"readonly" 	=> "readonly",
					"disabled" 	=> "disabled",
				),
			));
			$cmb->add_field(array(
			
				"id"        => FMS_META_PRE."presented_by",
				"type"      => "text",
				
				"name"      => __("Presented By:", "firstlook"),
				"desc"		=> __("Read-Only", "firstlook"),
				
				"save_field" => false,
				"attributes" => array(
					
					"readonly" 	=> "readonly",
					"disabled" 	=> "disabled",
				),
			));
		}	
	}
	add_action("cmb2_admin_init", "fms_create_listing_metaboxes");

?>