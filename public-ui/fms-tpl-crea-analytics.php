<?php defined("ABSPATH") or die("Access denied.");

// **********************************************************************************************************************
// *
// *	CREA Analytics Template
// *
// *	Sends some basic usage data to CREA whenever a National Pool listing is viewed.
// *	Uses a cookie to save the "guid" for this browser, valid for 365 days.
// *	
// *	NOTE: CREA now requires analytics data to be submitted for sites using shared pool data.
// *
// **********************************************************************************************************************
	
// Get the first nation feed ID that was entered
$ddf_national_ids = array_filter(explode(",", preg_replace("/\s+/", "", fms_get_option("ddf_national_ids"))));
$ddf_id = $ddf_national_ids[0];
?>
<script>
	jQuery(document).ready(function($) {
	
		var key 	= "<?php echo fms_get_listing_key() ?>";
		var dest	= "<?php echo $ddf_id ?>";
	
		var guid = getCookie("fms-crea-analytics-guid");
		if (!guid) {
	
			// Create a GUID
			function S4() { return (((1+Math.random())*0x10000)|0).toString(16).substring(1) }
			guid = (S4() + S4() + "-" + S4() + "-4" + S4().substr(0,3) + "-" + S4() + "-" + S4() + S4() + S4()).toLowerCase();
			
			// Set the cookie
			setCookie("fms-crea-analytics-guid", guid, 365);
		}
	
		// Parameterize
		var params = "?"+ $.param({
			
			"ListingID":		key,
			"DestinationID":	dest,
			"EventType":		"view",
			"UUID":				guid +"-"+ dest,
			
		});
		
		// Submit to the CREA API...
		$.get("http://analytics.crea.ca/LogEvents.svc/LogEvents"+ params);
		
		// Get a specific cookie
		function getCookie(cname) {
							
			var value = "; " + document.cookie;
			var parts = value.split("; " + cname + "=");
			if (parts.length == 2) return parts.pop().split(";").shift();
		}
		
		// Set a cookie
		function setCookie(cname, cvalue, exdays) {
			
			var d = new Date();
			d.setTime(d.getTime() + (exdays*24*60*60*1000));
			var expires = "expires="+ d.toUTCString();
			document.cookie = cname +"="+ cvalue +"; "+ expires +"; path=/";
		}
	});
</script>
