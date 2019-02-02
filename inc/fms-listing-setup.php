<?php defined("ABSPATH") or die("Access denied.") ?>
<?php

	if (defined("FMS_META_PRE")) {


		// **********************************************************************************************************************************
		//
		// 		CLASS: FMSListingdata (singleton)
		//
		// 		- Called by: 	Any API function that needs access to listing meta
		//		- Parameters: 	None
		//		- Returns: 		None
		//
		// 		This class creates an access point for any FMS meta requests, so we can treat remote and local listings the same way
		//		During "the_post" hook, we seed all the FMS meta that is available either from the DB or virtually
		//
		// **********************************************************************************************************************************
		
		class FMSListingdata {
			
			private static 	$instance = null;
			private 		$listingdata;
			public			$is_remote;

			public static function get_instance() {
			
				if (is_null(self::$instance)) {
					
					self::$instance = new self();
				} 
				return self::$instance;
			} 
			
			private function __construct() {
				
				// Do nothing...
			}
			
			public function set_listingdata($data = array()) {
				
				$this->listingdata = $data;
			}
			
			public function get_listingdata() {
				
				return $this->listingdata;
			}
			
			public function __get($key) {
	
				// CMB2 stores meta with prefix, but we don't store it that way in memory
				// Make sure that requests containing the prefix still work!
				if (substr($key, 0, strlen(FMS_META_PRE)) == FMS_META_PRE) {
					$key = str_replace(FMS_META_PRE, "", $key);
				}
	
				return (!empty($this->listingdata[$key]) ? $this->listingdata[$key] : false);
			}
		}

		
		// **********************************************************************************************************************************
		//
		// 		fms_apply_remote_filtering
		//
		// 		- Called by: 	fms_the_posts_filter
		//		- Parameters: 	Query vars that were passed to the current WP_Query and the current set of retrieved virtual listings
		//		- Returns: 		The remote listing array, sorted and filtered to match the current WP_Query
		//
		// 		After a set of remote listings has been pulled, take the current query's parameters and filter the remotes accordingly
		//		This ensures that the remote set is ordered and filtered the way the the current query specifies
		//
		//		NOTE: This only works with basic meta query operation (single param, 1 level)
		//
		// **********************************************************************************************************************************
		
		
		function fms_evaluate_meta_query($query_part, $listing) {
			
			$rel = (!empty($query_part["relation"]) ? $query_part["relation"] : "AND");
			$cmp = array();
			
			foreach ($query_part as $key => $val) {

				if ($key !== "relation") {

					if (is_array($val)) {

						$cmp[] = fms_evaluate_meta_query($val, $listing);
					}
					else {
						
						$meta_key = (!empty($query_part["key"]) 	? $query_part["key"] 		: null);
						$meta_val = (!empty($query_part["value"]) 	? $query_part["value"] 		: null);
						$meta_cmp = (!empty($query_part["compare"]) ? $query_part["compare"] 	: null);
						
						if ($meta_key && $meta_cmp) {
							
							/**/
							// Adapted to filter both listing objects and listingdata arrays together!
							
							if (is_array($listing)) {
								
								// Strip the prefix off the key, if it's there
								$meta_key = str_replace(FMS_META_PRE, "", $meta_key);

								// Get the meta value from the listing object
								// Mark it as being "not included" by default
								$listing_val = (!empty($listing[$meta_key]) ? $listing[$meta_key] : "");
							}
							else $listing_val = get_post_meta($listing->ID, $meta_key, true);
							/**/
							
							/*
							// Strip the prefix off the key, if it's there
							$meta_key = str_replace(FMS_META_PRE, "", $meta_key);

							// Get the meta value from the listing object
							// Mark it as being "not included" by default
							$listing_val = (!empty($listing[$meta_key]) ? $listing[$meta_key] : "");
							*/
							
							// If we're trying to match in a group (array) we'll need to serialize first
							if (is_array($listing_val)) $listing_val = json_encode($listing_val);
							
							// Check to see if the current listing should be included based on the meta query
							// This replicates some basic behaviour of the core WP meta query
							switch ($meta_cmp) {
								
								case "=":
								case "==":
								
								if ($listing_val == $meta_val) return 1;
								
								break;
								
								case "!=":
								
								if ($listing_val != $meta_val) return 1;
								
								break;
								
								case ">":
								
								if ($listing_val > $meta_val) return 1;
								
								break;
								
								case "<":
								
								if ($listing_val < $meta_val) return 1;
								
								break;
								
								case ">=":
								case "=>":
								
								if ($listing_val >= $meta_val) return 1;
								
								break;
								
								case "<=":
								case "=<":
								
								if ($listing_val <= $meta_val) return 1;
								
								break;
								
								case "IN":
								case "LIKE":
								
								if (strpos($listing_val, $meta_val) > -1) return 1;
								
								break;
								
								case "EXISTS":
								
								if (!empty($listing_val)) return 1;
								
								break;
								
								case "NOT EXISTS":
								
								if (empty($listing_val)) return 1;
								
								break;
							}
							
							// If nothing stuck, then it's not a match!
							return 0;
						}
						
						// What to do if some basic criteria isn't included?
						// Since this is totally subjective, call it a match anyways...
						return 1;
					}	
				}
			}

			switch ($rel) {
				
				default:
				case "AND":
				
					if (in_array(0, $cmp)) return false;
					else return true;
				
				break;
				
				case "OR":
				
					if (in_array(1, $cmp)) return true;
					else return false;
				
				break;
			}
		}
		
		function fms_apply_remote_filtering($args, $listings) {

			if (!empty($args["meta_query"])) {
				
				$temp = array();
				
				foreach ($listings as $listing) {

					if (fms_evaluate_meta_query($args["meta_query"], $listing) == true) {
						
						$temp[] = $listing;
					}
				}
				
				$listings = $temp;
			}
			
			/**/
			// SORTING
			$key = str_replace(FMS_META_PRE, "", $args["meta_key"]);
			
			if (empty($key)) $key = "last_updated";
			
			$ord = $args["order"];
			
			
			// Sort the listing collection ascending or descending based on the meta query
			// This replicates some basic behaviour of the core WP meta query
			$success = usort($listings, function($a, $b) use ($key, $ord) {
				
				/**/
				// Adapted to sort listing objects and listingdata arrays together!
				
				if (is_array($a)) $a_val = (!empty($a[$key]) ? $a[$key] : "");
				else {
					
					// last_updated can either be the raw value or the post date of an existing post object
					if ($key == "last_updated") $a_val = strtotime($a->post_date);
					else $a_val = get_post_meta($a->ID, FMS_META_PRE.$key, true);
				}
				
				if (is_array($b)) $b_val = (!empty($b[$key]) ? $b[$key] : "");
				else {
					
					// last_updated can either be the raw value or the post date of an existing post object
					if ($key == "last_updated") $b_val = strtotime($b->post_date);
					else $b_val = get_post_meta($b->ID, FMS_META_PRE.$key, true);
				}
				/**/
				
				/*
				$a_val = (!empty($a[$key]) ? $a[$key] : "");
				$b_val = (!empty($b[$key]) ? $b[$key] : "");
			    */
			    
		    	if ($a_val == $b_val) return 0;
		    	
		    	if ($ord == "DESC") return ($a_val > $b_val) ? -1 : 1;
			    if ($ord == "ASC")	return ($a_val < $b_val) ? -1 : 1;
			});
			/**/

			return $listings;
		}


		// **********************************************************************************************************************************
		//
		// 		fms_the_posts_filter + fms_create_virtual_listing_post
		//
		// 		- Called by: 	Action, the_posts
		//		- Parameters: 	2, the returned list of posts from the current query and the query object itself
		//		- Returns: 		The "updated" list of posts 
		//
		//		The idea here is that once the query has run, we can replace the found set with "virtual" listings representing remotes
		//		We run the remmote API call with our GET parameters (if required) and spoof the query with new data (filtered to match)
		//
		// **********************************************************************************************************************************
		
		function fms_create_virtual_listing_post($listingdata) {
			
			// Create a fake post intance
            $vp = new stdClass;
            
            // Fill the fake post with everything a post in the database would have
            $vp->ID 					= -1;                          
            $vp->post_author 			= 1;       						
            $vp->post_date 				= date("Y-m-d H:i:s", $listingdata["last_updated"]);           
            $vp->post_date_gmt 			= date("Y-m-d H:i:s", $listingdata["last_updated"]);
            $vp->post_content 			= $listingdata["remarks"];
            $vp->post_title 			= $listingdata["address"];
            $vp->post_excerpt 			= "";
            $vp->post_status 			= "publish";
            $vp->comment_status 		= "closed";      
            $vp->ping_status 			= "closed";         
            $vp->post_password 			= "";
            $vp->post_name 				= "";
            $vp->to_ping 				= "";
            $vp->pinged 				= "";
            $vp->modified 				= "";
            $vp->modified_gmt 			= "";
            $vp->post_content_filtered 	= "";
            $vp->post_parent 			= 0;
            $vp->guid 					= "";
            $vp->menu_order 			= 0;
            $vp->post_type 				= "fms_listing";
            $vp->post_mime_type 		= "";
            $vp->comment_count 			= 0;

			// Remove the remapped meta
			unset($listingdata["remarks"]);
			unset($listingdata["address"]);

			// The secret sauce
			$vp->remote_listing_meta = $listingdata;

			return $vp;
		}
		
		function fms_the_posts_filter($posts, $query) {

			if (!is_admin()) {

				// Get a reference to the current post type
				$post_type = $query->get("post_type");

				if (
					
					// Post type or category for listings
					($query->get("post_type") === "fms_listing" ||  $query->is_tax("fms_listing_category")) 
					
					|| 
					
					// Listing post type is part of a complex, multi-type query
					(is_array($post_type) && in_array("fms_listing", $post_type))
				
				) {

					// Make sure we always have a type
					// This wil be empty for taxonomy queries!
					if (empty($post_type)) {
						
						$query->set("post_type", "fms_listing");
						$post_type = "fms_listing";
					}
					
					/**/			
					// Get all the default search query strings (this would be where raw opts get parameterized)
					
					//$default_searches = fms_get_option("default_searches", array());		// OLD
					$default_searches = fms_get_option("default_search_group", array());	// NEW
					
				
					// If the option was selected to block defaults from the home page, we turf it here
					if (fms_get_option("hide_default_search_home", false) && is_home() && $query->is_main_query()) {
						$default_searches = null;
					}
					
					// Obviously we don't want default searches messing with the single listings
					if (is_single()) {
						$default_searches = null;
					}
					/**/

					// Shorthand to determine if we are in national pool search mode
					$is_national_search = ((!empty($_GET["pool"]) && $_GET["pool"] == "national") ? true : false);
					
					if ($is_national_search || !empty($default_searches)) {
					
						$remote_listings 	= array();
						$virtual_posts 		= array();


						// For combining member and national listings
						if (!$is_national_search && !empty($default_searches)) {
							
							/* NEW */
							foreach ($default_searches as $search_term) {

								if (!empty($search_term["search_in_category"])) {
								
									$selected_term	= $search_term["search_in_category"];
									$current_term 	= get_query_var("term");
									
									
									if ($selected_term != $current_term) continue;
								}
								

								if (!empty($search_term["search_query"])) {
									
									$result = fms_fetch_national_feed_inline(false, $search_term["search_query"]);
									$remote_listings = array_merge($remote_listings, $result);
								}
							}
							/**/
							
							/* OLD
							foreach ($default_searches as $search_query) {
								
								$result = fms_fetch_national_feed_inline(false, $search_query);
								$remote_listings = array_merge($remote_listings, $result);
							}
							*/
							
							// BAIL! 
							// The search(es) didn't return anything, so just send back the member listings only
							if (empty($remote_listings)) return $posts;
						}
						
						// For just national search listings
						elseif ($is_national_search) {
						
							// Try to get the listing data from the session (only if the queries match!)
							if (!empty($_SESSION["fms_ddf_search"]) && $_SESSION["fms_get_string"] == $_SERVER["QUERY_STRING"]) {
								
								$remote_listings = $_SESSION["fms_ddf_search"];
							}
								
							// If they're not there, re-run the search (all query vars will be passed)
							if (empty($remote_listings)) {
	
								//$remote_listings = fms_fetch_national_feed_inline(true);	// FOR TESTING 
								$remote_listings = fms_fetch_national_feed_inline();
							}
						}
						
						if (count($remote_listings) > 0) {
	
							$separate = fms_get_option("show_member_listings_first", false);
	
							// Sort the search content first, before merging member content (separate member and national)
							if ($separate) $remote_listings = fms_apply_remote_filtering($query->query_vars, $remote_listings);
							
							/**/
							// Fetch all member listings and prepend to the default search results (for combined member / national only!)
							// NOTE: we *can* do this first because the filtering functions are tuned to filter / sort WP objects and arrays togehter
							// Calling this first will merge the sorting of member and default search (and blog post too)
							// Calling it second will result in member feed and blog content appearing first (separated)
							
							if (!$is_national_search && !empty($default_searches)) {

								// This might be a better option!
								// Just keep it super super simple...
								$member_listings = $query->posts;
								

								// All of this stuff was misbehaving when filters were applied, and mixed content was "separated"
								// It may have to do with the query parameters not getting copied to get posts properly (but I'm not sure)
								// The simpler method above seems to work, and is less expensive, so I'll leave it for now and see what happens.
								
								/*
								$member_args = array(
								
									"post_type"			=> $post_type,
									"posts_per_page" 	=> -1,
								);
								
								// Apply some parameters from the current query to the get_posts call
								// This is useful if we're "separating" so the member feed listings can maintain the correct order
								
								if (isset($query->query_vars["order"])) {
									$member_args["order"] = $query->query_vars["order"];
								}
								
								if (isset($query->query_vars["orderby"])) {
									$member_args["orderby"] = $query->query_vars["orderby"];
								}
								
								if (isset($query->query_vars["meta_key"])) {
									$member_args["meta_key"] = $query->query_vars["meta_key"];
								}
								
								
								// If we're querying  listing category specifically, make sure the term is set...
								
								if (isset($query->query_vars["fms_listing_category"])) {
									$member_args["fms_listing_category"] = $query->query_vars["fms_listing_category"];
								}
								
								
								// Make sure to include the current meta query too!
								$member_args["meta_query"] = $query->get("meta_query");
	
								// Fetch the member feed listings from the WP DB and merge them with our remote listings
								//$member_listings = get_posts($member_args);
								*/
								
								
								
								
								
								// Do a quick pass through and remove any duplicates from the remote listing set before merging
								// Duplicates will get removed unless the option to explicitly include them is checked!
								
								if (!fms_get_option("include_default_search_duplicates", false)) {
								
									$member_mls_numbers = array();
									
									foreach ($member_listings as $member_listing) {
											
										$member_mls_numbers[] = get_post_meta($member_listing->ID, FMS_META_PRE."mls_number", true);
									}
									
									foreach ($remote_listings as $key => $remote_listing) {
										
										$remote_mls = $remote_listing["mls_number"];
										
										if (!empty($remote_mls) && in_array($remote_mls, $member_mls_numbers)) {
											
											unset($remote_listings[$key]);
											$remote_listings = array_values($remote_listings);
										}
									}
								}
								
								// Merge the member and remote listing groups togehter
								$remote_listings = array_merge($member_listings, $remote_listings);	
							}
							/**/
							
							// Sort everyhting togeher!
							if (!$separate) $remote_listings = fms_apply_remote_filtering($query->query_vars, $remote_listings);



							// Get the pagination parameters from the query
							if 		($query->get("paged")) $paged = $query->get("paged");
							elseif 	($query->get("page"))  $paged = $query->get("page");
							else 	$paged = 1;
							
							// Figure out how many posts are allowed on the page
							$ppp = $query->get("posts_per_page");
							
							// Override some of the existing parameters to make pagination work properly
							$query->found_posts 	= count($remote_listings);
							$query->max_num_pages 	= ceil($query->found_posts / $ppp);
							
							
							// Figure out which "page" of rmote listings we're on (only if paging is required!)
							if ($ppp > -1) {
								$remote_listings = array_slice($remote_listings, (($paged - 1) * $ppp), $ppp);
							}

							foreach ($remote_listings as $listing) {

								// Add the fake post to the fake posts array
								// NOTE: Again, we have to filter out the ones that are post objects already...
								// The solution here will be to adapt the sync and filter to accommodate post objects properly
								
								if (!is_object($listing)) $virtual_posts[] = fms_create_virtual_listing_post($listing);
								else $virtual_posts[] = $listing;
							}

							// If there is only one fake post, mark the query as a single
							if (count($virtual_posts) == 1) {

								$query->is_single				= true;
								$query->is_singular 			= true;
								$query->is_archive 				= false;
								$query->is_post_type_archive 	= false;
							}
							
							// If there are sevaral fake posts, mark the query as an archive
							else {

								$query->is_single				= false;
								$query->is_singular 			= false;
								$query->is_archive 				= true;
								$query->is_post_type_archive 	= true;
							}
							
							
							// Special rule, preserve the taxonomy status
							if ($query->is_tax) {
								
								$query->is_single				= false;
								$query->is_singular 			= false;
								$query->is_archive 				= false;
								$query->is_post_type_archive 	= false;
							}
							
							
							
			                // Remove errors and any 404 states that might be present
			                unset($query->query["error"]);
			                $query->query_vars["error"] = "";
			                $query->is_404 = FALSE; 	
			                
			                // No comment!
			                $query->current_comment = 0;
			                $query->comment_count	= 0;
						}
						
						// Return the array of fake posts
						$posts = $virtual_posts;
					}
					
					else if (!empty($_GET["pool"]) && $_GET["pool"] == "member") {
					
						// TODO
						// Consider adding local search filtering using the same params available to remote	
					}
					
					else {
						
						// DO NOTHING
						// Just let WP do its thing for cached listing posts and archives	
					}					
				}
			}
			
			return $posts;
		}
		add_filter("the_posts", "fms_the_posts_filter", 10, 2);
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_the_wp_action
		//
		// 		- Called by: 	Action, wp
		//		- Parameters: 	None
		//		- Returns: 		None
		//
		//		The point of this is just to get the API seeded with some data for the current post right away
		// 		This is so that actions that occur outside of the loop on singles will sill work
		//
		// **********************************************************************************************************************************
		
		function fms_the_wp_action() {
			
			global $post;
			fms_the_post_action($post);
		}
		add_action("wp", "fms_the_wp_action");
		

		// **********************************************************************************************************************************
		//
		// 		fms_the_post_action
		//
		// 		- Called by: 	Action, the_post
		//		- Parameters: 	Auto, the current post object
		//		- Returns: 		None
		//
		//		This will set up the FMSListingdata singleton class to hold all of the FMS meta for the current listing
		//		Whether the listing is from the local DB or the remote gateway, the metadata is homogenized so we can access it nicely
		//
		// **********************************************************************************************************************************
		
		function fms_the_post_action($post_object) {
			
			if (!is_admin()) {
			
				if ($post_object && $post_object->post_type === "fms_listing") { 
					
					$api = FMSListingdata::get_instance();
					
					if (!empty($post_object->remote_listing_meta)) {
						
						// Add the pseudo meta from the remote listing to the accessor API
						$api->set_listingdata($post_object->remote_listing_meta);
						$api->is_remote = true;
					}
					
					else {
						
						// Try to get the meta for the current post
						$listing_meta = get_post_meta($post_object->ID);
						
						// Filter out the correct meta values
						foreach ($listing_meta as $key => $val_array) {
								
							// Strip the meta key off the front to clean the data
							$key = str_replace(FMS_META_PRE, "", $key);
								
							// Because this is how WP store its meta...
							$val = $val_array[0];
							
							// If the data is stored in serialized form (CMB2 arrays)
							// Unserialize it before storing in memory!
							$unser = @unserialize($val);
							if ($unser !== false) $val = $unser;

							// Build the new pseudo-meta array
							$temp[$key] = $val;
						}
						
						// Add the cleaned meta from the local listing to the accessor API
						$api->set_listingdata($temp);
						$api->is_remote = false;
					}				
				}
			}
		}
		add_action("the_post", "fms_the_post_action");
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_listing_thumbnail_filter
		//		fms_listing_meta_filter
		//		fms_attachment_image_src
		//
		// 		- Called by: 	Filter, post_thumbnail_html, get_post_metadata, wp_get_attachment_image_src
		//		- Parameters: 	Various...
		//		- Returns: 		Various...
		//
		//		Basically we're just making sure that the featured or thumbnail image is never returned for remote listings
		//		Because of the way the queries get spoofed, it's sometimes possible for other posts' FI to sneak in
		//		We can avoid that by cutting it out all together since there's no way to set it anyhow...
		//
		// **********************************************************************************************************************************
			
		function fms_listing_thumbnail_filter($html, $post_id, $post_thumbnail_id, $size) {
			
			global $post;
			
			if (!is_admin()) {
			
				if ($post && $post->post_type === "fms_listing" && fms_is_remote()) {
			
					$html = "";
				}
			}		
			return $html;
		}
		add_filter("post_thumbnail_html", "fms_listing_thumbnail_filter", 10, 4);

		function fms_listing_meta_filter($metadata, $object_id, $meta_key, $single) {
			
			global $post;
			
			if (!is_admin()) {
			
				if ($post && $post->post_type === "fms_listing" && fms_is_remote()) {

					if ($meta_key === "_thumbnail_id") {

						$metadata = false;	
					}
				}
			}
			return $metadata;
		}
		add_filter("get_post_metadata", "fms_listing_meta_filter", 10, 4);
		
		function fms_attachment_image_src($image, $attachment_id, $size, $icon) {
			
			global $post;
			
			if (!is_admin()) {
			
				if ($post && $post->post_type === "fms_listing" && fms_is_remote()) {

					return array();	
				}
			}
			return $image;
		}
		add_filter("wp_get_attachment_image_src", "fms_attachment_image_src", 10, 4);

		
		// **********************************************************************************************************************************
		//
		// 		fms_edit_post_link_filter
		//
		// 		- Called by: 	Filter, edit_post_link
		//		- Parameters: 	1, the requested post edit link
		//		- Returns: 		The filtered link
		//
		//		Since we can't edit virtual or remote posts, make sure the edit link is never displayed!
		//
		// **********************************************************************************************************************************
		
		function fms_edit_post_link_filter($link) {
			
			global $post;
			
			if (!is_admin()) {
			
				if ($post && $post->post_type === "fms_listing") {
					
					if ($post->ID == -1) $link = "";
				}
			}
			return $link;
		}
		add_filter("edit_post_link", "fms_edit_post_link_filter", 10, 1);
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_next_post_link_filter
		//		fms_previous_post_link_filter
		//
		// 		- Called by: 	Filter, next_post_link, previous_post_link
		//		- Parameters: 	Various...
		//		- Returns: 		Various...
		//
		//		The next and previous post logic is really weird and hard to pull off with remote listings
		//		So for now, we just return nothing when WP asks for that link
		//
		// **********************************************************************************************************************************
		
		function fms_next_post_link_filter($output, $format, $link) {
			
			global $post;
			
			if (!is_admin()) {
			
				if ($post && $post->post_type === "fms_listing") {
					
					if ($post->ID == -1) $output = "";
				}
			}
			return $output;
		}
		add_filter("next_post_link", "fms_next_post_link_filter", 10, 3);
		
		function fms_previous_post_link_filter($output, $format, $link) {
			
			global $post;
			
			if (!is_admin()) {

				if ($post && $post->post_type === "fms_listing") {
					
					if ($post->ID == -1) $output = "";
				}
			}
			return $output;
		}
		add_filter("previous_post_link", "fms_previous_post_link_filter", 10, 3);


		// **********************************************************************************************************************************
		//
		// 		fms_the_content
		//
		// 		- Called by: 	Filter, the_content
		//		- Parameters: 	The current post content
		//		- Returns: 		Filtered content
		//
		//		Replace the requested post content with output from the listing single or archive content template (depending on context)
		//		This allows us to display a bunch of listing data "out of the box" when content is requested
		//
		//		NOTE: Developers should instead opt to build their own templates and not use the_content tag
		//
		// **********************************************************************************************************************************
		
		function fms_the_content($content) {
			
			global $post;
			
			if (!is_admin()) {
			
				if ($post && $post->post_type === "fms_listing") { 
					
					remove_filter("the_content", "fms_the_content");
					
					// Use the output buffer to patch in our content template
					// This will trigger whenever the excerpt is requested for an fms_listing
					ob_start();
					
					if (is_single()) {
						
						// Look for a developer defined template first
						$template = locate_template("fms-templates/something.php");
						if (!empty($template)) include ($template);
						else {
							
							//include (FMS_DIR ."/public-ui/fms-tpl-api-tests.php");	// FOR TESTING 
							include (FMS_DIR ."/public-ui/fms-tpl-the-content.php");
						}
					}

					else {
						
						// Look for a developer defined template first
						$template = locate_template("fms-templates/something.php");
						if (!empty($template)) include ($template);
						else {
							
							include (FMS_DIR ."/public-ui/fms-tpl-the-excerpt.php");
						}
					}

					$content = ob_get_clean();

					add_filter("the_content", "fms_the_content");
				}
			}
			return $content;
		}
		add_filter("the_content", "fms_the_content");
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_the_excerpt
		//
		// 		- Called by: 	Filter, the_excerpt
		//		- Parameters: 	The current post excerpt
		//		- Returns: 		Filtered excerpt
		//
		//		Replace the requested post excerpt with output from the listing excerpt template
		//		This allows us to display a bunch of listing data "out of the box" when the excerpt is requested
		//
		//		NOTE: Developers should instead opt to build their own templates and not use the_excerpt tag
		//
		// **********************************************************************************************************************************
		
		function fms_the_excerpt($excerpt) {
			
			global $post;
			
			if (!is_admin()) {
			
				if ($post && $post->post_type === "fms_listing") { 
					
					remove_filter("the_excerpt", "fms_the_excerpt");
					
					// Use the output buffer to patch in our excerpt template
					// This will trigger whenever the excerpt is requested for an fms_listing
					ob_start();
					
					// Look for a developer defined template first
					$template = locate_template("fms-templates/something.php");
					if (!empty($template)) include ($template);
					else {
					
						include (FMS_DIR ."/public-ui/fms-tpl-the-excerpt.php");
					}

					$excerpt = ob_get_clean();

					add_filter("the_excerpt", "fms_the_excerpt");
				}
			}
			return $excerpt;
		}
		add_filter("the_excerpt", "fms_the_excerpt");

		
		// **********************************************************************************************************************************
		//
		// 		fms_the_permalink
		//
		// 		- Called by: 	Filter, post_type_link, fms_get_permalink
		//		- Parameters: 	The current requested link (optional)
		//		- Returns: 		Filtered remote link
		//
		//		Replace the currently requested permalink with one that contains the correct filter parameters (for remote listings)
		//
		// **********************************************************************************************************************************
		
		function fms_the_permalink($link = "") {
			
			global $post;
			
			if (empty($link)) $link = get_the_permalink();

			if (!is_admin() || (defined("DOING_AJAX") && DOING_AJAX)) {
			
				$api = FMSListingdata::get_instance();

				if ($post && $post->post_type === "fms_listing" && $api->is_remote === true) { 
					
					// Replace the link with a remote single query for live listings								
					$link = add_query_arg(array(
						
						"pool"			=> "national",
						"mlsNumber"		=> $api->mls_number,
						"postal"		=> $api->postal_code,
						
					), get_post_type_archive_link("fms_listing"));
				}
			}
			return $link;
		}
		add_filter("post_type_link", "fms_the_permalink");
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_the_permalink
		//
		// 		- Called by: 	Filter, post_type_link, fms_get_permalink
		//		- Parameters: 	The current requested link (optional)
		//		- Returns: 		Filtered remote link
		//
		//		Replace the currently requested permalink with one that contains the correct filter parameters (for remote listings)
		//
		// **********************************************************************************************************************************
		
		function fms_insert_clickwrap() {
			
			global $post;
			
			if (!is_admin()) {
			
				$api = FMSListingdata::get_instance();
				
				if ($post && $post->post_type === "fms_listing" && $api->is_remote === true) {

					// Check to make sure the admin hasn't disabled clickwrap injection
					if (!fms_get_option("disable_clickwrap", false) && is_single()) {

						include (FMS_DIR ."/public-ui/fms-tpl-crea-clickwrap.php");
					}
				}
				
				// STUFF FOR CREA ANALYTICS!
				// Insert the analytics package for single listing views from the remote gateway
				if ($post && $post->post_type === "fms_listing" && $api->is_remote === true && is_single()) {
				
					include (FMS_DIR ."/public-ui/fms-tpl-crea-analytics.php");
				}	
			}
		}
		add_action("wp_footer", "fms_insert_clickwrap");
	}
	
	// END FMS-LISTING-SETUP
	
?>