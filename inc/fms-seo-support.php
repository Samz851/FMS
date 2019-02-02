<?php defined("ABSPATH") or die("Access denied.") ?>
<?php




	
	// TODO:
	// Add OG / meta tags for archive and result pages



	
	
	
	



	// **********************************************************************************************************************************
	//
	// 		Modify the contents of the title tag for listing singles
	//
	// **********************************************************************************************************************************
	
	function fms_seo_support_title() {
	
		global $wp_query;
		global $post;
		
		if (($post && $post->post_type == "fms_listing") && (is_singular() || count($wp_query->posts) == 1) && !fms_get_option("disable_retriever_seo", false)) {
		
			// Send back the freshly encoded title
			return htmlspecialchars(fms_get_full_address() ." - MLS#". fms_get_mls_number(), ENT_COMPAT, "UTF-8", false);
		}
	}
	add_filter("pre_get_document_title", "fms_seo_support_title", 0);


	// **********************************************************************************************************************************
	//
	// 		Add OG meta and schema ld+json to the header for listings, running as first priority
	//
	// **********************************************************************************************************************************

	function fms_seo_support_header() {

		global $wp;
		global $wp_query;
		global $post;
		
		if (($post && $post->post_type == "fms_listing") && (is_singular() || count($wp_query->posts) == 1) && !fms_get_option("disable_retriever_seo", false)) {

			// Set some variables for the OG data
			
			$og_url 	= add_query_arg($_SERVER["QUERY_STRING"], "", home_url($wp->request) ."/");
			$og_title	= fms_get_full_address() ." - MLS#". fms_get_mls_number();
			$og_image	= fms_get_lead_photo_url();
			$og_desc	= $post->post_content;
			
			// NOTE:
			// Ideally we would just call fms_get_remarks for the description and be done with it...
			// But we can't because WP doesn't support calling get_the_content outside of the loop.
			// Title and other meta stuff seems to work fine though! 
			
			// Add some character encoding safety
			 
			$og_title 	= htmlspecialchars($og_title,	ENT_COMPAT, "UTF-8", false);
			$og_desc	= htmlspecialchars($og_desc,	ENT_COMPAT, "UTF-8", false);
			
			?>	
			
			<!-- FMS: Add OG support for listing objects -->
			
			<meta name="twitter:card" 			content="summary" />
												
			<meta property="og:url" 			content="<?php echo $og_url 	?>" />
			<meta property="og:title" 			content="<?php echo $og_title 	?>" />
			<meta property="og:description" 	content="<?php echo $og_desc	?>" />
			<meta property="og:image" 			content="<?php echo $og_image 	?>" />
			
			<meta property="og:image:width"		content="1200" 	/>
			<meta property="og:image:height"	content="630"	/>
												
			<meta property="description"		content="<?php echo $og_desc	?>" />
			
			<!-- FMS: Add schema support for listing objects -->
			
			<script type="application/ld+json">
				
				{
					"@context":		"http:\/\/schema.org",
					"@type":		"Product",
					
					"name":			"<?php echo $og_title ?>",
					"image":		"<?php echo $og_image ?>",
					
					"offers": {
					
						"@type":			"Offer",
						
						"price":			"<?php echo fms_get_price() ?>",
						"priceCurrency":	"CAD"
					}
				}
				
			</script>
				
			<script type="application/ld+json">
				
				{
					"@context":		"http:\/\/schema.org",
					"@type":		"Residence",
					
					"address": {
					
						"@type":			"PostalAddress",
						
						"streetAddress":	"<?php echo fms_get_address() 		?>",
						"addressLocality":	"<?php echo fms_get_city() 			?>",
						"addressRegion":	"<?php echo fms_get_province() 		?>",
						"postalCode":		"<?php echo fms_get_postal_code() 	?>"
					}
				}
			
			</script>
			
			<!-- FMS: End -->
			
			<?php
		}
	}
	add_action("wp_head", "fms_seo_support_header", 0);
	

	
	// Because playing nice is hard when everyone is fighting over meta tags...
	// If the Retriever SEO has not been disabled, we want to make sure that we don't get conflicts from anyone else.
	// Here we'll turf some common 3rd party SEO plugin function that will interefere with our FMS specific code
	
	
	// **********************************************************************************************************************************
	//
	// 		Disable metaboxes that get added to listing posts
	//
	// **********************************************************************************************************************************
	
	function fms_seo_support_remove_metaboxes() {
		
		if (!fms_get_option("disable_retriever_seo", false)) {
				
		
			// YOAST:
			// Disable the metabox that gets added to the post editor
			remove_meta_box("wpseo_meta", "fms_listing", "normal");
			
			// END YOAST
			
			
			
			
			
		}
	}
	add_action("add_meta_boxes", "fms_seo_support_remove_metaboxes", 11);
	
	
	// **********************************************************************************************************************************
	//
	// 		Disable front end features (tag injection, etc)
	//
	// **********************************************************************************************************************************
	
	function fms_seo_support_disable_features() {
		
		global $wp_query;
		global $post;

		if (($post && $post->post_type == "fms_listing") && (is_singular() || count($wp_query->posts) == 1) && !fms_get_option("disable_retriever_seo", false)) {

			
			// YOAST:
			// Remove features that inject tags into the frontend
			if (defined("WPSEO_VERSION")) {
				
				$wpseo_front = WPSEO_Frontend::get_instance();
				
				remove_action("wp_head", array($wpseo_front, "head"), 1);
				
				remove_filter( 'pre_get_document_title', array( $wpseo_front, 'title' ), 15 );
				remove_filter( 'wp_title', array( $wpseo_front, 'title' ), 15 );
			}
			// END YOAST
			
			
			
			
			
		}
	}
	add_action("wp", "fms_seo_support_disable_features", 10);
	
	
	
?>