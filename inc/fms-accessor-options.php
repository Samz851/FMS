<?php defined("ABSPATH") or die("Access denied.") ?>
<?php

	class fms_accessor_options {
		
		private 		$key 			= "fms_accessor_options";			// Option key, and option page slug
		private 		$metabox_id		= "fms_accessor_options_metabox";	// Metabox ID to hold the options
		protected 		$title 			= "";								// Options Page title
		protected 		$options_page 	= "";								// Options Page hook
		private static 	$instance 		= null; 							// Holds an instance of the object
				
		
		// **********************************************************************************************************************************
		// 		Constructor
		// **********************************************************************************************************************************
		
		private function __construct() {
			
			$this->title = __("Settings", "firstlook");	// Set the title
		}
		
		
		// **********************************************************************************************************************************
		// 		Get an instance of this class
		// **********************************************************************************************************************************
		
		public static function get_instance() {
			
			if (is_null(self::$instance)) {
				
				self::$instance = new self();	// Create a new instance
				self::$instance->hooks();		// Register WP admin hooks
			}
			return self::$instance;
		}
		

		// **********************************************************************************************************************************
		// 		Register WP action hooks
		// **********************************************************************************************************************************

		public function hooks() {
			
			add_action("admin_init", 		array($this, "init"));
			add_action("admin_menu", 		array($this, "add_options_page"));
			add_action("cmb2_admin_init", 	array($this, "add_options_page_metabox"));
		}
		
		
		// **********************************************************************************************************************************
		// 		Admin initialization
		// **********************************************************************************************************************************
		
		public function init() {
			
			register_setting($this->key, $this->key);	// Register the settings group
		}
		
		
		// **********************************************************************************************************************************
		// 		Create the relevant admin options (sub)page
		// **********************************************************************************************************************************
		
		public function add_options_page() {

			$this->options_page = add_submenu_page(
			
				"edit.php?post_type=fms_listing",		// Parent slug
			
				$this->title,							// Page title
				$this->title, 							// Menu title
				
				"manage_options",						// Capability
				
				$this->key,								// Menu slug
				array($this, "admin_page_display")		// Page renderer
			);
			
			// Include CMB CSS in the head to avoid FOUC
			add_action("admin_print_styles-{$this->options_page}", array("CMB2_hookup", "enqueue_cmb_css"));
		}
		
		
		// **********************************************************************************************************************************
		// 		Render the admin page UI
		// **********************************************************************************************************************************
		
		public function admin_page_display() {
			
			include (FMS_DIR ."/admin-ui/fms-tpl-accessor-options-page.php");
		}
		
		
		// **********************************************************************************************************************************
		// 		Add custom metaboxes
		// **********************************************************************************************************************************
		
		public function add_options_page_metabox() {
			
			foreach ($GLOBALS["fms_accessor_classes"] as $accessor_class) {
			
				$accessor 	= new $accessor_class;
				$notice 	= $accessor->fms_save_options_notice();
				
				// Add the update notice
				add_action("cmb2_save_options-page_fields_{$accessor_class}", function ($object_id, $updated) use ($notice) {
			
					$opt_singleton = fms_accessor_options::get_instance();
					$opt_singleton->fms_settings_notices($object_id, $updated, $notice);
				
				}, 10, 2);
				
				// Generate the form
				$accessor->fms_create_options_form($this->key);
			}
		
			// hook in our save notices
			add_action("cmb2_save_options-page_fields_{$this->metabox_id}", function ($object_id, $updated) {
			
				$opt_singleton = fms_accessor_options::get_instance();
				$opt_singleton->fms_settings_notices($object_id, $updated, "Accessor Options updated");
				
			}, 10, 2);

			$cmb = new_cmb2_box(array(
				
				"id"         	=> $this->metabox_id,
				"hookup"     	=> false,
				"cmb_styles" 	=> false,
				"show_on"   	=> array(
					
					"key"   	=> "options-page",
					"value" 	=> array($this->key),
				),
			));
			
			// MAPS API KEY	
			$cmb->add_field(array(
			
				"before_row"	=> "<h1>Mapping & Location Services</h1>",
			
				"name"      	=> __("Google Maps Key", "firstlook"),
				"desc"			=> __("A valid Google Maps API key is required to display listing maps.<br>
										Click <a href='https://developers.google.com/maps/documentation/javascript/get-api-key' target='_blank'>here</a> 
										for instructions on how to obtain a key.", "firstlook"),
			
				"id"        	=> "google_maps_key",
				"type"      	=> "text",
				
				"after_row"		=> "<hr/>",
			));
			
			// ARCHIVE AND CATEGORY SLUGS
			$cmb->add_field(array(
			
				"before_row"	=> "<h1>Archive & Category Slugs</h1>",
			
				"name"      	=> __("Listing Archive Slug", "firstlook"),
				"desc"			=> __("The slug where you'd like the listing archives to be located. If left blank, will default to \"listings\".<br>
										NOTE: You will need to flush your permalinks before changes to this option will take effect!<br>
										Settings > Permalinks > Save", "firstlook"),
			
				"id"        	=> "listing_archive_slug",
				"type"      	=> "text",
			));
			
			$cmb->add_field(array(
			
				"name"      	=> __("Listing Category Slug", "firstlook"),
				"desc"			=> __("The slug where you'd like the listing categories to be located. If left blank, will default to \"listing-category\".<br>
										NOTE: You will need to flush your permalinks before changes to this option will take effect!<br>
										Settings > Permalinks > Save", "firstlook"),
			
				"id"        	=> "listing_category_slug",
				"type"      	=> "text",
				
				
				// Most of this is hacking in the title and desc info for the group below!
				"after_row"		=> "<hr/>",
			));
			
			
			
			// DEFAULT SEARCHES
			/*
			$cmb->add_field(array(
			
				"before_row"	=> "<h1>Default Searches</h1>",
			
				"desc"			=> __("Add Retriever query strings that will search the National Shared Pool automatically and display the results on the listing archive page. Shared pool listings will always appear after member feed listings, giving your inventory top priority (if applicable). 
									
									See our documentation on 
									
									<a target='_blank' href='http://www.myfirstlook.ca/documentation/national-shared-pool/'>
									The National Shared Pool</a> and 
									
									<a target='_blank' href='http://www.myfirstlook.ca/documentation/retriever-search-parameters/'>
									Retriever Search Parameters</a> for more information.
									
									NOTE: The more searches you add, the greater the performance impact will be. Please use sparingly.
									
									<br/><br/>
									
									Example: A search for all recent listings in Ottawa would look like this:
									<strong>?pool=national&city=Ottawa&province=Ontario</strong>", "firstlook"),
			
				"id"        	=> "default_searches",
				"type"      	=> "text",

				"options" => array(
					
			    	"add_row_text" => __("Add Search Query", "firstlook"),
			    ),

				"repeatable"	=> true,
			));
			*/
			
			
			$listing_categories = get_terms(array(
				
				"taxonomy" 		=> "fms_listing_category",
				"hide_empty" 	=> false,
			));
			
			$category_options = array(
				
				"" => "All Categories",
			);
			
			foreach ($listing_categories as $term) {
				
				$category_options[$term->slug] = $term->name;
			}
			
			
			$cmb->add_field(array(
				
				"before_group"	=> "<h1>Default Searches</h1>",
				
				"id"			=> "default_search_group",
				
				"desc"			=> __("Add Retriever query strings that will search the National Shared Pool automatically and 
									display the results on the listing archive page. Shared pool listings will always appear 
									after member feed listings, giving your inventory top priority (if applicable). 
									
									See our documentation on 
									
									<a target='_blank' href='http://www.myfirstlook.ca/documentation/national-shared-pool/'>
									The National Shared Pool</a> and 
									
									<a target='_blank' href='http://www.myfirstlook.ca/documentation/retriever-search-parameters/'>
									Retriever Search Parameters</a> for more information.
									
									NOTE: The more searches you add, the greater the performance impact will be. Please use sparingly.
									
									<br/><br/>
									
									Example: A search for all recent listings in Ottawa would look like this:
									<strong>?pool=national&city=Ottawa&province=Ontario</strong>", "firstlook"),
				
				"type"			=> "group",
				"options"     	=> array(
				    
				    "group_title"   => __("Default Search {#}", 	"firstlook"),
				    "add_button"    => __("Add A Default Search",	"firstlook"),
				    "remove_button" => __("Remove Default Search", 	"firstlook"),
				    
				    "closed"		=> "true",
				),
				
				"fields" 		=> array(
					
					array(
			
						"id"   	=> "search_query",
						"type" 	=> "text",
						
						"name" 	=> __("Search Query", "firstlook"),
					),
					array(
			
						"id"   	=> "search_in_category",
						"type" 	=> "select",
					
						"name" 	=> __("Listing Category", "firstlook"),
						
						"options" 	=> $category_options,
					),
				),
			));
			
			
			
			$cmb->add_field(array(
			
				"desc"			=> __("Hide default searches on the home page.", "firstlook"),
			
				"id"        	=> "hide_default_search_home",
				"type"      	=> "checkbox",
			));
			$cmb->add_field(array(
			
				"desc"			=> __("Show member feed listings first, before default search content.", "firstlook"),
			
				"id"        	=> "show_member_listings_first",
				"type"      	=> "checkbox",
			));
			$cmb->add_field(array(
			
				"desc"			=> __("Include member feed duplicates in default search results.", "firstlook"),
			
				"id"        	=> "include_default_search_duplicates",
				"type"      	=> "checkbox",
				
				"after_row"		=> "<hr/>",
			));
			
			
			/*
			$cmb->add_field( array(
			
				"id"          	=> "fms_default_searches",
				"type"       	=> "group",
				
				"options"     	=> array(
				    
				    "group_title"   => __("Shared Pool Search {#}", "firstlook"),
				    "add_button"    => __("Add A Search", 			"firstlook"),
				    "remove_button" => __("Remove Search", 			"firstlook"),
				    
				    "closed"		=> "true",
				),

				"fields" => array(
					
					array(
			
						"id"   => "default_city",
						"type" => "text",
						
						"name" => __("City", "firstlook"),
					),
					array(
			
						"id"   => "default_province",
						"type" => "text",
						
						"name" => __("Province", "firstlook"),
					),
					
				),
			));
			*/
			
			
			// CHECKBOXES
			$cmb->add_field(array(
			
				"before_row"	=> "<h1>Other Options</h1>",
			
				"desc"			=> __("Show Firstlook some love! Let us put a \"powered by\" link on the default listing content template.", "firstlook"),
			
				"id"        	=> "show_firstlook_powered_by",
				"type"      	=> "checkbox",
			));	
			$cmb->add_field(array(
			
				"desc"			=> __("Hide listings on the home page. Checking this option will remove listings from the main home page query.", "firstlook"),
			
				"id"        	=> "hide_homepage_listings",
				"type"      	=> "checkbox",
			));
			$cmb->add_field(array(
			
				"desc"			=> __("Do not show \"Sold\" and \"Leased\" listings from the member feed.", "firstlook"),
			
				"id"        	=> "hide_unavailable_listings",
				"type"      	=> "checkbox",
			));
			$cmb->add_field(array(
			
				"desc"			=> __("Disable Retriever SEO output. Check this option if you intend to use an SEO plugin and want to avoid
									potential meta tag or Open Graph conflicts.", "firstlook"),
			
				"id"        	=> "disable_retriever_seo",
				"type"      	=> "checkbox",
			));
			$cmb->add_field(array(
			
				"desc"			=> __("For developers, check this option if you intend to create your own photo lightbox to display listing photos.<br>
									When this option is checked, nothing will happen when a photo is clicked on the default template.", "firstlook"),
			
				"id"        	=> "disable_photo_lightbox",
				"type"      	=> "checkbox",
			));
			$cmb->add_field(array(
			
				"desc"			=> __("For developers, check this option if you intend to create your own clickwrap for CREA terms of service.<br>
									NOTE: You are required by CREA regulations to display a clickwrap before allowing users to view any listings.<br>
									Firstlook Media Solutions Inc. is not responsible if you choose to ignore this requirement!", "firstlook"),
			
				"id"        	=> "disable_clickwrap",
				"type"      	=> "checkbox",
				
				"after_row"		=> "<br/>",
			));
			
			
			
			
			
			
			
			
			
			
			
			
			
			
		}


		// **********************************************************************************************************************************
		// 		Display notices
		// **********************************************************************************************************************************

		public function fms_settings_notices($object_id, $updated, $text) {
			
			if ($object_id !== $this->key || empty($updated)) return;

			add_settings_error($this->key ."-notices", "", $text, "updated");
			settings_errors($this->key ."-notices");
		}

		
		// **********************************************************************************************************************************
		// 		Magic getter
		// **********************************************************************************************************************************

		public function __get($field) {
			
			// Allowed fields to retrieve
			if (in_array($field, array("key", "metabox_id", "title", "options_page"), true)) {
				
				return $this->{$field};
			}
			throw new Exception("Invalid property: ". $field);
		}
	}
	
	
	// Helper function to get/return the fms_accessor_options object
	function fms_accessor_options() {
		
		return fms_accessor_options::get_instance();
	}
	
	// Wrapper function around cmb2_get_option
	function fms_get_option($key = "", $default = "") {
		
		if (function_exists("cmb2_get_option")) {
		
			$option = cmb2_get_option(fms_accessor_options()->key, $key);
		}
		
		if (empty($option)) return $default;
		return $option;
	}
	
	// Kick off the jams
	fms_accessor_options();
	
?>
