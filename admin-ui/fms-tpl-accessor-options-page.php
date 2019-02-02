<?php defined("ABSPATH") or die("Access denied.") ?>

<div id="fms-plugin-options" class="wrap cmb2-options-page">
					
	<h1><img src="<?= FMS_URL ?>lib/icons/retriever-orange.png">Firstlook Retriever Settings</h1>
	
	<div class="nav-tab-section">
					
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab nav-tab-active" href="#">Data Feeds</a>
			<a class="nav-tab" href="#">Retriever Options</a>
		</h2>
	
		<div id="accessor-options" class="fms-options-section">
		
			<?php if (!empty($GLOBALS["fms_accessor_classes"])): ?>
		
				<?php foreach ($GLOBALS["fms_accessor_classes"] as $accessor_class) {
					
					$accessor = new $accessor_class;
					$accessor->fms_render_options_form($this->key);
					
				} ?>
				
				<div id="sync-controls">
				
					<h1>After saving the options above:</h1>
					
					<hr>
					<div id="member-sync-controls" class="fms-options-section">
					
						<h3>If you're using a member feed:</h3>
						
						<ol>
							<li>Click the Fetch Member Feed button to do an initial pull of your listings.</li>
							<li>Click Start Auto Fetch to have the system automatically check for listing updates every hour.</li>
							<li>Auto fetch functionality may be stopped at any time using the Stop Auto Fetch button.</li>
						</ol>
						
						<p><strong>
						<span id="sync-status">
						<?php
							
							if (wp_next_scheduled("fms_member_fetch_cron")) { 
								
								$diff = round(abs(time() - wp_next_scheduled("fms_member_fetch_cron")) / 60, 0);
								
								if ($diff != 0) echo "Next auto fetch will occur in $diff minutes.";
								else echo "Next auto fetch will occur in 1 hour.";
							}
							
							else echo "Auto fetch is currently turned off. Click Start to activate.";
							
						?>
						</span>
						</strong></p>
						
						<p>
						<button id="member-fetch" 	type="button" class="button button-large hide-if-no-js">Fetch Member Feed</button>
						<button id="start-sync" 	type="button" class="button button-large hide-if-no-js">Start Auto Fetch</button>
						<button id="stop-sync" 		type="button" class="button button-large hide-if-no-js">Stop Auto Fetch</button>
						</p>
						
						<div id="member-fetch-output"></div>
						
					</div>
					
					
					<hr>
					<div id="national-test-controls" class="fms-options-section">
					
						<h3>If you're using a national shared pool feed:</h3>
						
						<ol>
							<li>Click Test National Feed to verify that your national feed ID is working properly.</li>
							<li>Fetch controls aren't necessary since national pool listings are never downloaded to your site.</li>
						</ol>
					
						<p>
						<button id="national-test" 	type="button" class="button button-large hide-if-no-js">Test National Shared Pool Feed</button>
						</p>
					
						<div id="national-fetch-output"></div>
					
					</div>

				</div>
				
			<?php else: ?>
			
				<?php echo "No accessors installed." ?>
				
			<?php endif ?>
	
		</div>
		
	</div>

	<div class="nav-tab-section">
					
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab" href="#">Data Feeds</a>
			<a class="nav-tab nav-tab-active" href="#">Retriever Options</a>
		</h2>

		<?php cmb2_metabox_form($this->metabox_id, $this->key, array(
			
			"save_button" => __("Save Options", "firstlook"),
				
		)) ?>
		
	</div>

	<script>
		jQuery(document).ready(function($) {

			(function() {
				
				// Listen for tab changes
				$("a.nav-tab").on("click", function(e) {
					
					e.preventDefault();
					
					$("div.nav-tab-section").hide();
					$("div.nav-tab-section").eq($(this).index()).show();
					
					// Add the current index to the url hash so the current tab will stay selected
					history.replaceState(null, null, "#"+ $(this).index());
					
					return false;
				});
	
				// Check if there is a tab selected already
				var hash = window.location.hash;
				if (hash && hash.charAt(0) == "#") {
					hash = hash.slice(1);
					
					// Show the page corresponding to the current hash number
					// It's cheap but it works!
					$("div.nav-tab-section").hide();
					$("div.nav-tab-section").eq(hash).show();
				}
				else {
					
					// Default state, show the first tab if no hash (first load)
					$("div.nav-tab-section").eq(0).show();
				}
				
			})();
			
			(function() {
				
				// **********************************************************************************************************************************
				//
				// 		Start and stop the auto-sync schedule
				//
				// **********************************************************************************************************************************
				
				$("button#start-sync").on("click", function(e) {
					
					e.preventDefault();
					
					$("#sync-status").empty();						
					$.post(ajaxurl, { action: "start_auto_sync" }, function(data_in) {

						$("#sync-status").html(data_in);
						
					});
				});
				
				$("button#stop-sync").on("click", function(e) {
					
					e.preventDefault();
					
					$("h3#sync-status").empty();
					$.post(ajaxurl, { action: "stop_auto_sync" }, function(data_in) {

						$("#sync-status").html(data_in);
						
					});
				});
				
				
				// **********************************************************************************************************************************
				//
				// 		Manually fetch Member Feed data
				//
				// **********************************************************************************************************************************

				$("button#member-fetch").on("click", function(e) {
					
					e.preventDefault();

					var btn = $(this);

					btn.addClass("disabled");
					btn.css({ "pointer-events": "none" });

					$("div#member-fetch-output").empty();
					$("div#member-fetch-output").append("<div class='feed-refreshing'><i class='fa fa-refresh fa-spin fa-fw'></i> Loading...</div>");
					
					var dataOut = { 
						
						action:		"fms_fetch_member_feed",
						echo_ob:	true,
						will_cache:	true,
					};
	
					var xhr = $.post(ajaxurl, dataOut, function(dataIn) {

						$("div#member-fetch-output").empty();
						$("div#member-fetch-output").text(dataIn);
						
					}).fail(function(xhr, status, error) {
						
						$("div#member-fetch-output").empty();
						$("div#member-fetch-output").text("Process did not fully complete ["+ error +"]");
					  
					}).always(function() {
						
						btn.removeClass("disabled");
						btn.css({ "pointer-events": "all" });
						
					});
					
					
					/*
					function(xhr, status, error) {
						
						var err = JSON.parse(xhr.responseText);
						alert(err.Message);
					  
					});
					*/

					/*
					$("div#fetch-output").empty();
					$("div#fetch-output").append("<div class='feed-refreshing'><i class='fa fa-refresh fa-spin fa-fw'></i> Loading...</div>");
					
					var dataOut = { 
						
						action:		"fms_fetch_member_feed",
						echo_ob:	true,
						will_cache:	true,
					};
	
					var xhr = $.post(ajaxurl, dataOut, function(dataIn) {

						$("div#fetch-output").empty();
						$("div#fetch-output").text(dataIn);
						
					}).fail(function() {
						
						$("div#fetch-output").empty();
						
						console.log(xhr.responseText);
						
						alert("Error fetching member feed. HTTP Status: "+ xhr.status);
						
					});
					*/
				});
				
				
				// **********************************************************************************************************************************
				//
				// 		Manually test the National Feed access
				//
				// **********************************************************************************************************************************
				
				$("button#national-test").on("click", function(e) {
					
					e.preventDefault();
					
					var btn = $(this);
					
					btn.addClass("disabled");
					btn.css({ "pointer-events": "none" });
					
					var testSearch = "?"+ $.param({
						
						"city":		"Vancouver",
						"province":	"British Columbia",
					});
					
					var dataOut = { 
						
						action:		"fms_fetch_national_feed",
						echo_ob:	true,
					};
					
					$("div#national-fetch-output").empty();
					$("div#national-fetch-output").append("<div class='feed-refreshing'><i class='fa fa-refresh fa-spin fa-fw'></i> Loading...</div>");
					
					$.post(ajaxurl + testSearch, dataOut, function(dataIn) {

						console.log(dataIn);
						
						$("div#national-fetch-output").text(dataIn);
						
						btn.removeClass("disabled");
						btn.css({ "pointer-events": "all" });
					});
					
					/*
					$("div#fetch-output").empty();
					$("div#fetch-output").append("<div class='feed-refreshing'><i class='fa fa-refresh fa-spin fa-fw'></i> Loading...</div>");
					
					$.post(ajaxurl + testSearch, dataOut, function(dataIn) {

						console.log(dataIn);
						
						$("div#fetch-output").text(dataIn);
						
					});
					*/
				});
	
			})();
			
		});
	</script>

</div>