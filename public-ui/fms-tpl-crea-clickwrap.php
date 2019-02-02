<?php defined("ABSPATH") or die("Access denied.");

// **********************************************************************************************************************
// *
// *	Clickwrap Overlay Template
// *
// *	Creates an overlay when an archive or single remote listing is accessed for the first time.
// *	Uses a cookie to save the "accepted terms" status, and will only show if terms have not been accepted.
// *	
// *	NOTE: CREA requires the use of a clickwrap to ensure that the terms are understood.
// *	This specific overlay can be disabled from the admin options if devs wish to create their own.
// *
// **********************************************************************************************************************
	
?>
<div id="fms-crea-clickwrap">
	<div id="fms-clickwrap-content" class="fms-clickwrap-content">
		
		<div id="fms-clickwrap-inside" class="fms-clickwrap-inside">
		
			<div id="fms-clickwrap-text" class="fms-clickwrap-section fms-text-container">
				
				<h1 class="fms-clickwrp-heading">Terms of Use</h1>
				<p class="fms-clickwrp-paragraph">
					
					Because we get our listing information from the Canadian Real Estate Association (CREA®),
					we need to show you these terms of use before you proceed.
				</p>
				<p class="fms-clickwrp-paragraph">
					
					REALTOR®, REALTORS®, and the REALTOR® logo are certification marks that are
					owned by REALTOR® Canada Inc. and licensed exclusively to The Canadian Real
					Estate Association (CREA). These certification marks identify real estate
					professionals who are members of CREA and who must abide by CREA’s By-Laws,
					Rules, and the REALTOR® Code. The MLS® trademark and the MLS® logo are owned
					by CREA and identify the quality of services provided by real estate professionals
					who are members of CREA.
				</p>
				<p class="fms-clickwrp-paragraph">
					
					The information contained on this site is based in whole or in part on information that is
					provided by members of The Canadian Real Estate Association, who are responsible
					for its accuracy. CREA reproduces and distributes this information as a service for its
					members and assumes no responsibility for its accuracy
				</p>
				<p class="fms-clickwrp-paragraph">
					
					The listing content on this website is protected by
					copyright and other laws, and is intended solely for the private, non-commercial use
					by individuals. Any other reproduction, distribution or use of the content, in whole 
					or in part, is specifically forbidden. The prohibited uses include commercial use,
					“screen scraping”, “database scraping”, and any other activity intended to collect,
					store, reorganize or manipulate data on the pages produced by or displayed on this
					website.
				</p>
				<p class="fms-clickwrp-paragraph">
					
					The listing content on this website originating from a province where the REALTOR® sponsoring
					this site is not licensed to trade are provided for reference only and are not intended to
					solicit trade of any kind. Any consumers interested in listings viewed on this website must 
					contact a REALTOR® who is licensed to trade real estate in the province where the listing is found.
				</p>
			</div>
			
			<div id="fms-clickwrap-buttons" class="fms-clickwrap-section fms-button-container">
				<button class="fms-button"><a href="" class="accept">I Agree</a></button>
				<button class="fms-button"><a href="<?php echo get_home_url() ?>">I Do Not Agree</a></button>
			</div>
			
			<? /*
			<div class="fms-clickwrap-section fms-trademark-container">
				<div class="tm crea">
					<span>&#57348;</span>
					<span>&#57347;</span>
				</div>
			</div>
			*/ ?>
		
		</div>
		
	</div>
</div>

<script>
	jQuery(document).ready(function($) {
	
		var target 	= $("div#fms-crea-clickwrap");
		var isOpen	= false;
								
		var openClickwrap 	= function() { target.addClass("open"); 	isOpen = true; 		};
		var closeClickwrap 	= function() { target.removeClass("open"); 	isOpen = false;		}; 
	
		// Check if the terms have been accepted and display the cw if not
		if (!getCookie("fms-crea-accepted-terms")) {
			
			openClickwrap.call();
			
			// Block the body scrolling
			$("html, body").css({ "overflow": "hidden" });
		}
	
		// Set the accepted cookie and close the cw
		$("a.accept", target).on("click", function(e) {
			e.preventDefault();
			
			$("html, body").css({ "overflow": "initial" });
			
			setCookie("fms-crea-accepted-terms", "true", 30);
			closeClickwrap.call();
		});
	
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