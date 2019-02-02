<?php defined("ABSPATH") or die("Access denied.") ?>
<?php

	if (defined("FMS_META_PRE")) {


		// FORMATTER FUNCTIONS
	
		// **********************************************************************************************************************************
		//
		// 		fms_format_money
		//		Get a currency value string prefixed with symbol and rounded to given number of decimal places.
		//
		// **********************************************************************************************************************************
	
		function fms_format_money($value, $decimals = 0, $decimal_sep = ".", $thousands_sep = ",", $symbol = "$") {
			
			if (!empty($value)) {

				// Strip the value down to it's bare essentials, using the provided decimal separator.
				// In case price is provided in Québec or EU formatting, PHP can still do the math.
			
				$value = floatval(str_replace($decimal_sep, ".", preg_replace("/[^0-9". $decimal_sep ."]/", "", $value)));
				
				if (is_numeric($value) && $value > 0) {

					// Do the formatting according to the provided parameters
					return ($symbol . number_format($value, $decimals, $decimal_sep, $thousands_sep));
				}
			}
			return "";
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_format_query_string
		//		Create a query string from an array of parameters
		//
		// **********************************************************************************************************************************
		
		function fms_format_query_string($params = array(), $encode = true) {
			
			// Make sure the flag is set to trigger remotes
			$params["pool"] = "national";
			
			// URL encode the keys and values if required
			if ($encode == true) {
				$temp = array();
				foreach ($params as $key => $val) {
					$temp[urlencode($key)] = urlencode($val);
				}
				$params = $temp;
			}
			
			return add_query_arg($params, "");
		}
	

		// **********************************************************************************************************************************
		//
		// 		fms_format_search_url
		//		Create a search URL that will point to a remote listing search page.
		//
		// **********************************************************************************************************************************
	
		function fms_format_search_url($params = array(), $encode = true) {
			
			$query = fms_format_query_string($params, $encode);

			// Add the parameters to the standard post archive URL
			return get_post_type_archive_link("fms_listing") . $query; //add_query_arg($query, get_post_type_archive_link("fms_listing"));
		}
	

		// WP WRAPPERS
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_remarks
		//		Get the "remarks" data, which is the same as getting the standard post content.
		//
		// **********************************************************************************************************************************
		
		function fms_get_remarks() {

			return get_the_content();
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_remarks_excerpt
		//		Just get a shortened version of the remarks
		//
		// **********************************************************************************************************************************
		
		function fms_get_remarks_excerpt($words = 20) {
		
			$content = strip_tags(fms_get_remarks());
			$excerpt = implode(" ", array_slice(explode(" ", strip_tags($content)), 0, $words));
			
			if (!empty($excerpt) && (strlen($content) > strlen($excerpt))) $excerpt .= "...";
			
			return $excerpt;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_address
		//		Get the "address" data, which is the same as getting the standard post title.
		//
		// **********************************************************************************************************************************
		
		function fms_get_address() {
			
			return get_the_title();
		}
	
	
		// API GETTERS
		
		// **********************************************************************************************************************************
		//
		// 		fms_set_listingdata
		//		Set data for the current listing post's API getter functions.
		//		NOTE: This is called automatically during "the_post" action hook.
		//		ALSO: When calling manually, the assumption is that you're working with a non WP listing, hence the remote override!
		//
		// **********************************************************************************************************************************
		
		function fms_set_listingdata($data, $remote = true) {
			
			FMSListingdata::get_instance()->is_remote = $remote;
			FMSListingdata::get_instance()->set_listingdata($data);
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_listingdata
		//		Get the full, unformatted set of raw meta used to create this listing.
		//		NOTE: Address and Remarks will not be present! (see above).
		//
		// **********************************************************************************************************************************
		
		function fms_get_listingdata() {
			
			return FMSListingdata::get_instance()->get_listingdata();
		}
	

		// **********************************************************************************************************************************
		//
		// 		fms_is_national_search_available
		//		All we're doing here is checking whether or not a national Transport ID has been set
		//		NOTE: This function does not check whether or not the ID actually works!!
		//
		// **********************************************************************************************************************************
		
		function fms_is_ddf_national_search_available() {
			
			$transport = fms_get_option("ddf_national_ids", "");
			
			if (!empty($transport)) return true;
			return false;
		}


		// **********************************************************************************************************************************
		//
		// 		fms_is_remote
		//		Is the current listing cached or loaded from the gateway?
		//
		// **********************************************************************************************************************************
		
		function fms_is_remote() {
			
			return FMSListingdata::get_instance()->is_remote;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_photo_urls
		//		Get the set of photos for the current listing. URLs will either be local (cached) or pointing to the FMS CDN.
		//		NOTE: Offset specifies starting index from the photo array, useful for excluding the lead photo if necessary.
		//
		// **********************************************************************************************************************************
		
		function fms_get_photo_urls($offset = 0) {
			
			global $post;
			
			$api 	= FMSListingdata::get_instance();
			$urls 	= array();
			
			$photos = $api->photos;	
			
			if (is_array($photos)) {
			
				// Ensure that onle 1 photo gets returned
				if (!fms_is_listing_available()) $photos = array($photos[0]);
			
				foreach ((array)$photos as $photo) {
					if (!empty($photo["url"]) && $photo["show"]) $urls[] = $photo["url"];
				}
			}

			if ($offset > 0) {
				$urls = array_slice($urls, $offset);
			}
			
			return $urls;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_lead_photo_url
		//		Pull the first photo from the list of photo URLs.
		//
		// **********************************************************************************************************************************

		function fms_get_lead_photo_url() {
			
			$urls = fms_get_photo_urls();
			
			$lead = "";
			if (!empty($urls)) $lead = $urls[0];
			
			return $lead;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_mls_number
		//		Get the (not necessarily unique) MLS number for the current listing.
		//
		// **********************************************************************************************************************************
		
		function fms_get_mls_number() {
			
			return FMSListingdata::get_instance()->mls_number;
		}

		
		// **********************************************************************************************************************************
		//
		// 		fms_get_listing_key
		//		Get the unique system key for the current listing (non-searchable).
		//		NOTE: This is the way that the FMS system keys the listing and might not have any real value otherwise.
		//
		// **********************************************************************************************************************************
		
		function fms_get_listing_key() {
			
			return FMSListingdata::get_instance()->listing_key;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_data_source
		//		Where did this listing come from? Show "exclusive" if there is no source.
		//
		// **********************************************************************************************************************************
		
		function fms_get_data_source() {
			
			$source = FMSListingdata::get_instance()->data_source;
			
			return (!empty($source) ? $source : __("Exclusive", "firstlook"));
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_is_exclusive
		//		Is this listing exclusive to this site? (Hand-made, artisanally crafted, etc.)
		//		NOTE: Exclusive listings should never have their source value set (read only)
		//
		// **********************************************************************************************************************************
		
		function fms_is_exclusive() {
			
			$source = FMSListingdata::get_instance()->data_source;
			
			return (empty($source) ? true : false);
		}

		
		// **********************************************************************************************************************************
		//
		// 		fms_get_status
		//		What is the current staus of this listing?
		//
		// **********************************************************************************************************************************
		
		function fms_get_status() {
			
			return FMSListingdata::get_instance()->status;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_is_lease
		//		Quick check to see if the listing status is ACTIVE_LEASE or LEASED
		//
		// **********************************************************************************************************************************
		
		function fms_is_lease() {
			
			$status = FMSListingdata::get_instance()->status;
			
			if ($status == fms_listing_statuses::$ACTIVE_LEASE || $status == fms_listing_statuses::$LEASED) return true;
			else return false;
		}
	
		
		// **********************************************************************************************************************************
		//
		// 		fms_is_listing_available
		//		Check to see if this listing is "on the market" or not.
		//		Will be used to hide thigns that shouldn't be shown for sold listings, etc.
		//
		// **********************************************************************************************************************************
		
		function fms_is_listing_available() {
			
			$status = fms_get_status();
			
			if ($status == fms_listing_statuses::$ACTIVE_SALE ||
				$status == fms_listing_statuses::$ACTIVE_LEASE ||
				$status == fms_listing_statuses::$CONDITIONAL) {
				
				return true;
			}
			else return false;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_can_show_listing
		//		One step further than the above, allow sold and leased listings through.
		//
		// **********************************************************************************************************************************
		
		function fms_can_show_listing() {
			
			$status = fms_get_status();
			
			if ($status == fms_listing_statuses::$ACTIVE_SALE ||
				$status == fms_listing_statuses::$ACTIVE_LEASE ||
				$status == fms_listing_statuses::$CONDITIONAL ||
				$status == fms_listing_statuses::$SOLD ||
				$status == fms_listing_statuses::$LEASED) {
				
				return true;
			}
			else return false;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_status_text
		//		Show the human-friendly text for the current status.
		//		NOTE: Developers may choose to overwrite these by supplying a "banner_labels" array to the global space.
		//
		// **********************************************************************************************************************************
	
		function fms_get_status_text() {
			
			$status = fms_get_status();
			$labels = (!empty($GLOBALS["banner_labels"]) ? $GLOBALS["banner_labels"] : array());
			$banner	= "";
			
			switch ($status) {
				
				case fms_listing_statuses::$ACTIVE_SALE:
				
				$banner = (!empty($labels["active_sale"]) ? $labels["active_sale"] : __("For Sale", "firstlook"));
				break;
				
				case fms_listing_statuses::$ACTIVE_LEASE:
				
				$banner = (!empty($labels["active_lease"]) ? $labels["active_lease"] : __("For Lease", "firstlook"));
				break;
				
				case fms_listing_statuses::$CONDITIONAL:
				
				$banner = (!empty($labels["conditional"]) ? $labels["conditional"] : __("Conditional", "firstlook"));
				break;
				
				case fms_listing_statuses::$SOLD:
				
				$banner = (!empty($labels["sold"]) ? $labels["sold"] :__( "Sold", "firstlook"));
				break;
				
				case fms_listing_statuses::$LEASED:
				
				$banner = (!empty($labels["leased"]) ? $labels["leased"] : __("Leased", "firstlook"));
				break;
			}
			
			if (fms_is_openhouse()) {
				$banner = (!empty($labels["openhouse"]) ? $labels["openhouse"] : __("Open House", "firstlook"));
			}

			return $banner;
		}
	
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_status_slug
		//		Get the (possibly user overwritten) status text in slug notation
		//
		// **********************************************************************************************************************************
	
		function fms_get_status_slug() {
			
			// Replace all spaces with dashes and strip anything non alphanumeric, dash, or underscore from the status string
			$status = str_replace(" ", "-", trim(strtolower(fms_get_status_text())));
			$status = preg_replace("/[^0-9a-z\-\_]+/i", "", $status);
			
			return $status;
		}

		
		// **********************************************************************************************************************************
		//
		// 		fms_get_price
		//		Get the raw listing price. Will either be the sale or lease price depending on the status.
		//
		// **********************************************************************************************************************************
		
		function fms_get_price() {
			
			if (fms_is_listing_available()) {
			
				return FMSListingdata::get_instance()->price;
			}
			else return 0;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_lease_rate
		//		Get the lease rate postfix text if available. If empty, we assume "per square foot" unless we're told not to.
		//
		// **********************************************************************************************************************************
		
		function fms_get_lease_rate($assume = "/sqft") {
			
			$rate = FMSListingdata::get_instance()->lease_rate;
			
			if (empty($rate) && FMSListingdata::get_instance()->status == "active_lease") {
				
				$rate = $assume;
			}
			return $rate;
		}
	
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_formatted_price
		//		Use the price formatter function to get the price formatted nicely.
		//
		// **********************************************************************************************************************************
	
		function fms_get_formatted_price($include_lease_rate = true, $decimals = 0, $decimal_sep = ".", $thousands_sep = ",", $symbol = "$") {
				
			if (fms_is_listing_available()) {
				
				$price = fms_format_money(fms_get_price(), $decimals, $decimal_sep, $thousands_sep, $symbol);
				
				// Attach the lease rate (if applicable)
				if ($include_lease_rate) $price = $price ." ". fms_get_lease_rate();

				return $price; 
			}
			else return fms_get_status_text();
		}
	
	
		// **********************************************************************************************************************************
		//
		// 		fms_get_closing_date
		//		Get the closing date if set by the user... It won't be available in DDF!
		//
		// **********************************************************************************************************************************
	
		function fms_get_closing_date() {
			
			return FMSListingdata::get_instance()->closing_date;
		}
		

		// **********************************************************************************************************************************
		//
		// 		fms_get_last_updated
		//		The last timestamp that this listing was updated on MLS
		//
		// **********************************************************************************************************************************
	
		function fms_get_last_updated() {
			
			return FMSListingdata::get_instance()->last_updated;
		}
	

		// **********************************************************************************************************************************
		//
		// 		fms_get_taxes
		//		Get the tax rate if set by the user... It won't be available in DDF!
		//
		// **********************************************************************************************************************************
	
		function fms_get_taxes() {
			
			return FMSListingdata::get_instance()->taxes;
		}
	
	
		// **********************************************************************************************************************************
		//
		// 		fms_get_tax_year
		//		Get the tax year if set by the user... It won't be available in DDF!
		//
		// **********************************************************************************************************************************
	
		function fms_get_tax_year() {
			
			return FMSListingdata::get_instance()->tax_year;
		}
	

		// **********************************************************************************************************************************
		//
		// 		fms_get_beds
		//		Get total number of bedrooms.
		//
		// **********************************************************************************************************************************
	
		function fms_get_beds() {
			
			return FMSListingdata::get_instance()->beds;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_baths
		//		Get total number of bathrooms.
		//
		// **********************************************************************************************************************************
		
		function fms_get_baths() {
			
			return FMSListingdata::get_instance()->baths;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_bed_bath_string
		//		Get the bed bath count as a nicely formatted string.
		//
		// **********************************************************************************************************************************
		
		function fms_get_bed_bath_string() {
			
			$be = FMSListingdata::get_instance()->beds;
			$ba = FMSListingdata::get_instance()->baths;
			
			return implode(", ", array_filter(array(  
						
				(!empty($be) ? $be ." Beds"		: ""),
				(!empty($ba) ? $ba ." Baths" 	: ""),
				
			)));
		}
	
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_property_type
		//		What is the property type for the current listing?
		//
		// **********************************************************************************************************************************
		
		function fms_get_property_type() {
			
			return FMSListingdata::get_instance()->property_type;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_dwelling_type
		//		What is the dwelling type for the current listing?
		//
		// **********************************************************************************************************************************
		
		function fms_get_dwelling_type() {
			
			return FMSListingdata::get_instance()->dwelling_type;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_building_style
		//		What is the building style for the current listing?
		//
		// **********************************************************************************************************************************
		
		function fms_get_building_style() {
			
			return FMSListingdata::get_instance()->building_style;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_zoning
		//		Zoning information (if available).
		//
		// **********************************************************************************************************************************
		
		function fms_get_zoning() {
			
			return FMSListingdata::get_instance()->zoning;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_year_built
		//		Year that this house or building was constructed.
		//
		// **********************************************************************************************************************************
		
		function fms_get_year_built() {
			
			return FMSListingdata::get_instance()->year_built;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_flooring
		//		Type of flooring in the house or building.
		//
		// **********************************************************************************************************************************
		
		function fms_get_flooring() {
			
			return FMSListingdata::get_instance()->flooring;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_heating
		//		Type of heating in the house or building.
		//
		// **********************************************************************************************************************************
		
		function fms_get_heating() {
			
			return FMSListingdata::get_instance()->heating;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_heating_fuel
		//		Fuel used with the above heating type.
		//
		// **********************************************************************************************************************************
		
		function fms_get_heating_fuel() {
			
			return FMSListingdata::get_instance()->heating_fuel;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_ac_type
		//		Type of AC in the house or building (if applicable).
		//
		// **********************************************************************************************************************************
		
		function fms_get_ac_type() {
			
			return FMSListingdata::get_instance()->ac_type;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_square_footage
		//		What is the total square footage of the unit?
		//
		// **********************************************************************************************************************************
		
		function fms_get_square_footage() {
			
			return FMSListingdata::get_instance()->square_footage;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_lot_size
		//		What is the total square footage of the property?
		//
		// **********************************************************************************************************************************
		
		function fms_get_lot_size() {
			
			return FMSListingdata::get_instance()->lot_size;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_parking
		//		Doe this listing include parking?
		//
		// **********************************************************************************************************************************
		
		function fms_get_parking() {
			
			return FMSListingdata::get_instance()->parking;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_parking_type
		//		If parking is available, what type of parking is it? Could it be for a space ship?
		//
		// **********************************************************************************************************************************
		
		function fms_get_parking_type() {
			
			return FMSListingdata::get_instance()->parking_type;
		}
		

		// **********************************************************************************************************************************
		//
		// 		fms_get_garage_spaces
		//		How many garage spaces are available?
		//
		// **********************************************************************************************************************************
		
		function fms_get_garage_spaces() {
			
			return FMSListingdata::get_instance()->garage_spaces;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_inclusions
		//		Self explanatory...
		//
		// **********************************************************************************************************************************
		
		function fms_get_inclusions() {
			
			return FMSListingdata::get_instance()->inclusions;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_exclusions
		//		Self explanatory...
		//
		// **********************************************************************************************************************************
		
		function fms_get_exclusions() {
			
			return FMSListingdata::get_instance()->exclusions;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_tenant_pays
		//		If this is a rental or lease unit, what services must the tenant pay for themselves?
		//
		// **********************************************************************************************************************************
		
		function fms_tenant_pays() {
			
			return FMSListingdata::get_instance()->tenant_pays;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_amenities
		//		Amenities available in rental or lease units.
		//
		// **********************************************************************************************************************************
		
		function fms_get_amenities() {
			
			return FMSListingdata::get_instance()->amenities;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_condo_fee
		//		For condo / strata listings, get the condo fee (if available).
		//
		// **********************************************************************************************************************************
		
		function fms_get_condo_fee() {
			
			return FMSListingdata::get_instance()->condo_fee;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_association_fee
		//		See above.
		//
		// **********************************************************************************************************************************
		
		function fms_get_association_fee() {
			
			return fms_get_condo_fee();
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_condo_fee_inclides
		//		What is included in that monthly fee? Are they just pissing it away?
		//
		// **********************************************************************************************************************************
		
		function fms_condo_fee_inclides() {
			
			return FMSListingdata::get_instance()->fee_includes;
		}
		

		// **********************************************************************************************************************************
		//
		// 		fms_get_city
		//		What city is the current listing in?
		//		NOTE: Calgary does this weird, they seem to include the neighbourhood in the city field for some reason.
		//
		// **********************************************************************************************************************************
		
		function fms_get_city() {
			
			return FMSListingdata::get_instance()->city;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_province
		//		What province is the current listing in?
		//
		// **********************************************************************************************************************************
		
		function fms_get_province() {
			
			return FMSListingdata::get_instance()->province;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_postal_code
		//		Get the current listing's postal code.
		//
		// **********************************************************************************************************************************
		
		function fms_get_postal_code() {
			
			return FMSListingdata::get_instance()->postal_code;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_city
		//		Get the current listing's neighbourhood or community.
		//		NOTE: Toronto does this weird, none of their listings have "neighbourhoods" for some reason...
		//
		// **********************************************************************************************************************************
		
		function fms_get_neighborhood() {
			
			return FMSListingdata::get_instance()->neighborhood;
		}
		
		function fms_get_neighbourhood() {
			
			// If you're spelling it the correct way,
			// You will be rewarded with code that won't break!!
			return fms_get_neighborhood();
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		COMBO TIME!
		//		Several functions, get several parts of the listing location as one comma separated string.
		//
		// **********************************************************************************************************************************
		
		function fms_get_address_city_province($sep = ", ") {
		
			return implode($sep, array_filter(array(
				
				fms_get_address(),
				fms_get_city(),
				fms_get_province(),
			
			)));
		}
		
		function fms_get_city_province($sep = ", ") {
		
			return implode($sep, array_filter(array(
				
				fms_get_city(),
				fms_get_province(),
			
			)));
		}
		
		function fms_get_city_neighborhood($sep = ", ") {
		
			return implode($sep, array_filter(array(
				
				fms_get_city(),
				fms_get_neighborhood(),
			
			)));
		}
		
		function fms_get_city_neighbourhood($sep = ", ") {
		
			// If you're spelling it the correct way,
			// You will be rewarded with code that won't break!!
			return fms_get_city_neighborhood($sep);
		}
		
		function fms_get_city_province_neighborhood($sep = ", ") {
		
			return implode($sep, array_filter(array(
				
				fms_get_city(),
				fms_get_province(),
				fms_get_neighborhood(),
			
			)));
		}
		
		function fms_get_city_province_neighbourhood($sep = ", ") {
		
			// If you're spelling it the correct way,
			// You will be rewarded with code that won't break!!
			return fms_get_city_province_neighborhood($sep);
		}
		
		function fms_get_full_address($sep = ", ") {
			
			// NOTE: This is mostly just for geocoding services...
			return implode($sep, array_filter(array(
				
				fms_get_address(),
				fms_get_city(),
				fms_get_province(),
				fms_get_postal_code(),
			
			)));
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_latitude, fms_get_longitude
		//		The current listing's geographical coordinates
		//
		// **********************************************************************************************************************************
		
		function fms_get_latitude() {
			
			return FMSListingdata::get_instance()->latitude;
		}
	
		function fms_get_longitude() {
			
			return FMSListingdata::get_instance()->longitude;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_directions
		//		Get the driving / transit directions to this listing (if provided).
		//
		// **********************************************************************************************************************************
		
		function fms_get_directions() {
			
			return FMSListingdata::get_instance()->directions;
		}
		

		// **********************************************************************************************************************************
		//
		// 		fms_get_brokerage_code
		//		The brokerage code for the current listing.
		//
		// **********************************************************************************************************************************
		
		function fms_get_brokerage_code() {
			
			return FMSListingdata::get_instance()->brokerage_code;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_listing_agents
		//		The CREA IDs for the agents with their names on this listing.
		//
		// **********************************************************************************************************************************
		
		function fms_get_listing_agents() {
			
			$one	= FMSListingdata::get_instance()->listing_agent_1;
			$two	= FMSListingdata::get_instance()->listing_agent_2;
			$three	= FMSListingdata::get_instance()->listing_agent_3;
			
			return array_filter(array($one, $two, $three));
		}

		
		// **********************************************************************************************************************************
		//
		// 		fms_get_presented_by
		//		The human-friendly name of the listing brokerage.
		//
		// **********************************************************************************************************************************
		
		function fms_get_presented_by() {
			
			return FMSListingdata::get_instance()->presented_by;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_openhouse
		//		Get the formatted array of open house data associated with this listing.
		//
		// **********************************************************************************************************************************
	
		function fms_get_openhouse() {
		
			$api 		= FMSListingdata::get_instance();
			$openhouses = array();
			
			if (is_array($api->openhouse_group)) {
			
				foreach ($api->openhouse_group as $openhouse) {
				
					// Only include open houses that have start and end date time set
					if (!empty($openhouse["openhouse_start"]) && !empty($openhouse["openhouse_end"])) {
						
						$openhouses[] = array(
						
							"openhouse_start"		=> (!empty($openhouse["openhouse_start"]) 		? $openhouse["openhouse_start"] 		: ""),
							"openhouse_end"			=> (!empty($openhouse["openhouse_end"]) 		? $openhouse["openhouse_end"] 			: ""),
							"openhouse_description"	=> (!empty($openhouse["openhouse_description"]) ? $openhouse["openhouse_description"] 	: ""),
							
						);
					}
				}
			}
			if (empty($openhouses)) $openhouses = array();
			return $openhouses;
		}
	
	
		// **********************************************************************************************************************************
		//
		// 		fms_get_openhouse_raw
		//		Get the raw (unformatted) open house data
		//
		// **********************************************************************************************************************************
	
		function fms_get_openhouse_raw() {
			
			return FMSListingdata::get_instance()->openhouse_group;
		}
	
	
		// **********************************************************************************************************************************
		//
		// 		fms_is_openhouse
		//		Check the open house data to see if there is an open house either coming up on ongoing.
		//
		// **********************************************************************************************************************************
	
		function fms_is_openhouse() {
			
			foreach ((array)fms_get_openhouse() as $event) {
				
				$now = time();
				
				if (!empty($event["openhouse_start"]) && !empty($event["openhouse_end"])) {
					
					$start	= $event["openhouse_start"];
					$end	= $event["openhouse_end"];
					
					//if ($start <= $end && ($now >= $start && $now <= $end)) return true;
					if ($start <= $end && $now <= $end) return true;
				}
			}
			return false;
		}
	

		// **********************************************************************************************************************************
		//
		// 		fms_get_rooms
		//		Get the formatted array of room data associated with the current listing.
		//
		// **********************************************************************************************************************************
		
		function fms_get_rooms() {
			
			if (fms_is_listing_available()) {
			
				$api 	= FMSListingdata::get_instance();
				$rooms 	= array();
				
				if (is_array($api->rooms_group)) {
				
					foreach ($api->rooms_group as $room_data) {
					
						// Only include rooms that have a name and at least one other parameter
						if (!empty($room_data["room_name"]) && (!empty($room_data["room_level"]) || !empty($$room_data["room_dimensions"]))) {
							
							$rooms[] = array(
								
								"room_name"			=> $room_data["room_name"],
								
								"room_level" 		=> (!empty($room_data["room_level"]) 		? $room_data["room_level"] 		: __("Inquire", "firstlook")),
								"room_dimensions" 	=> (!empty($room_data["room_dimensions"]) 	? $room_data["room_dimensions"] : __("Inquire", "firstlook")),
							);
						}	
					}
				}
				if (empty($rooms)) $rooms = array();
				return $rooms;
			}
			else return array();
		}


		// **********************************************************************************************************************************
		//
		// 		fms_get_media_links
		//		Get the formatted array of external media links associated with the current listing.
		//
		// **********************************************************************************************************************************

		function fms_get_media_links() {
			
			$api 	= FMSListingdata::get_instance();
			$links 	= array();
			
			if (is_array($api->media_links_group)) {
			
				foreach ($api->media_links_group as $link) {
				
					// Only include rooms that have a name and at least one other parameter
					if (!empty($link["link_name"]) && !empty($link["link_url"])) {
						
						$links[] = array(
							
							"link_name"	=> $link["link_name"],
							"link_url"	=> $link["link_url"],
						);
					}	
				}
			}
			if (empty($links)) $links = array();
			return $links;
		}


		// **********************************************************************************************************************************
		//
		// 		fms_get_video_links
		//		Get the formatted array of external media links associated with the current listing.
		//
		// **********************************************************************************************************************************

		function fms_get_video_links() {
			
			$api 	= FMSListingdata::get_instance();
			$links 	= array();
			
			if (is_array($api->media_videos_group)) {
			
				foreach ($api->media_videos_group as $link) {
				
					// Only include rooms that have a name and at least one other parameter
					if (!empty($link["video_name"]) && !empty($link["video_url"])) {
						
						$links[] = array(
							
							"video_name"	=> $link["video_name"],
							"video_url"		=> $link["video_url"],
						);
					}	
				}
			}
			if (empty($links)) $links = array();
			return $links;
		}
		
		
		// **********************************************************************************************************************************
		//
		// 		fms_get_tour_links
		//		Get the formatted array of external media links associated with the current listing.
		//
		// **********************************************************************************************************************************

		function fms_get_tour_links() {
			
			$api 	= FMSListingdata::get_instance();
			$links 	= array();
			
			if (is_array($api->media_tours_group)) {
			
				foreach ($api->media_tours_group as $link) {
				
					// Only include rooms that have a name and at least one other parameter
					if (!empty($link["tour_name"]) && !empty($link["tour_url"])) {
						
						$links[] = array(
							
							"tour_name"		=> $link["tour_name"],
							"tour_url"		=> $link["tour_url"],
						);
					}	
				}
			}
			if (empty($links)) $links = array();
			return $links;
		}


		// **********************************************************************************************************************************
		//
		// 		CONVENIENCE FEATURE (fms_get_details)
		//		Get a pre-formatted table of "essential" and "available" data to make life slightly easier.
		//
		// **********************************************************************************************************************************

		function fms_get_details() {
			
			if (fms_is_listing_available()) {
			
				$api = FMSListingdata::get_instance();
				
				$data = array();
				$temp = array(
					
					// DETAILS
					"MLS#"				=> $api->mls_number,
					"Status"			=> fms_get_status_text(),
					"Last Updated"		=> date("m/d/Y", fms_get_last_updated()),
					"Price"				=> fms_get_formatted_price(),
					
					// LEASE
					"Tenant Pays"		=> $api->tenant_pays,
					"Amenities"			=> $api->amenities,
					
					// DETAILS CONT'D
					"Neighbourhood"	 	=> $api->neighborhood,
					"Postal Code"		=> $api->postal_code,
					"Dwelling Type"		=> $api->dwelling_type,
					"Property Type"		=> $api->property_type,
					"Year Built"		=> $api->year_built,
					"Bedrooms"			=> $api->beds,
					"Bathrooms"			=> $api->baths,
					"Flooring"			=> $api->flooring,
					"Heating"			=> $api->heating,
					"Heating Fuel"		=> $api->heating_fuel,
					"AC Type"			=> $api->ac_type,
					"Taxes"				=> fms_format_money($api->taxes),
					"Tax Year"			=> $api->tax_year,
					"Lot Size"			=> $api->lot_size,
					"Square Footage"	=> $api->square_footage,
					"Parking"			=> $api->parking,
					"Inclusions"		=> $api->inclusions,
					"Exclusions"		=> $api->exclusions,
					"Zoning"			=> $api->zoning,
					"Directions"		=> $api->directions,
					"Building Style"	=> $api->building_style,
					"Parking Type"		=> $api->parking_type,
					"Garage Spaces"		=> $api->garage_spaces,
					
					// CONDO
					"Association Fees"	=> fms_format_money($api->condo_fee),
					"Fee Includes"		=> $api->fee_includes,
					
					// OWNER
					"Presented By"		=> ($api->is_remote ? $api->presented_by : ""),
				);
				
				foreach ($temp as $key => $val) {
					if (!empty($val)) $data[$key] = $val;
				}
	
				if (empty($data)) $data = array();
				return $data;
			}
			else return array();
		}
	}
	
	// END FMS-ACCESSOR-API-FUNCTIONS
	
?>