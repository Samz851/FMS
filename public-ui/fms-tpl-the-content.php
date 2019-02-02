<?php defined("ABSPATH") or die("Access denied.");
	
// **********************************************************************************************************************
// *
// *	Basic Content Template
// *
// *	This template will be inserted into the document flow wherever the_content is called for fms_listing posts.
// *	This is a more advanced example of how the API can be used to get and format whatever data is needed.
// *
// **********************************************************************************************************************
	
?>
<div id="fms-listing-template">

	<?php if ($lead_photo = fms_get_lead_photo_url()): ?>
		
		<div class="fms-template-section">

			<img class="fms-lead-photo" src="<?php echo $lead_photo ?>" />

		</div>
	
	<?php endif ?>
			
	<div class="fms-template-section">
		
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
			
		<p class="fms-stat location"><?php echo fms_get_city_province() ?></p>
			
		<p class="fms-stat bedbath"><?php echo fms_get_bed_bath_string() ?></p>
		
		<p class="fms-stat mls">MLS#: <?php echo fms_get_mls_number() ?></p>
		
	</div>

	<?php if ($openhouses = fms_get_openhouse()): if (fms_is_openhouse()): ?>
	
		<div class="fms-template-section">
	
			<h3><?php echo __("Open House", "firstlook") ?></h3>

			<?php foreach ($openhouses as $oh):
				
				$s = $oh["openhouse_start"]; $e = $oh["openhouse_end"]; ?>
			
				<p>
				<?php echo date("l F jS", $s) ?> from <?php echo date("H:i", $s) ?> to <?php echo date("H:i", $e) ?>. 
				<?php echo $oh["openhouse_description"] ?>
				</p>
				
			<?php endforeach ?>
	
		</div>
	
	<?php endif; endif ?>

	<?php if ($description = fms_get_remarks()): ?>
	
		<div class="fms-template-section">
	
			<h3><?php echo __("Description", "firstlook") ?></h3>
			<p><?php echo $description ?></p>
	
		</div>
	
	<?php endif ?>

	<?php if ($photos = fms_get_photo_urls()): ?>
		
		<div class="fms-template-section">
		
			<h3><?php echo __("Photos", "firstlook") ?></h3>
			<?php
		
				// Use the photo viewer component
				include_once (FMS_DIR."/public-ui/components/fms-ui-component-photo-box.php");
				new FMSPhotoBox($photos, false);
			
			?>
		</div>
		
	<?php endif ?>

	<?php if ($details = fms_get_details()): ?>
	
		<div class="fms-template-section">
		
			<h3><?php echo __("Details", "firstlook") ?></h3>
			<table class="fms-data-table">
		
				<?php foreach ($details as $key => $val): ?>
				
					<tr>
						<td class="half"><?php echo $key ?></td>
						<td class="half"><?php echo $val ?></td>
					</tr>
				
				<?php endforeach ?>
		
			</table>
		
		</div>
	
	<?php endif ?>
	
	<?php if ($rooms = fms_get_rooms()): ?>
	
		<div class="fms-template-section">
	
			<h3><?php echo __("Rooms", "firstlook") ?></h3>					
			<table class="fms-data-table">
		
				<?php foreach ($rooms as $room): ?>
				
					<tr>
						<td class="third"><?php echo $room["room_name"] ?></td>
						<td class="third"><?php echo $room["room_level"] ?></td>
						<td class="third"><?php echo $room["room_dimensions"] ?></td>
					</tr>
				
				<?php endforeach ?>
		
			</table>
		
		</div>
		
	<?php endif ?>

	<?php if ($gmaps_key = fms_get_option("google_maps_key", false)): ?>
	
		<div class="fms-template-section">
	
			<h3><?php echo __("Map", "firstlook") ?></h3>
			<?php
			
				// Use the mapping component
				include_once (FMS_DIR."/public-ui/components/fms-ui-component-listing-map.php");
				new FMSListingMap($gmaps_key);
			
			?>
		</div>
	
	<?php endif ?>

	<?php if ($links = fms_get_media_links()): ?>
	
		<div class="fms-template-section">
	
			<h3><?php echo __("Media Links", "firstlook") ?></h3>					
			<table class="fms-data-table">
		
				<?php foreach ($links as $link): ?>
				
					<tr>
						<td><a href="<?php echo $link["link_url"] ?>"><?php echo $link["link_name"] ?></a></td>
					</tr>
				
				<?php endforeach ?>
		
			</table>
		
		</div>
		
	<?php endif ?>

	<div class="fms-template-section fms-trademark-container">
		
		<div class="tm crea">
		
			<span>&#57348;</span>
			<span>&#57347;</span>
			<div>MLS®, REALTOR®, and the associated logos are trademarks of The Canadian Real Estate Association.</div>
			
		</div>
		
		<? if (fms_get_option("show_firstlook_powered_by", false)): ?>
		
			<div class="tm fms">
				<a href="http://myfirstlook.ca">
			
				<span>&#57345;</span>
				<div>Listing integration powered by Firstlook Media Solutions INC. myfirstlook.ca.</div>
			
				</a>
			</div>
		
		<? endif ?>
		
	</div>

</div>
