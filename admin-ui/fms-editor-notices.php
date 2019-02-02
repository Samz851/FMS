<?php defined("ABSPATH") or die("Access denied.") ?>
<?php
	
	
	// **********************************************************************************************************************************
	// 		Show notice if the listing is not visible
	// **********************************************************************************************************************************
	
	function fms_listing_visibility_notice() {
		
		global $post; $screen = get_current_screen();
		
		if ($screen->id == "fms_listing") {
			if ($post->post_type == "fms_listing" && $post->post_status == "private") {
				
			    /*<?php*/ ?>
			    
				    <div class="error notice">
				        <p><?php echo __("The visibility of this listing is currently set to \"Private,\" which will prevent it from being displayed.", "firstlook" ) ?></p>
				    </div>
			    
			    <?php /*?>*/  
		    }
	    }
	}
	add_action("admin_notices", "fms_listing_visibility_notice");
	
	
	// **********************************************************************************************************************************
	// 		Show notice if sync blocks are turned on
	// **********************************************************************************************************************************
	
	function fms_listing_sync_settings_notice() {
		
		if (defined("FMS_META_PRE")) {
		
			global $post; $screen = get_current_screen();
			
			if ($screen->id == "fms_listing") {
				if ($post->post_type == "fms_listing") {
					
					if (get_post_meta($post->ID, FMS_META_PRE."block_sync_all", true)) {
						
						/*<?php*/ ?>
			    
						    <div class="error notice">
						        <p><?php echo __("Full sync block is enabled. This listing will not update on its own!", "firstlook" ) ?></p>
						    </div>
					    
					    <?php /*?>*/  
						
					} else {
						
						if (get_post_meta($post->ID, FMS_META_PRE."block_sync_photos", true)) {
							
							/*<?php*/ ?>
			    
							    <div class="error notice">
							        <p><?php echo __("Photo sync block is enabled.", "firstlook" ) ?></p>
							    </div>
						    
						    <?php /*?>*/ 
						}
						
						if (get_post_meta($post->ID, FMS_META_PRE."block_sync_address", true)) {
							
							/*<?php*/ ?>
			    
							    <div class="error notice">
							        <p><?php echo __("Title (address) sync block is enabled.", "firstlook" ) ?></p>
							    </div>
						    
						    <?php /*?>*/ 
						}
						
						if (get_post_meta($post->ID, FMS_META_PRE."block_sync_remarks", true)) {
							
							/*<?php*/ ?>
			    
							    <div class="error notice">
							        <p><?php echo __("Content (public remarks) sync block is enabled.", "firstlook" ) ?></p>
							    </div>
						    
						    <?php /*?>*/ 
						}
						
						if (get_post_meta($post->ID, FMS_META_PRE."block_sync_status", true)) {
							
							/*<?php*/ ?>
			    
							    <div class="error notice">
							        <p><?php echo __("Listing status sync block is enabled.", "firstlook" ) ?></p>
							    </div>
						    
						    <?php /*?>*/ 
						}
					}
				} 
			}
		}
	}
	add_action("admin_notices", "fms_listing_sync_settings_notice");

?>