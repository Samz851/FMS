<?php defined("ABSPATH") or die("Access denied.");
	
// **********************************************************************************************************************
// *
// *	Basic Excerpt Template
// *
// *	This template will be inserted into the document flow wherever the_excerpt is called for fms_listing posts.
// *	This is the simplest example of how the API functions can be used to pull data on the current post.
// *
// **********************************************************************************************************************
	
?>
<div id="fms-listing-template">

	<?php if ($lead_photo = fms_get_lead_photo_url()): ?>
		
		<div class="fms-template-section">
			
			<a href="<?php echo fms_the_permalink() ?>">
				
				<img class="fms-lead-photo" src="<?php echo $lead_photo ?>" />
				
			</a>

		</div>
	
	<?php endif ?>
			
	<div class="fms-template-section">
		
		<a class="fms-link-wrapper" href="<?php echo fms_the_permalink() ?>">
			
			<div class="fms-h-group">
	
				<?php if ($price = fms_get_formatted_price()): ?>

					<h3 class="fms-stat price"><?php echo $price ?></h3>
				
				<?php endif ?>
				
				<? switch(fms_get_status()) {
						
					case fms_listing_statuses::$ACTIVE_SALE:
					case fms_listing_statuses::$ACTIVE_LEASE:
					
					?><h3 class="fms-stat status"><?php echo fms_get_status_text() ?></h3><?
					
					break;
					
				} ?>

			</div>
		
		</a>
			
		<p class="fms-stat location"><?php echo fms_get_city_province() ?></p>
			
		<p class="fms-stat bedbath"><?php echo fms_get_bed_bath_string() ?></p>
		
		<p class="fms-stat mls">MLS#: <?php echo fms_get_mls_number() ?></p>
		
	</div>
	
</div>
