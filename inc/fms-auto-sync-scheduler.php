<?php defined("ABSPATH") or die("Access denied.") ?>
<?php

	if (defined("FMS_MAIN_FILE")) {
		
		// **********************************************************************************************************************************
		// 		Action when the auto sync runs
		// **********************************************************************************************************************************
		
		function fms_listing_auto_sync() {
			
			if (function_exists("fms_fetch_member_feed")) {
				
				fms_fetch_member_feed(true); 
				
				// Note: The "true" parameter makes sure the listings are cached!
			}
		}
		add_action("fms_member_fetch_cron", "fms_listing_auto_sync");
		
		
		// **********************************************************************************************************************************
		// 		Action when the safety check runs
		// **********************************************************************************************************************************
		
		function fms_restart_fetch_if_failed() {
			
		    if (!wp_next_scheduled("fms_member_fetch_cron")) {
		        
		        wp_schedule_event(time() + 3600, "hourly", "fms_member_fetch_cron");
		        
		        fms_listing_auto_sync();
		    }
		}
		add_action("fms_fetch_safety_check", "fms_restart_fetch_if_failed");
		

		// **********************************************************************************************************************************
		// 		Start the auto-sync
		// **********************************************************************************************************************************
		
		function fms_start_auto_sync() {
		
			// Clear and restart the scheduled fetch
			wp_clear_scheduled_hook("fms_member_fetch_cron");
			wp_schedule_event(time() + 3600, "hourly", "fms_member_fetch_cron");
			
			// Add a safety check to re-kick the task if it failed
			wp_clear_scheduled_hook("fms_fetch_safety_check");
			wp_schedule_event(time() + 3600, "hourly", "fms_fetch_safety_check");
		
			echo __("Fetch schedule started. Next auto fetch will occur in 1 hour.");
			exit();	
		}
		add_action("wp_ajax_start_auto_sync", "fms_start_auto_sync");
		
		
		// **********************************************************************************************************************************
		// 		Stop the auto-sync
		// **********************************************************************************************************************************
		
		function fms_stop_auto_sync() {
			
			// Clear the scheduled fetch
			wp_clear_scheduled_hook("fms_member_fetch_cron");
			
			// Clear safety check
			wp_clear_scheduled_hook("fms_fetch_safety_check");
			
			echo __("Fetch schedule has been stopped.");
			exit();	
		}
		add_action("wp_ajax_stop_auto_sync", "fms_stop_auto_sync");
		

		// **********************************************************************************************************************************
		// 		Disable auto-sync when the plugin is deactivated
		// **********************************************************************************************************************************
		
		function fms_remove_sync_schedule() {
		
			// Clear the scheduled fetch
			wp_clear_scheduled_hook("fms_member_fetch_cron");
			
			// Clear safety check
			wp_clear_scheduled_hook("fms_fetch_safety_check");
		}
		register_deactivation_hook(FMS_MAIN_FILE, "fms_remove_sync_schedule");
	}
	
?>