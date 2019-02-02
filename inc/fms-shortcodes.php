<?php defined("ABSPATH") or die("Access denied.") ?>
<?php




	// **********************************************************************************************************************************
	//
	//		Display a grid of listings
	// 
	// **********************************************************************************************************************************

	function fms_shortcode_listing_grid($atts) {
		
		//global $wp_query;
		global $post;
		
		$a = shortcode_atts(array(
			
			"query" 		=> "",		// Optional query string to submit to the search gateway
			"use_current" 	=> "true",	// Should the current query string be used?
			
		), $atts);
		
		// Figure out what the "user_current" and query string values should be
		$use_current 	= (($a["use_current"] == "true" || $a["use_current"] == "1") ? true : false);
		$query 			= (($use_current && !empty($_SERVER["QUERY_STRING"])) ? $_SERVER["QUERY_STRING"] : $a["query"]);
		
		// If there is a query set, make sure that it has the question mark
		if (!empty($query) && $query[0] != "?") $query = "?". $query;

		$listings = array();
		
		// Search the national shared pool
		if (!empty($query)) {
			
			$listingdata = fms_fetch_national_feed_inline(false, $query);
			
			foreach ($listingdata as $ld) {
				
				$listings[] = fms_create_virtual_listing_post($ld);
			}
		}
		
		// Get member feed listings (and default search) listings
		else {
			
			$listing_query = new WP_Query(array(
				
				"post_type"			=> "fms_listing",
				"numberposts"		=> -1,
				"posts_per_page"	=> -1,
				
			));
			
			$listings = fms_the_posts_filter($listing_query->posts, $listing_query);	
		}

		$jsid = "grid_".rand(100, 999);

		ob_start() ?>
		
			<div class="fms-listing-grid" id="<?php echo $jsid ?>">
		
				<?php foreach ($listings as $post): fms_the_post_action($post); setup_postdata($post); ?>
				
					<div class="fms-grid-card">
						
						<a href="<?php echo get_the_permalink() ?>">
							<div class="fms-grid-card-photo" style="background-image:url(<?php echo fms_get_lead_photo_url() ?>)">
								
								<div class="fms-grid-status-banner"><?php echo fms_get_status_text() ?></div>
								
							</div>
						</a>
						
						<div class="fms-grid-price"><?= fms_get_formatted_price() ?></div>
						
						<div class="fms-grid-bedbath">
						<?php
							
							$bb_string = fms_get_bed_bath_string();
							
							if (empty($bb_string)) $bb_string = "<br>";
							
							echo $bb_string;
							
						?>
						</div>
						
					</div>				
				
				<?php endforeach; wp_reset_postdata(); ?>
			
				<script>
					jQuery(document).ready(function($) {
				
						var $grid 	= $("div#<?php echo $jsid ?>");
						var $cards	= $("div.fms-grid-card", $grid);
						
						var cardStyle 		= $cards[0].currentStyle || window.getComputedStyle($cards[0]);
						var cardsMinWidth	= parseInt(cardStyle.minWidth);
						var cardsMargin		= parseInt(cardStyle.marginLeft) +  parseInt(cardStyle.marginRight);
						
						var adjustFn = function() {
						
							var gridWidth		= $grid.outerWidth();
							var adjust			= ((gridWidth % (cardsMinWidth + cardsMargin)) / Math.trunc(gridWidth / (cardsMinWidth + cardsMargin)));
							
							$cards.css({ "width": (cardsMinWidth + adjust) +"px" });
						};
						
						adjustFn();
						$(window).on("resize", adjustFn);				
					});
				</script>
				
			</div>

		<?php return ob_get_clean();
	}
	add_shortcode("listing_grid", "fms_shortcode_listing_grid");
	
	
	
	
	
	
?>