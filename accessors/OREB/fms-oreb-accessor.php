<?php defined("ABSPATH") or die("Access denied.") ?>
<?php
	
	// EXTRA REQUIREMENTS
	require_once ("inc/phrets.php");	
	
	// THIS IS CRUCIAL
	// Add this accessor to the global space.
	// Effectively what "installs" the accessor.
	
	$GLOBALS["fms_accessor_classes"][] = "fms_oreb_accessor";

	class fms_oreb_accessor extends fms_accessor {
		
		private $oreb_rets_url = "http://rets.oreb.mlxmatrix.com/RETS/login.ashx";
		
		private $oreb_username;
		private $oreb_password;
		
		private $oreb_rets_username;
		private $oreb_rets_password;
		
		private $oreb_agent_id;

		protected function init() {
			
			$this->accessor_name = __("OREB", "firstlook");
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

				$this->oreb_username 	= fms_get_option("oreb_username");
				$this->oreb_password	= fms_get_option("oreb_password");
				
				$this->oreb_rets_username 	= fms_get_option("oreb_rets_username");
				$this->oreb_rets_password	= fms_get_option("oreb_rets_password");

				$this->oreb_agent_ids 	= array_filter(explode(",", preg_replace("/\s+/", "", strtoupper(fms_get_option("oreb_agent_ids")))));
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
			
				"before_row"	=> "<h1>OREB IDX Feed Settings</h1>
									<p>
									Please note that these fields are only usable if your brokerage has an IDX agreement with OREB and Firstlook Media Solutions INC.
									<br>
									For more information, please contact your office administrator.
									</p>",
			
				"name"      	=> __("IDX Username", "firstlook"),
			
				"id"       	 	=> "oreb_username",
				"type"      	=> "text",
				
			));
			$cmb->add_field(array(
			
				"name"      	=> __("IDX Password", "firstlook"),
			
				"id"       	 	=> "oreb_password",
				"type"      	=> "text",
				
				"attributes" 	=> array(
					
					"type" 		=> "password",
				),
				
				"after_row"		=> "<hr>",
				
			));
			$cmb->add_field(array(
			
				"name"      	=> __("RETS User Agent", "firstlook"),
			
				"id"       	 	=> "oreb_rets_username",
				"type"      	=> "text",
				
			));
			$cmb->add_field(array(
			
				"name"      	=> __("RETS Password", "firstlook"),

				"id"       	 	=> "oreb_rets_password",
				"type"      	=> "text",
				
				"attributes" 	=> array(
					
					"type" 		=> "password",
				),
				
				"after_row"		=> "<hr>",
				
			));
			$cmb->add_field(array(
			
				"name"      	=> __("Agent ID", "firstlook"),
			
				"id"       	 	=> "oreb_agent_ids",
				"type"      	=> "text",
				
				"after_row"		=> "<br>",
			));	
		}
		
		public function fms_render_options_form($form_key) {
				
			cmb2_metabox_form($this->accessor_id, $form_key, array(
			
				"save_button" => __("Save OREB IDX Settings", "firstlook"),
				
			));
		}		
		
		public function fms_save_options_notice() {
			
			return __("OREB Feed Settings updated", "firstlook");
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
			
			if (!empty($this->oreb_agent_ids)
			&&	!empty($this->oreb_username) 
			&&  !empty($this->oreb_password) 
			&&  !empty($this->oreb_rets_username) 
			&&  !empty($this->oreb_rets_password)) {

				$this->log_msg("Fetching member feed(s): ". implode(", ", array_filter($this->oreb_agent_ids)));
				$this->log_msg("Scanning OREB Database.");
				
				// Search all provided IDs
				$listingdata = $this->fms_search_oreb();
				if (!empty($listingdata)) {

					$this->log_msg("Found ".count($listingdata)." total results.");

					$results = array();
					foreach($listingdata as $raw_listing) {

						$mapping = $this->fms_get_new_meta($raw_listing);

						// Do not map any cancelled or expired listings!
						// They can be set to status "none" like all the others
						// Really we just want the sold and leased info to come through
						
						if ($mapping["status"] != fms_listing_statuses::$CANCELLED
						 && $mapping["status"] != fms_listing_statuses::$EXPIRED) {
							 
							$results[] = $mapping;	 
						}
						
						else {
							
							$this->log_msg("OREB listing with status ". $mapping["status"] ." will be ignored.");
						}
					}

					$this->log_msg("OREB fetch complete!");
					return $results;
				}
				else {
					
					$this->log_msg("OREB fetch returned no data.");
					return;
				}

			}
			else {
				
				$this->log_msg("OREB feed credentials missing.");
				return;
			}
		}
		// END fms_fetch_member_feed

	
		// **********************************************************************************************************************************
		//
		// 		fms_fetch_national_feed
		//		STUB ONLY
		//
		// **********************************************************************************************************************************
		
		public function fms_fetch_national_feed($search_params = "") {

			// STUBB STUBB STUBB
		}
		// END fms_fetch_national_feed
		

		// **********************************************************************************************************************************
		//
		// 		fms_search_oreb
		//
		// 		- Called by: 	fms_fetch_member_feed
		//		- Parameters: 	None
		//		- Returns: 		Array of data released from OREB or null
		//
		// 		Paw through the OREB database like a bear going through a ripe dumpster!
		//
		// **********************************************************************************************************************************
	

		private function fms_rets_connect() {
			
			if (!empty($this->oreb_agent_ids)
			&&	!empty($this->oreb_username) 
			&&  !empty($this->oreb_password) 
			&&  !empty($this->oreb_rets_username) 
			&&  !empty($this->oreb_rets_password)) {
			
				// Close any connection that might still be open
				if (isset($this->rets)) $this->fms_rets_disconnect();
			
				// Make the connection to the RETS server and run the search query
				$this->rets = new phRETS();
				$this->rets->AddHeader("User-Agent", $this->oreb_rets_username);
				
				$connect = $this->rets->Connect($this->oreb_rets_url, $this->oreb_username, $this->oreb_password, $this->oreb_rets_password);
				
				if (!$connect) {
					
					$this->log_msg("The OREB RETS server is unavailable...");
					$this->log_msg("Error: ". $this->rets->Error());	
				} 
			}
			else {
				
				$this->log_msg("Missing OREB RETS credentials.");
			}
		}
		
		private function fms_rets_disconnect() {
			
			if (isset($this->rets)) {
			
				$this->rets->Disconnect();
				unset($this->rets);
			}
		}
	
		private function fms_search_oreb() {
			
			date_default_timezone_set("America/Toronto");
			
			// Get the date that we should start retrieving from
			$time = new DateTime("now");
			$start_time = $time->modify("-1 year")->format("Y-m-d\TH:i:s");
			
			// Last modified and list agent fields to retrieve
			$lm_field = "MatrixModifiedDT";
			$la_field = array("ListAgentMLSID", "CoListAgentMLSID", "CoListAgent2MLSID");
			
			// Flatten the list of agent IDs
			$ids = implode(",", array_filter($this->oreb_agent_ids));
			
			// Do some funky mapping so that the agent IDs are associates with the LA fields... I guess!
			$agent_clause_arr = array();
			foreach ($la_field as $field) {
				
				$agent_clause_arr[] = "({$field}={$ids})";
			}
			$agent_clause = implode(" | ", array_filter($agent_clause_arr));
			
			/**/
			// Construct the RETS base query string
			
			$this->fms_rets_connect();
			$query = "(({$lm_field}={$start_time}+),($agent_clause))";
			
			$listingdata_base = array();
			$listings_search = $this->rets->SearchQuery("Property", "Listing", $query, array (
				
				"Limit" 	=> 1000,
				"Count" 	=> 1,
				"Format" 	=> "COMPACT-DECODED",
			));

			if ($this->rets->NumRows () > 0) {
				while ($record = $this->rets->FetchRow($listings_search)) {
					$listingdata_base[] = $record;
				}
			}
			$this->rets->FreeResult($listings_search);
			$this->fms_rets_disconnect();
			/**/
			
			$listingdata = array();
			
			foreach ($listingdata_base as $listing) {
				
				// Don't include cancelled or expired listings!
				if ($raw_listing["Status"] != "Cancelled" && $raw_listing["Status"] != "Expired") {
				
					$sysid = $listing["Matrix_Unique_ID"];
					
					/**/
					// Get the photos for this listing
					// The trick here is that OREB only allows downloading of photo data directly (no URLs)
					// First, we download all of the photos for a listing temporarily and save the URLs
					// Then, we can use the normal sync process in the parent class the usual way
					// This is not the most efficiant process, but necessary for integration until OREB will give us URLs
					
					$this->fms_rets_connect();
					$photos = array();
					
					$raw_photos = $this->rets->GetObject("Property", "LargePhoto", $listing["Matrix_Unique_ID"]);
	
					unset($raw_photos[0]);
	
					$listingdir = ($this->photo_dir."/".$sysid."/latest");
					$listingurl = ($this->photo_url."/".$sysid."/latest");
					
					if (!is_dir($listingdir)) wp_mkdir_p($listingdir);
					
					foreach ($raw_photos as $raw_photo) {
						
						$filename = $sysid."_".$raw_photo["Object-ID"].".jpg";
						
						$new_file_dir = ($listingdir."/".$filename);
						$new_file_url = ($listingurl."/".$filename);
						
						$success = file_put_contents($new_file_dir, $raw_photo["Data"]);
						
						if ($success) $photos[] = $new_file_url;
					}
	
					$this->fms_rets_disconnect();
					$listing["Photos"] = $photos;
					/**/
					
					/**/
					// Get rooms for this listing
					
					$this->fms_rets_connect();
					$query = "(Listing_MUI={$sysid})";
					
					$rooms = array();
					$rooms_search = $this->rets->SearchQuery("PropertySubTable", "Room", $query, array (
						
						"Limit" 	=> 1000,
						"Count" 	=> 1,
						"Format" 	=> "COMPACT-DECODED",
					));
					
					if ($this->rets->NumRows() > 0) {
						while ($record = $this->rets->FetchRow($rooms_search)) {
							$rooms[] = $record;
						}
					}
					$this->rets->FreeResult($rooms_search);
					$this->fms_rets_disconnect();
					$listing["Rooms"] = $rooms;
					/**/
	
					/**/
					// Get open house info for this listing
					
					$this->fms_rets_connect();
					$query = "(Listing_MUI={$sysid})";
					
					$openhouse = array();
					$openhouse_search = $this->rets->SearchQuery ("OpenHouse", "OpenHouse", $query, array (
						
						"Limit" 	=> 1000,
						"Count" 	=> 1,
						"Format" 	=> "COMPACT-DECODED",
					));
					
					if ($this->rets->NumRows() > 0) {
						while ($record = $this->rets->FetchRow($openhouse_search)) {
							$rooms[] = $record;
						}
					}
					$this->rets->FreeResult($openhouse_search);
					$this->fms_rets_disconnect();
					$listing["OpenHouse"] = $openhouse;
					/**/
					
					$listingdata[] = $listing;
				}
			}
			
			return $listingdata;
		}
		

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
	
				$listing_key = $raw_listing["Matrix_Unique_ID"];
	
				// Get the raw photo urls
				$new_photos = array();
				if (!empty($raw_listing["Photos"])) {
	
					foreach ($raw_listing["Photos"] as $photo) {
						$new_photos[] = array(
							
							"url"		=> $photo,
							"show"		=> true,
							"remote"	=> true,
						);
						
						// NOTE: These will be the URLs of the photos we cached locally above (latest copies)
						// We will be copying the ones we want to keep in the next step, assuming photo updates aren't blocked
						// All temp photos we don't keep will be deleted in the cleanup phase 
					}
				}				
				
				// Get the rooms
				$new_rooms = array();
				if (!empty($raw_listing["Rooms"])) {
					
					foreach ((array)$raw_listing["Rooms"] as $room) {
						$new_rooms[] = array(
						
							"room_name"			=> $room["RoomType"],
							"room_level"		=> $room["RoomLevel"],
							"room_dimensions"	=> $room["Dimension1"]." x ".$room["Dimension2"],
						);
					}
				}
				
				// Get the open houses
				$new_openhouses = array();
				if (!empty($raw_listing["OpenHouse"])) {
				
					foreach ((array)$raw_listing["OpenHouse"] as $openhouse) {
						
						$oreb_date 		 = date("Y-m-d", 	strtotime($openhouse["OpenHouseDate"]));
						$oreb_start_time = date("\TH:i:s", 	strtotime($openhouse["StartTime"]));
						$oreb_end_time 	 = date("\TH:i:s", 	strtotime($openhouse["EndTime"]));
						
						$new_openhouses[] = array(
							
							"openhouse_start"		=> strtotime($oreb_date.$oreb_start_time),
							"openhouse_end"			=> strtotime($oreb_date.$oreb_end_time),
							"openhouse_description"	=> $openhouse["Description"],
						);
					}
				}
							
				// Determine status and price			
				$raw_price = $raw_listing["ListPrice"];
				$raw_lease = $raw_listing["LeaseRate"];
				
				if (!empty($raw_lease)) {
					
					$price 	= $raw_lease;
					$status = fms_listing_statuses::$ACTIVE_LEASE; 
					
					// NOTE:
					// This is really only appropriate for commercial listings
					// For residential, things get a little strange (see bleow)
				
				} else {
					
					$price	= $raw_price;
					$status	= fms_listing_statuses::$ACTIVE_SALE;
				}
				
				
				
				
				
				// ***
				// Ok... here's some ass-backwards logic for ya...
				// In Matrix, listings that are "for sale" have a field "Available For Rent" and rentals have "Available For Sale" (mandatory field)
				// Since they decided not to include an "isRental" field (or equivalent), we can assume that if a listing has no "rental" field, it's a rental.
				// This will have to do for now!
				
				// FORE RESIDENTIAL RENTAL / LEASE LISTINGS
				
				if ($raw_listing["AvailableforLeaseYN"] == "" && $raw_listing["AvailableforSaleYN"] != "") {
					
					$price 	= $raw_price;
					$status = fms_listing_statuses::$ACTIVE_LEASE;
				}
		
				// ***
				
				
				
				
				// Additional statuses (different from DDF!)
				switch ($raw_listing["Status"]) {
					
					case "Conditional Sale":
					$status = fms_listing_statuses::$CONDITIONAL;
					break;
					
					case "Sold":
					$status = fms_listing_statuses::$SOLD;
					break;
					
					case "Rented":
					case "Leased":
					$status = fms_listing_statuses::$LEASED;
					break;
					
					/**/
					// We'll include these for completeness, but they should never actually be used!
					
					case "Cancelled":
					$status = fms_listing_statuses::$CANCELLED;
					break;
					
					case "Expired":
					$status = fms_listing_statuses::$EXPIRED;
					break;
					
					/**/
				}
				
				// Determine neighbourhood / community
				$neighborhood = $raw_listing["NeighbourhoodName"];
				
				if (empty($neighborhood)) {
					
					$neighborhood = "";
				}
				
				// Send back the new meta array
				$mapping = array(
				
					// FIELDS WITH NO META EDITOR
					"address"				=> $raw_listing["StreetNumber"]." ".$raw_listing["StreetName"]." ".$raw_listing["StreetSuffix"],
					"remarks"				=> $raw_listing["PublicRemarks"],
					
					"closing_date"			=> strtotime($raw_listing["CloseDate"]),
					"taxes"					=> $raw_listing["Taxes"],
					"tax_year"				=> $raw_listing["TaxYear"],
					"square_footage"		=> $raw_listing["TotalAvailSqftforGencom"] ." sq ft",
					
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
					
					"last_updated"			=> strtotime($raw_listing["MatrixModifiedDT"]),
	
					// SUPPORTED CONTENT RAW DATA
	
					"beds"					=> $raw_listing["BedsTotal"],
					"baths"					=> str_replace(".00", "", $raw_listing["BathsTotal"]),
					"year_built"			=> $raw_listing["YearBuilt"],
					"flooring"				=> $raw_listing["FloorCovering"],
					"heating"				=> $raw_listing["HeatDesc"],
					"lot_size"				=> $raw_listing["LotSizeImpFrontage"]." ft x ".$raw_listing["LotSizeImpDepth"]." ft",
					"parking"				=> $raw_listing["TotalParking"],
					"inclusions"			=> "", // See below
					"exclusions"			=> $raw_listing["Exclusions"],
					
					"property_type"			=> $raw_listing["PropertyType"],
					"zoning"				=> $raw_listing["ZoningDescription"],
					
					"city"					=> $raw_listing["City"],
					"province"				=> "Ontario",
					"postal_code"			=> $raw_listing["PostalCode"],
					"latitude"				=> $raw_listing["Latitude"],
					"longitude"				=> $raw_listing["Longitude"],
					"directions"			=> $raw_listing["Directions"],
					
					"dwelling_type"			=> $raw_listing["TypeofDwelling"],
					"building_style"		=> $raw_listing["StyleofDwelling"],
					"heating_fuel"			=> $raw_listing["HeatingFuel"],
					"ac_type"				=> $raw_listing["AirConditioningDesc"],
					"parking_type"			=> $raw_listing["ParkingDesc"],
					"garage_spaces"			=> $raw_listing["NumberofGarageSpaces"],
					
					"lease_rate"			=> $raw_listing["LeasePriceRatePer"],
					"tenant_pays"			=> $raw_listing["TenantPays"],
					"amenities"				=> $raw_listing["Amenities"],
					
					"condo_fee"				=> $raw_listing["CondoFee"],
					"fee_includes"			=> $raw_listing["FeeIncludes"],
					
					"mls_number" 			=> $raw_listing["MLSNumber"],
					"listing_agent_1"		=> $raw_listing["ListAgentMLSID"],
					"listing_agent_2"		=> $raw_listing["CoListAgentMLSID"],
					"listing_agent_3"		=> $raw_listing["CoListAgent2MLSID"],
					"brokerage_code"		=> $raw_listing["ListOfficeMLSID"],
					"presented_by"			=> $raw_listing["ListOfficeName"],
				);
				
				/**/
				// Add the unit number (if available)
				if(!empty($raw_listing["UnitNumber"])) {
					$mapping["address"] .= " Unit#".$raw_listing["UnitNumber"];  
				}
				/**/
				
				/**/
				// Combining included appliances and equipment.
				if ($raw_listing["AppliancesIncluded"] != "") {
					$mapping["inclusions"] = $raw_listing["AppliancesIncluded"];
				}
				if ($raw_listing["FeaturesEquipmentIncluded"] != "") {
					
					if ($mapping["inclusions"] != "") {
						$mapping["inclusions"] = "{$mapping['inclusions']},{$raw_listing['FeaturesEquipmentIncluded']}";
					} 
					else {
						$mapping["inclusions"] = $raw_listing["FeaturesEquipmentIncluded"];
					}
				}
				/**/

				return $mapping;
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
			
			// Delete all the photos from the temp directory that we created earlier
			// This will get called before any of the master cleanup routines take place
			
			$photo_folders = glob($this->photo_dir."/*", GLOB_ONLYDIR);
			foreach ($photo_folders as $folder) {
			
				$latest = $folder."/latest";

				array_map("unlink", glob($latest."/*"));
				rmdir($latest);	
			}
			
			$this->log_msg("OREB cleanup finished.");
		}
	}
?>