<?php defined("ABSPATH") or die("Access denied.") ?>
<?php
	
	// THIS IS CRUCIAL
	// Add this accessor to the global space.
	// Effectively what "installs" the accessor.
	
	$GLOBALS["fms_accessor_classes"][] = "fms_ddf_accessor";

	class fms_ddf_accessor extends fms_accessor {
		
		private $ddf_member_ids;
		private $ddf_national_ids;
		
		private $ddf_account_id;
		private $ddf_license_key;
		
		
		protected function init() {
			
			$this->accessor_name = __("CREA DDF®", "firstlook");
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_accessor_options
		//		fms_create_options_form
		//		fms_render_options_form
		//		fms_save_options_notice
		//
		// 		- Called by: 	Inline, during admin form creation, during construction
		//		- Parameters: 	None, master form key (for saving options)
		//		- Returns: 		None, Output inline HTML, text for update notices
		//
		// 		Handle integration with CMB2 options framework for this accessor type
		//
		// **********************************************************************************************************************************
		
		protected function fms_get_accessor_options() {
			
			if (function_exists("fms_get_option")) {

				$this->ddf_member_ids 	= array_filter(explode(",", preg_replace("/\s+/", "", fms_get_option("ddf_member_ids"))));
				$this->ddf_national_ids = array_filter(explode(",", preg_replace("/\s+/", "", fms_get_option("ddf_national_ids"))));

				$this->ddf_account_id 	= fms_get_option("ddf_account_id");
				$this->ddf_license_key	= fms_get_option("ddf_license_key");
			}
		}

		public function fms_create_options_form($form_key) { 
			
			$cmb = new_cmb2_box(array(
				
				"id"         	=> $this->accessor_id,
				"hookup"     	=> false,
				"cmb_styles" 	=> false,
				"show_on"   	=> array(
					
					"key"   	=> "options-page",
					"value" 	=> array($form_key),
				),
			));
			$cmb->add_field(array(
			
				"before_row"	=> "<h1>CREA DDF® Feed Settings</h1>",
				
				"name"      	=> __("(Optional) DDF® Member Feed ID", "firstlook"),
				"desc"			=> __("CREA DDF® Member Feed ID.<br>
									This is emailed to you by CREA when you set up a DDF® member feed.", "firstlook"),
			
				"id"       	 	=> "ddf_member_ids",
				"type"      	=> "text",
				
			));
			$cmb->add_field(array(
			
				"name"      	=> __("(Optional) DDF® National Shared Pool Feed ID", "firstlook"),
				"desc"			=> __("CREA DDF® National Shared Pool Feed ID.<br>
									This is emailed to you by CREA when you set up a DDF® national shared pool feed.", "firstlook"),
			
				"id"       	 	=> "ddf_national_ids",
				"type"      	=> "text",
				
				"before_row"	=> "<hr/>",
				
			));
			$cmb->add_field(array(
			
				"name"      	=> __("Firstlook Account ID", "firstlook"),
			
				"id"        	=> "ddf_account_id",
				"type"      	=> "text",
				
				"desc"			=> __("Long string of letters, numbers, and underscores starting with \"ACCT_\"", "firstlook"),
				
				"before_row"	=> "<hr/>",
				
			));
			$cmb->add_field(array(
			
				"name" 			=> __("Retriever License Key", "firstlook"),
			
				"id" 			=> "ddf_license_key",
				"type"			=> "text",
				
				"desc"			=> __("Need a license key? Visit the <a target='_blank' href='https://store.myfirstlook.ca'>Firstlook Store.</a><br>
									Account ID and License Key are emailed to you when you subscribe to a Retriever plan.", "firstlook"),
			
				"after_row"		=> "<br>",
				
			));
			
		}
		
		public function fms_render_options_form($form_key) {
				
			cmb2_metabox_form($this->accessor_id, $form_key, array(
			
				"save_button" => __("Save DDF Feed Settings", "firstlook"),
				
			));
		}		
		
		public function fms_save_options_notice() {
			
			return __("DDF Feed Settings updated", "firstlook");
		}

		
		// **********************************************************************************************************************************
		//
		// 		fms_fetch_member_feed
		//
		// 		- Called by: 	PUBLIC
		//		- Parameters: 	None
		//		- Returns: 		Array of data created by fms_create_listing_post
		//
		// 		Search the DDF mirror and process the results
		//		The returned data should be suitable for cached fms_listing single display
		//
		// **********************************************************************************************************************************
		
		public function fms_fetch_member_feed() {

			if (!empty($this->ddf_account_id) && !empty($this->ddf_license_key) && !empty($this->ddf_member_ids)) {

				$this->log_msg("Fetching member feed(s): ". implode(", ", array_filter($this->ddf_member_ids)));
				$this->log_msg("Scanning DDF Mirror.");
				
				// Compile the search URL tails from the ids
				$searches = array();
				foreach ($this->ddf_member_ids as $member_id) {
					
					$searches[] = "member/". $member_id;
				}
				
				
				/**/
				// Member feed retry system
				// If there are any failures, try the full fetch again until $max_retries is exceeded
				
				$max_retries = 3;
				
				for ($i = 0; $i < $max_retries; $i++) {
					
					// If we're not on the first retry cycle
					if ($i != 0) {
						
						$c = ($i + 1);
						
						$this->log_msg("Retrying [Attempt $c / $max_retries]");
						$this->clean_fetch = true;
					}
					
					// Search all provided IDs
					$listingdata = $this->fms_search_gateway($searches);
					
					if ($this->clean_fetch == true) break;
				}
				/**/

				
				if (!empty($listingdata)) {

					$this->log_msg("Found ".count($listingdata)." total results.");

					$results = array();
					foreach($listingdata as $raw_listing) {

						$results[] = $this->fms_get_new_meta($raw_listing);
					}

					$this->log_msg("DDF fetch complete!");
					return $results;
				}
				else {
					
					$this->log_msg("DDF fetch returned no data.");
					return;
				}
			}
			else {
				
				$this->log_msg("DDF feed credentials missing.");
				return;
			}
		}
		// END fms_fetch_member_feed

	
		// **********************************************************************************************************************************
		//
		// 		fms_fetch_national_feed
		//
		// 		- Called by: 	PUBLIC
		//		- Parameters: 	None
		//		- Returns: 		Array of data created by fms_create_live_listing
		//
		// 		Search the DDF mirror and process the results
		//		The returned data should be suitable for live display (not cached)
		//
		// **********************************************************************************************************************************
		
		public function fms_fetch_national_feed($search_params = "") {

			if (!empty($this->ddf_account_id) && !empty($this->ddf_license_key) && !empty($this->ddf_national_ids)) {
			
				// Get the search parameters from either the arg or from the server query string
				if (empty($search_params) && !empty($_SERVER["QUERY_STRING"])) $search_params = $_SERVER["QUERY_STRING"];
				if (empty($search_params)) {
					
					$this->log_msg("Missing search parameters.");
					return;
				}
			
				// Make sure that the args are formatted with the leading question mark
				if (substr($search_params, 0, 1) != "?") $search_params = "?". $search_params;
			
				// Compile the search URL tails from the ids
				$searches = array();
				if (count($this->ddf_national_ids) > 0) {
					
					// Only search on 1 national ID (the first one entered)
					$national_id 	= $this->ddf_national_ids[0];
					$searches[] 	= "national/". $national_id . $search_params;
				}
				
				// Get listing data from the gateway
				$listingdata = $this->fms_search_gateway($searches);
				
				if (!empty($listingdata)) {
					
					$results = array();
					foreach ($listingdata as $raw_listing) {
						
						$results[] = $this->fms_get_new_meta($raw_listing);
					}

					$this->log_msg("DDF National Feed OK!");
					return $results;
				}
				else {
						
					$this->log_msg("DDF Search returned no data.");
					return;
				}
			}
			else {
				
				$this->log_msg("DDF feed credentials are missing.");
				return;
			}
		}
		// END fms_fetch_national_feed
		

		// **********************************************************************************************************************************
		//
		// 		fms_login_get_token, fms_search_gateway
		//
		// 		- Called by: 	fms_search_gateway and fms_fetch_member_feed, fms_fetch_national_feed (respectively)
		//		- Parameters: 	FMS/RPM account id and license key, array of URL tails for national or member endpoints
		//		- Returns: 		RPM system auth token or null, data released from gateway or null
		//
		// 		Use the login_get_token function to start a system session for pulling data. 
		//		Then, search the list of target URL tails to pull active listing data.
		//
		// **********************************************************************************************************************************
		
		private function fms_login_get_token($id, $key) {

			if (!empty($id) && !empty($key)) {
				
				$key_parts = explode("_", $key);
				if (count($key_parts) == 2) {
					
					$uname = $key;				// Username is the whole key
					$pword = $key_parts[0];		// Password is the first part, before the dash
					
					$args = array(
						
						"headers" => array(
							
							"Authorization" => "Basic ". base64_encode("$uname:$pword"),
							"Content-Type" 	=> "application/json",
						),
					);
					$res = wp_remote_get("http://access.myfirstlook.ca/rpm-api/login/$id", $args);
					//$res = wp_remote_get("http://172.99.67.86/rpm-api/login/$id", $args);

					if (is_array($res) && $res["response"]["code"] == 200) {
						
						$data = json_decode($res["body"], true);
						
						$_SESSION["ddf_access_token"] = $data["token"];
						return $data["token"];
					}
					
					else {
						
						$err = (is_array($res) ? $res["response"]["code"] : $res->get_error_message());
						
						$this->log_msg("Authorization failed! [$err]");
						$this->clean_fetch = false;
					}
				}
			}
			return null;
		}

		private $ct = 0;

		private function fms_search_gateway($searches) {
			
			$id		= $this->ddf_account_id;
			$key 	= $this->ddf_license_key;
			
			//$token  = $_SESSION["ddf_access_token"];

			// Create a new token if the existing one is empty or we're forcing a new one to be created
			//if (empty($token)) $token = $this->fms_login_get_token($id, $key);
			
			// For now, we'll just generate a new token every time. Slower, but more secure...
			$token = $this->fms_login_get_token($id, $key);


			$key = strtolower($key);


			// Once we have a cached token, we can go to work
			if (!empty($token)) {

				
				$args = array(
					
					"headers" => array(
							
						"RPM-SessionToken"	=> $token,
						"Content-Type" 		=> "application/json",
					),
				);
				$res = wp_remote_get("http://access.myfirstlook.ca/rpm-api/AccountManager/$id/accounts/$id/users/$key", $args);
				//$res = wp_remote_get("http://172.99.67.86/rpm-api/AccountManager/$id/accounts/users/$key", $args);
				
				

				
				
				/*
				
				if (is_array($res) && $res["response"]["code"] == 200) {
					
					$data = json_decode($res["body"], true);
					
					// Get the domain name of the current install
					$domain = (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"]);
					
					// Get the license key's valid IP and the current install's IP 
					$ip 	= gethostbyname($data["firstName"].".");
					$addr 	= gethostbyname($domain.".");
					
					if ($ip == $addr) {
					*/
						$listingdata = array();
						
						foreach ($searches as $search) {

							$args = array(
								
								"headers" => array(
							
									"RPM-SessionToken"	=> $token,
									"Content-Type" 		=> "application/json",
								),
							);
							$res = wp_remote_get("http://access.myfirstlook.ca/rpm-api/ddfSvcV2/$id/property/listings/$search", $args);
							//$res = wp_remote_get("http://172.99.67.86/rpm-api/ddfSvcV2/property/listings/$search", $args);
						
							
							if (is_array($res) && $res["response"]["code"] == 200) {
							
								$data = json_decode($res["body"], true);
							
								foreach ($data as $entry) $listingdata[] = $entry;
							}
							
							else {
								
								$err = (is_array($res) ? $res["response"]["code"] : $res->get_error_message());
								
								$this->log_msg("Failed to get listings from gateway! [$err]");
								$this->clean_fetch = false;
							}
						}
						return $listingdata;
					/*
					}
					
					else {
						
						$this->log_msg("License domain mismatch!");
						$this->clean_fetch = false;
					}
				}
				
				else {
					
					$err = (is_array($res) ? $res["response"]["code"] : $res->get_error_message());
					
					$this->log_msg("Could not get license information! [$err]");
					$this->clean_fetch = false;
				}
				*/				
			}
			return null;
		}
		// END fms_login_get_token, fms_search_gateway
		

		// **********************************************************************************************************************************
		//
		// 		fms_get_new_meta
		//
		// 		- Called by: 	fms_create_listing_post, fms_create_live_listing
		//		- Parameters: 	Array of raw listing data from the accessor
		//		- Returns: 		Array of meta values
		//
		// 		Create a mapping between the raw DDF output data and the WP meta structure
		//		For a list of meta fields see /inc/fms-register-editor-meta.php
		//
		// **********************************************************************************************************************************
		
		private function fms_get_new_meta($raw_listing) {

			if (defined("FMS_META_PRE")) {
	
				$listing_key = $raw_listing["tombstone"]["propertyKey"];
	
				// Get the raw photo urls
				$new_photos = array();
				if (!empty($raw_listing["photos"])) {
	
					foreach ($raw_listing["photos"] as $photo) {
						$new_photos[] = array(
							
							"url"		=> $photo["fileUrl"],
							"show"		=> true,
							"remote"	=> true,
						);
						
						// NOTE: These will be the URLs to access the photos on the CDN
						// If we're caching, these photos will be downloaded and the URLs replaced in the next step
					}
				}				
				
				// Get the rooms
				$new_rooms = array();
				if (!empty($raw_listing["rooms"])) {
					
					foreach ((array)$raw_listing["rooms"] as $room) {
						$new_rooms[] = array(
						
							"room_name"			=> $room["rmType"],
							"room_level"		=> $room["level"],
							"room_dimensions"	=> $room["dimension"],
						);
					}
				}
				
				// Get the open houses
				$new_openhouses = array();
				if (!empty($raw_listing["openHouse"])) {
				
					foreach ((array)$raw_listing["openHouse"]["events"] as $openhouse) {
						$new_openhouses[] = array(
							
							"openhouse_start"		=> strtotime($openhouse["startDateTime"]),
							"openhouse_end"			=> strtotime($openhouse["endDateTime"]),
							"openhouse_description"	=> $openhouse["comments"],
						);
					}
				}
							
				// Determine status and price			
				$raw_price = $raw_listing["tombstone"]["price"];
				$raw_lease = $raw_listing["rental"]["rentalPrice"];
				
				if (!empty($raw_lease)) {
					
					$price 	= $raw_lease;
					$status = fms_listing_statuses::$ACTIVE_LEASE; 
				
				} else {
					
					$price	= $raw_price;
					$status	= fms_listing_statuses::$ACTIVE_SALE;
				}
				
				// Determine neighbourhood / community
				$neighborhood = $raw_listing["location"]["neighborhood"];
				
				if (empty($neighborhood)) {
					
					$neighborhood = $raw_listing["location"]["community"];
					
					// TODO: Get this to work.
					// Will require additional logic on the gateway side.
				}
				if (empty($neighborhood)) {
					
					$neighborhood = "";
				}
				
				// Send back the new meta array
				return array(
				
					// FIELDS WITH NO META EDITOR
					"address"				=> $raw_listing["location"]["address"],		// Mapped to post title
					"remarks"				=> $raw_listing["tombstone"]["remarks"],	// Mapped to post content
				
					// UNSUPPORTED FIELDS (FOR NOW...)
					
					"closing_date"			=> "",
					"taxes"					=> "",
					"tax_year"				=> "",
					"square_footage"		=> "",
					
					// NOTE: We also don't have support for virtual tour / multimedia / additional photos yet!
				
					// UNSUPPORTED GROUPS (FOR NOW...)
					
					"media_videos_group"	=> array(),
					"media_tours_group"		=> array(),
					"media_links_group"		=> array(),
				
					// SUPPORTED CONTENT SPECIAL VARS
				
					"listing_key"			=> $listing_key,
				
					"data_source"			=> $this->accessor_name,
					"data_source_id"		=> $this->accessor_id,
				
					"status"				=> $status,
					"price"					=> $price,
					
					"neighborhood"			=> $neighborhood,
					
					"photos"				=> $new_photos,
					"rooms_group"			=> $new_rooms,
					"openhouse_group"		=> $new_openhouses,
					
					"last_updated"			=> strtotime($raw_listing["tombstone"]["lastUpdated"]),
	
					// SUPPORTED CONTENT RAW DATA
	
					"beds"					=> $raw_listing["tombstone"]["bedrooms"],
					"baths"					=> $raw_listing["tombstone"]["bathrooms"],
					"year_built"			=> $raw_listing["tombstone"]["yearBuilt"],
					"flooring"				=> $raw_listing["tombstone"]["flooring"],
					"heating"				=> $raw_listing["tombstone"]["heating"],
					"lot_size"				=> $raw_listing["tombstone"]["lotSize"],
					"parking"				=> $raw_listing["tombstone"]["parking"],
					"inclusions"			=> $raw_listing["tombstone"]["inclusions"],
					"exclusions"			=> $raw_listing["tombstone"]["exclusions"],
					
					"property_type"			=> $raw_listing["location"]["propertyType"],
					"zoning"				=> $raw_listing["location"]["zoning"],
					
					"city"					=> $raw_listing["location"]["city"],
					"province"				=> $raw_listing["location"]["province"],
					"postal_code"			=> $raw_listing["location"]["postalCode"],
					"latitude"				=> $raw_listing["location"]["latitude"],
					"longitude"				=> $raw_listing["location"]["longitude"],
					"directions"			=> $raw_listing["location"]["directions"],
					
					"dwelling_type"			=> $raw_listing["details"]["dwellingType"],
					"building_style"		=> $raw_listing["details"]["style"],
					"heating_fuel"			=> $raw_listing["details"]["heatingFuel"],
					"ac_type"				=> $raw_listing["details"]["acDesc"],
					"parking_type"			=> $raw_listing["details"]["parkingDesc"],
					"garage_spaces"			=> $raw_listing["details"]["numGarageSpace"],
					
					"lease_rate"			=> $raw_listing["rental"]["rentPerTime"],
					"tenant_pays"			=> $raw_listing["rental"]["tenantPays"],
					"amenities"				=> $raw_listing["rental"]["amenities"],
					
					"condo_fee"				=> $raw_listing["condo"]["condoFee"],
					"fee_includes"			=> $raw_listing["condo"]["condoFeeIncludes"],
					
					"mls_number" 			=> $raw_listing["listingInfo"]["mlsNumber"],
					"listing_agent_1"		=> $raw_listing["listingInfo"]["listAgentCode1"],
					"listing_agent_2"		=> $raw_listing["listingInfo"]["listAgentCode2"],
					"listing_agent_3"		=> $raw_listing["listingInfo"]["listAgentCode3"],
					"brokerage_code"		=> $raw_listing["listingInfo"]["brokerageCode"],
					"presented_by"			=> $raw_listing["listingInfo"]["brokerageId"],
				);
			}
		} 
		// END fms_get_new_meta	
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_fetch_cleanup
		//
		// 		- Called by: 	fms_cleanup
		//		- Parameters: 	n/a
		//		- Returns: 		n/a
		//
		// 		A last opportunity to clean anything that needs to be cleaned before the sync process completes
		//		Called at the start of the cleanup process
		//
		// **********************************************************************************************************************************
		
		public function fms_fetch_cleanup() {
			
			$this->log_msg("DDF cleanup finished.");
		}
	}
?>