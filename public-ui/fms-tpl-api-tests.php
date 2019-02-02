<?php defined("ABSPATH") or die("Access denied."); 
	
// **********************************************************************************************************************
// *
// *	API Tests
// *
// *	Here is a comprehensive list of how to call all the different functions from the API.
// *	This will never run on its own, but rather can be pulled in wherever it's needed.
// *	
// *	NOTE: Like the other templates, this will only work properly inside an fms_listing loop!
// *
// **********************************************************************************************************************	

?>
<div id="fms-listing-template">

	<h1>Testing All FMS API Functions</h1>
	
	<h3>Money Formatting</h3>
	<p><?php echo fms_format_money("2345678.9", 2, ".", ",", "¢") ?></p>
	
	<h3>Money Formatting (preformatted)</h3>
	<p><?php echo fms_format_money("€2.345.678,9", 2, ",", ".", "€") ?></p>
	
	<h3>Search URL (encoded)</h3>
	<p><?php echo fms_format_search_url(array( "city" => "ottawa", "province" => "ontario" ), true) ?></p>
	
	<h3>Search URL (unencoded)</h3>
	<p><?php echo fms_format_search_url(array( "city" => "ottawa", "province" => "ontario" ), false) ?></p>
	
	<h3>Remarks</h3>
	<p><?php echo fms_get_remarks() ?></p>
	
	<h3>Address</h3>
	<p><?php echo fms_get_address() ?></p>							 
	
	<h3>Get Listingdata</h3>
	<p><pre><?php print_r(fms_get_listingdata()) ?></pre></p>							 
	
	<h3>Is Remote</h3>
	<p><?php echo fms_is_remote() ?></p>							 
	
	<h3>Photo URLs</h3>
	<p><pre><? print_r(fms_get_photo_urls()) ?></pre></p>					 
	
	<h3>Lead Photo URL</h3>
	<p><?php echo fms_get_lead_photo_url() ?></p>						 
	
	<h3>MLS Number</h3>
	<p><?php echo fms_get_mls_number() ?></p>
	
	<h3>Listing Key</h3>
	<p><?php echo fms_get_listing_key() ?></p>								 
	
	<h3>Data Source</h3>
	<p><?php echo fms_get_data_source() ?></p>							 
	
	<h3>Is Exclusive?</h3>
	<p><?php echo fms_is_exclusive() ?></p>	
	
	<h3>Status</h3>
	<p><?php echo fms_get_status() ?></p>								 
	
	<h3>Status Text</h3>
	<p><?php echo fms_get_status_text() ?></p>								 
	
	<h3>Status Slug</h3>
	<p><?php echo fms_get_status_slug() ?></p>								 
	
	<h3>Price (unformatted)</h3>
	<p><?php echo fms_get_price() ?></p> 										 
	
	<h3>Lease Rate</h3>
	<p><?php echo fms_get_lease_rate($assume = true) ?></p> 					 
	
	<h3>Formatted Price</h3>
	<p><?php echo fms_get_formatted_price(true, 2) ?></p>		
	
	<h3>Closing Date</h3>
	<p><?php echo fms_get_closing_date() ?></p>							 
	
	<h3>Last Updated</h3>
	<p><?php echo fms_get_last_updated() ?></p>							 
	
	<h3>Taxes</h3>
	<p><?php echo fms_get_taxes() ?></p>								 
	
	<h3>Tax Year</h3>
	<p><?php echo fms_get_tax_year() ?></p>								 
	
	<h3>Beds</h3>
	<p><?php echo fms_get_beds() ?></p>									 
	
	<h3>Baths</h3>
	<p><?php echo fms_get_baths() ?></p>									 
	
	<h3>Property Type</h3>
	<p><?php echo fms_get_property_type() ?></p>							 
	
	<h3>Dwelling Type</h3>
	<p><?php echo fms_get_dwelling_type() ?></p>							 
	
	<h3>Building Style</h3>
	<p><?php echo fms_get_building_style() ?></p>							 
	
	<h3>Zoning</h3>
	<p><?php echo fms_get_zoning() ?></p>								 
	
	<h3>Year Built</h3>
	<p><?php echo fms_get_year_built() ?></p>							 
	
	<h3>Flooring</h3>
	<p><?php echo fms_get_flooring() ?></p>								 
	
	<h3>Heating</h3>
	<p><?php echo fms_get_heating() ?></p>								 
	
	<h3>Heating Fuel</h3>
	<p><?php echo fms_get_heating_fuel() ?></p>							 
	
	<h3>AC Type</h3>
	<p><?php echo fms_get_ac_type() ?></p>								 
	
	<h3>Square Footage</h3>
	<p><?php echo fms_get_square_footage() ?></p>							 
	
	<h3>Lot Size</h3>
	<p><?php echo fms_get_lot_size() ?></p>								 
	
	<h3>Parking</h3>
	<p><?php echo fms_get_parking() ?></p>									 
	
	<h3>Parking Type</h3>
	<p><?php echo fms_get_parking_type() ?></p>							 
	
	<h3>Garage Spaces</h3>
	<p><?php echo fms_get_garage_spaces() ?></p>								 
	
	<h3>Inclisions</h3>
	<p><?php echo fms_get_inclusions() ?></p>							 
	
	<h3>Exclusions</h3>
	<p><?php echo fms_get_exclusions() ?></p>								 
	
	<h3>Tenant Pays</h3>
	<p><?php echo fms_tenant_pays() ?></p>									 
	
	<h3>Amenities</h3>
	<p><?php echo fms_get_amenities() ?></p>								 
	
	<h3>Condo Fee</h3>
	<p><?php echo fms_get_condo_fee() ?></p>								 
	
	<h3>Association Fee (same as condo fee)</h3>
	<p><?php echo fms_get_association_fee() ?></p>							 
	
	<h3>Condo Fee Incluldes</h3>
	<p><?php echo fms_condo_fee_inclides() ?></p>							 
	
	<h3>City</h3>
	<p><?php echo fms_get_city() ?></p>								 
	
	<h3>Province</h3>
	<p><?php echo fms_get_province() ?></p>								 
	
	<h3>Postal Code</h3>
	<p><?php echo fms_get_postal_code() ?></p>								 
	
	<h3>Neighborhood / Community</h3>
	<p><?php echo fms_get_neighborhood() ?></p>								 
	
	<h3>Neighbo(u)rhood (again)</h3>
	<p><?php echo fms_get_neighbourhood() ?></p>							 
	
	<h3>City - Province</h3>
	<p><?php echo fms_get_city_province() ?></p>							 
	
	<h3>City - Neighborhood</h3>
	<p><?php echo fms_get_city_neighborhood() ?></p>						 
	
	<h3>City - Neighbo(u)rhood</h3>
	<p><?php echo fms_get_city_neighbourhood() ?></p>				 
	
	<h3>City - Province - Neighborhood</h3>
	<p><?php echo fms_get_city_province_neighborhood() ?></p>				 
	
	<h3>City - Province - Neighbo(u)rhood</h3>
	<p><?php echo fms_get_city_province_neighbourhood() ?></p>			 
	
	<h3>Full Address</h3>
	<p><?php echo fms_get_full_address() ?></p>							 
	
	<h3>Latitude</h3>
	<p><?php echo fms_get_latitude() ?></p>							 
	
	<h3>Longitude</h3>
	<p><?php echo fms_get_longitude() ?></p>								 
	
	<h3>Directions</h3>
	<p><?php echo fms_get_directions() ?></p>								 
	
	<h3>Brokerage Code</h3>
	<p><?php echo fms_get_brokerage_code() ?></p>							 
	
	<h3>Listing Agents</h3>
	<p><pre><?php print_r(fms_get_listing_agents()) ?></pre></p>							 
	
	<h3>Presented By</h3>
	<p><?php echo fms_get_presented_by() ?></p>								 
	
	<h3>Openhouses</h3>
	<p><pre><?php print_r(fms_get_openhouse()) ?></pre></p>								 
	
	<h3>Is Openhouse?</h3>
	<p><?php echo fms_is_openhouse() ?></p>									 
	
	<h3>Rooms</h3>
	<p><pre><?php print_r(fms_get_rooms()) ?></pre></p>									 
	
	<h3>Media Links</h3>
	<p><pre><?php print_r(fms_get_media_links()) ?></pre></p>							 
	
	<h3>Details Table</h3>
	<p><pre><?php print_r(fms_get_details()) ?></pre></p>

</div>