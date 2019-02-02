<?php defined("ABSPATH") or die("Access denied.") ?>
<?php

	$GLOBALS["fms_accessor_classes"] 	= array();
	$GLOBALS["fms_accessor_log"]		= array();

	abstract class fms_accessor {
		
		public $accessor_id;	// ID of the accessor will be auto-set to match the class name
		public $accessor_name;	// Default name (should be set in init function)
		
		protected $photo_dir;	// System directory where listing photos are stored
		protected $photo_url;	// URL to the photo directory
		
		protected $clean_fetch;	// Did the current fetch exit without errors?

		// **********************************************************************************************************************************
		//
		// 		Public constructor
		//
		// **********************************************************************************************************************************
		
		public function __construct() {
			
			$this->accessor_id 		= get_class($this);
			$this->accessor_name 	= "FMS Accessor";

			$this->init();
			$this->fms_get_accessor_options();
			
			if (defined("FMS_PHOTO_DIR") && defined("FMS_PHOTO_URL")) {

				$this->photo_dir = (FMS_PHOTO_DIR."/".$this->accessor_id);
				$this->photo_url = (FMS_PHOTO_URL."/".$this->accessor_id);
			}
			
			$this->clean_fetch = true; // True until proven otherwise!	
		}
		// END __construct
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_cache_wp_listings
		//
		// 		- Called by: 	PUBLIC
		//		- Parameters: 	Array of listing objects where keys corresponds to fms_listing meta fields (minus the prefix!)
		//		- Returns: 		None
		//
		// 		Turn raw data from the accessor into cached fms_listing post objects
		//
		// **********************************************************************************************************************************
		
		public function fms_cache_wp_listings($new_listing_data, $dl_photos = true) {

			$touched_listing_ids = array();
			
			// Make sure that the fetch exited cleanly before caching or updating anything!
			if ($this->clean_fetch == true) {
				
				if (defined("FMS_META_PRE")) {
	
					if (!empty($new_listing_data)) {
	
						$this->log_msg("Caching listings...");
		
						$all_listings = get_posts(array("post_type" => "fms_listing", "numberposts" => -1, "fms_unlock_query" => true));
						
						foreach ($new_listing_data as $listingdata) {
							

							
							
							$will_update	= false;
							
							$new_mls 		= $listingdata["mls_number"];
							$new_key		= $listingdata["listing_key"];
							$new_address 	= $listingdata["address"];
							$new_remarks 	= $listingdata["remarks"];
							
							foreach ($all_listings as $listing) {
								
								$id = $listing->ID;
								
								$old_mls = get_post_meta($id, FMS_META_PRE."mls_number", true);
								$old_key = get_post_meta($id, FMS_META_PRE."listing_key", true);
								
								// We'll update an old listing here
								if ($new_key == $old_key) {
									
									$this->log_msg("Updating listing $new_address (MLS-$new_mls, KEY-$new_key, ID-$id)");
				
									$touched_listing_ids[] = $id;
				
									$block_all 		= get_post_meta($id, FMS_META_PRE."block_sync_all", 	true);
									$block_remarks	= get_post_meta($id, FMS_META_PRE."block_sync_remarks", true);
									$block_address	= get_post_meta($id, FMS_META_PRE."block_sync_address", true);
									$block_status	= get_post_meta($id, FMS_META_PRE."block_sync_status", 	true);
									$block_photos	= get_post_meta($id, FMS_META_PRE."block_sync_photos", 	true);
									
									if (!$block_all) {
				
										// Update the post time with the MLS timestamp
										wp_update_post(array(
												
											"ID"			=> $id,
											"post_date"		=> date("Y-m-d H:i:s", $listingdata["last_updated"]),
										));
				
										// Update the public remarks if updates haven't been blocked
										if (!$block_address) {
											wp_update_post(array(
												
												"ID"			=> $id,
												"post_title"	=> $new_address,
											));
										}
										else $this->log_msg("Address on MLS-$new_mls is being blocked and will not update.");	
				
										// Update the public remarks if updates haven't been blocked
										if (!$block_remarks) {
											wp_update_post(array(
												
												"ID"			=> $id,
												"post_content"	=> $new_remarks,
											));
										}
										else $this->log_msg("Public remarks on MLS-$new_mls are being blocked and will not update.");
										
										// Update the post meta
										foreach ($listingdata as $key => $val) {
			
											if ($block_status && $key == "status") {
												
												// Don't update status if we said not to...
												$this->log_msg("Listing status updates on MLS-$new_mls are blocked. Status will not change.");
											}
											
											if ($block_photos && $key == "photos") {
												
												// Don't update photos if we said not to...
												$this->log_msg("Listing photos updates on MLS-$new_mls are blocked. Photos will not change.");
											}
			
											else update_post_meta($id, FMS_META_PRE.$key, $val);
										}
									}
									else $this->log_msg("Updates on MLS-$new_mls are being blocked. Listing will not be updated.");
									
									if ($dl_photos) {
										$this->fms_download_photos($id);
									}
									
									// Cache the listing photos assuming that:
									// 1) They are explicitly marked for download by the caller
									// 2) They haven't been downloaded already by the member feed fetch process	
									/*	
									if ($dl_photos && !$listingdata["photos_prefetched"]) {
										$this->fms_download_photos($id);
									}
									*/
									
									$will_update = true;
									break;
								}
							}
							
							// We'll create a new listing here
							if (!$will_update) {
								
								$this->log_msg("Creating new listing post for $new_address (MLS-$new_mls, KEY-$new_key)");
								
								// Create the new post object
								$id = wp_insert_post(array( 
								
									"post_title"	=> $new_address,
									"post_name"		=> sanitize_title_with_dashes($new_address),
									
									"post_content"	=> $new_remarks,
									"post_type" 	=> "fms_listing",
									
									"post_date"		=> date("Y-m-d H:i:s", $listingdata["last_updated"]),
									
								));
		
								$touched_listing_ids[] = $id;
		
								// Update the post meta
								foreach ($listingdata as $key => $val) {
									update_post_meta($id, FMS_META_PRE.$key, $val);	
								}
								
								// Publish the new content
								wp_publish_post($id);
								
								$this->log_msg("New ID created (ID-$id)");
								
								if ($dl_photos) {
									$this->fms_download_photos($id);
								}
								
								// Cache the listing photos assuming that:
								// 1) They are explicitly marked for download by the caller
								// 2) They haven't been downloaded already by the member feed fetch process	
								/*					
								if ($dl_photos && !$listingdata["photos_prefetched"]) {
									$this->fms_download_photos($id);
								}
								*/
							}
						}
					}
					else {
						
						$this->log_msg("No listings to cache... Continuing.");
					}
					
					// Run the cleanup routines
					$this->fms_cleanup($touched_listing_ids);
				}
			}
			
			// Fetch did not come out clean...
			else $this->log_msg("Fetch did not exit cleanly, listings will not be altered.");
		}
		// END fms_cache_wp_listings

		
		// **********************************************************************************************************************************
		//
		// 		fms_download_photos
		//
		// 		- Called by: 	fms_cache_wp_listings
		//		- Parameters: 	post ID to update.
		//		- Returns: 		None
		//
		//		Download all the associated liting photos to the target directories and update the URLs in the photo meta
		//
		// **********************************************************************************************************************************
		
		private function fms_download_photos($id) {
			
			if (defined("FMS_META_PRE")) {
			
				$mls = get_post_meta($id, FMS_META_PRE."mls_number", true);
				$key = get_post_meta($id, FMS_META_PRE."listing_key", true);
				
				if (!empty($mls) && !empty($key)) {
					
					$this->log_msg("Syncing photos for listing MLS-$mls (KEY-$key, ID-$id).");
							
					$block_photos = get_post_meta($id, FMS_META_PRE."block_sync_photos", true);
					if (!$block_photos) {
						
						// Target folder based on the system's listing ID
						$listingdir = ($this->photo_dir."/".$key);
						$listingurl = ($this->photo_url."/".$key);
						
						// Make sure that the folder exists
						if (!is_dir($listingdir)) wp_mkdir_p($listingdir);
	
						// Take all the old target URLs and download the files
						$targets = get_post_meta($id, FMS_META_PRE."photos", true);
						$new_photos = array();
						foreach ($targets as $target) {
							
							// Source filename and target download URL
							$source_fileurl	 = $target["url"];
							$source_filename = array_pop(explode("/", $source_fileurl));
								
							// Cached system filename and URL (WP directory)
							$new_fileurl  = ($listingurl."/".$source_filename);
							$new_filename = ($listingdir."/".$source_filename);
		
							// Download the photo from the CDN and add the new URL to the array
							$success = file_put_contents($new_filename, file_get_contents($source_fileurl));

							if ($success !== false) {
								
								$new_photos[] = array(
							
									"url"		=> $new_fileurl,
									"show"		=> true,
									"remote"	=> true,
								);
							}
						}
						
						$this->log_msg("Downloaded ". count($new_photos) ." photos.");
						
						// Update the photo meta to reflect the new cached URLs
						update_post_meta($id, FMS_META_PRE."photos", $new_photos);
					} 
					else {
						
						$this->log_msg("Photo updates on MLS-$mls are blocked, updated photos will not be downloaded.");
					}
				}
			}
		}
		// END fms_download_photos
		

		// **********************************************************************************************************************************
		//
		// 		fms_cleanup
		//
		// 		- Called by: 	PUBLIC
		//		- Parameters: 	Array of listing fms_listing posts (freshly created)
		//		- Returns: 		None
		//
		// 		Run several cleanup routines: 
		//		- Remove status from listings missing from the fetch.
		//		- Delete photos for removed posts.
		//
		// **********************************************************************************************************************************
		
		public function fms_cleanup($new_listing_ids) {
			
			$this->fms_fetch_cleanup(); // Run the cleanup routine for the current accessor
			
			if (defined("FMS_META_PRE")) {
			
				$all_keys = array();
			
				$this->log_msg("Running cleanup routines.");
				
				$all_listings = get_posts(array("post_type" => "fms_listing", "numberposts" => -1 ));
	
				foreach ($all_listings as $listing) {
					
					$id = $listing->ID;
					
					$mls = get_post_meta($id, FMS_META_PRE."mls_number", true);
					$key = get_post_meta($id, FMS_META_PRE."listing_key", true);
					$src = get_post_meta($id, FMS_META_PRE."data_source_id", true);
					
					// If there's no key, it means we're dealing with an exclusive listing
					// And we should never mess with exclusive lisings!
					if (!empty($key) && $src == $this->accessor_id) {
					
						// If an existing post isn't in the new set of results...
						if (!in_array($id, $new_listing_ids)) {
							
							$this->log_msg("Listing MLS-$mls (KEY-$key, ID-$id) is no longer available for syncing.");
							
							// Flip the post status to none if a status block isn't in place
							if (!get_post_meta($id, FMS_META_PRE."block_sync_status", true)) {
								
								$this->log_msg("It will be hidden, but not removed from your site.");
								
								update_post_meta($id, FMS_META_PRE."status", fms_listing_statuses::$NONE);
							}
							else {
								
								$this->log_msg("Status sync block on MLS-$mls enabled, listing status will be preserved.");
							}
							
							// TODO: Any other considerations for hiding a listing?
						} 
					}
					
					// Save this key so we can use it for photo cleanup
					$all_keys[] = $key;
				}
	
				// Remove any photos for listings that no longer exist in the system
				$this->log_msg("Cleaning up photo folders.");
				
				if (!empty($this->photo_dir)) {
					
					$photo_folders = glob($this->photo_dir."/*", GLOB_ONLYDIR);
					foreach ($photo_folders as $folder) {
						
						$key = array_pop(explode("/", $folder));
						
						if (!in_array($key, $all_keys)) {
							
							$this->log_msg("Listing KEY-$key is no longer in the database, so its photos will be deleted.");
							
							array_map("unlink", glob($folder."/*"));
							rmdir($folder);
						}
					}				
				}
				
				$this->log_msg("Cleanup finished.");
			}
		}
		// END fms_cleanup


		// **********************************************************************************************************************************
		//
		// 		logging functions
		//
		// 		- Called by: 	Mixed public / protected
		//		- Parameters: 	Single log message text
		//		- Returns: 		None
		//
		// 		Helper functions: Manage the global access log
		//
		// **********************************************************************************************************************************

		public function log_msg($message) {
			
			if (!is_array($GLOBALS["fms_accessor_log"])) $this->clear_access_log();
			
			array_push($GLOBALS["fms_accessor_log"], array(
				
				"timestamp"		=> time(),
				"message"		=> $message,
			));
		}
		
		public function clear_access_log() {
		
			$GLOBALS["fms_accessor_log"] = array();
		}
		
		public function print_access_log($write_to_file = true) {

			// If there is no data to show, abort
			if (!is_array($GLOBALS["fms_accessor_log"])) {
				
				$this->clear_access_log();
				return;	
			}
			
			// Echo the log content (messages only)
			foreach ($GLOBALS["fms_accessor_log"] as $entry) {
					
				$msg = $entry["message"];
				
				if (is_array($msg)) print_r($msg);
				else {
						
					echo ($entry["message"] ."\n");
				}
			}

			// Make sure we have access to all the file system stuff (running from cron)
			require_once (ABSPATH."wp-admin/includes/file.php");

			// Print the log content to file (formatted)
			$access_type = get_filesystem_method();
			
			if ($write_to_file && $access_type == "direct") {

				$logfile = FMS_DIR."/fms-access-log";

				$creds = request_filesystem_credentials(site_url()."/wp-admin/", "", false, false, array());
			
				if (!WP_Filesystem($creds)) return false;	

				global $wp_filesystem;
				
				if (!file_exists($logfile)) $wp_filesystem->put_contents($logfile, "", FS_CHMOD_FILE);

				$bytes = 0;	
			
				foreach ($GLOBALS["fms_accessor_log"] as $entry) {
					
					$msg = $entry["message"];
					
					if (is_array($msg)) $msg = json_encode($msg);
						
					$text = (date("Y-m-d H:i:s", $entry["timestamp"]) ."\t\t". $msg ."\n");
					$bytes += intval(file_put_contents($logfile, $text, FILE_APPEND));	
				}
				
				// Add a new line after each entry set
				$bytes += intval(file_put_contents($logfile, "\n", FILE_APPEND));
				
				// Check if data was actually written
				if ($bytes == 0) {
					
					// No data written...
				}
			}
			
			elseif ($write_to_file) {
				
				echo "WordPress does not currently have file write permission.\n";
				echo "Access log will not be written to file.\n";
			}

			// Clear and reset the log
			$this->clear_access_log();
		}
		// END logging functions
		

		// **********************************************************************************************************************************
		//
		// 		Abstract methods
		//
		// **********************************************************************************************************************************

		abstract protected function init();
		abstract protected function fms_get_accessor_options();
		
		// Admin option forms
		abstract public function fms_create_options_form($form_key);
		abstract public function fms_render_options_form($form_key);
		abstract public function fms_save_options_notice();
		
		// Core accessor behaviour
		abstract public function fms_fetch_member_feed();
		abstract public function fms_fetch_national_feed($search_params);
		abstract public function fms_fetch_cleanup();
	}
	

	// USER FACING FUNCTIONS - FOR MEMBER FEEDS
	
	// **********************************************************************************************************************************
	//
	// 		fms_fetch_member_feed
	//
	// 		- Called by: 	Anybody
	//		- Parameters: 	Boolean flag to enable member feed caching (default to false)
	//		- Returns: 		Array of listingdata objects (raw data)
	//
	// 		Run a member feed query against the FMS DFF gateway and return the raw data.
	//		If caching is enabled, listing data will be converted to fms_listing posts in the WP database and photos downloaded.
	//
	// **********************************************************************************************************************************
	
	function fms_fetch_member_feed($will_cache = false) {
		
		$results = array();
		foreach ($GLOBALS["fms_accessor_classes"] as $accessor_class) {

			// Create a new accessor and get to work
			$accessor = new $accessor_class;
			$accessor->log_msg("> Starting ". $accessor->accessor_name ." Member Feed sync.");

			// Get the raw listing data from the accessor source
			$listings = $accessor->fms_fetch_member_feed();
			
			// Save the returned listing as a WP post in the database
			// This gives us full editor control and permanence, fully integrated with WP
			if ($will_cache == true) $accessor->fms_cache_wp_listings($listings, true);

			// Show the access logs (will write to file by default)
			$accessor->log_msg($accessor->accessor_name ." Member Feed sync complete!\n");
			$accessor->print_access_log();
			
			// Destroy the accessor
			unset($accessor);
			
			// Save the results
			if (!empty($listings)) $results = array_merge($listings, $results);
		}
		
		return $results;
	}
	

	// **********************************************************************************************************************************
	//
	// 		fms_fetch_member_feed_inline, fms_fetch_member_feed_ajax
	//
	// 		Wrappers for fms_fetch_member_feed.
	//		fms_fetch_member_feed_inline: Allow suppression of the output buffer, used when querying listings synchronously
	//		fms_fetch_member_feed_ajax: Same as above but facilitates raw data transfer over AJAX
	//
	// **********************************************************************************************************************************
	
	function fms_fetch_member_feed_inline($echo_ob = false, $will_cache = false) {
		
		//ob_start();
		
		$results 	= fms_fetch_member_feed($will_cache);
		//$output 	= ob_get_clean();
		
		//if ($echo_ob == true) echo $output;
		return $results;
	}
	function fms_fetch_member_feed_ajax() {
		
		$echo_json 		= (empty($_POST["echo_json"]) 	? false : true);
		$echo_ob		= (empty($_POST["echo_ob"]) 	? false : true);
		$will_cache		= (empty($_POST["will_cache"])  ? false : true);
		
		$results = fms_fetch_member_feed_inline($echo_ob, $will_cache);
		
		if ($echo_json) echo json_encode($results);		
		exit();
	}
	add_action("wp_ajax_fms_fetch_member_feed", "fms_fetch_member_feed_ajax");
	
	
	// USER FACING FUNCTIONS - FOR NATIONAL FEEDS
	
	// **********************************************************************************************************************************
	//
	// 		fms_fetch_member_feed
	//
	// 		- Called by: 	Anybody
	//		- Parameters: 	None
	//		- Returns: 		Array of listingdata objects (raw data)
	//
	// 		Run a national feed query against the FMS DFF gateway and return the raw data.
	//		If possible, data will be stored in the server session for faster data retrieval.
	//
	// **********************************************************************************************************************************
	
	function fms_fetch_national_feed($query = "") {

		$results = array();
		foreach ($GLOBALS["fms_accessor_classes"] as $accessor_class) {

			// Create a new accessor and get to work
			$accessor = new $accessor_class;
			
			// Get the search query
			if (empty($query)) $query = $_SERVER["QUERY_STRING"];
			
			// Get the raw data from the accessor source
			$listings = $accessor->fms_fetch_national_feed($query);
			
			// Save the current search query so we can refer to it later
			$_SESSION["fms_get_string"] = $query;
			
			// Clear out any session information that might be lingering
			unset($_SESSION["fms_ddf_search"]);
			
			if (is_array($listings)) {
			
				// Create a temporary cache of listing results
				foreach ($listings as $listing) {
					if (!empty($listing["mls_number"])) {
	
						$_SESSION["fms_ddf_search"][$listing["mls_number"]] = $listing;
					}
				}
			}
			
			// Show access log (do not write to file)
			$accessor->print_access_log(false);
			
			// Destroy the accessor
			unset($accessor);
			
			// Save the results
			if (!empty($listings)) $results = array_merge($listings, $results);
		}
		
		return $results;
	}
	
	
	// **********************************************************************************************************************************
	//
	// 		fms_fetch_national_feed_inline, fms_fetch_national_feed_ajax
	//
	// 		Wrappers for fms_fetch_national_feed.
	//		fms_fetch_national_feed_inline: Allow suppression of the output buffer, used when querying listings synchronously
	//		fms_fetch_national_feed_ajax: Same as above but facilitates raw data transfer over AJAX
	//
	// **********************************************************************************************************************************
	
	function fms_fetch_national_feed_inline($echo_ob = false, $query = "") {

		ob_start();
		
		$results 	= fms_fetch_national_feed($query);
		$output 	= ob_get_clean();
		
		if ($echo_ob == true) echo $output;
		
		return $results;
	}
	
	function fms_fetch_national_feed_ajax() {
				
		$echo_json 	= (empty($_POST["echo_json"]) 	? false : true);
		$echo_ob	= (empty($_POST["echo_ob"]) 	? false : true);
		
		$results = fms_fetch_national_feed_inline($echo_ob);
		
		if ($echo_json) echo json_encode($results);
		exit();
	}
	add_action("wp_ajax_fms_fetch_national_feed", "fms_fetch_national_feed_ajax");

	// END FMS-ACCESSOR
	
?>