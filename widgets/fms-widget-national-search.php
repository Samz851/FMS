<?php defined('ABSPATH') or die("Access denied.") ?>
<?php

	class fms_widget_national_search extends WP_Widget {
	
		public function __construct() {
			parent::__construct("fms_widget_national_search", "FMS National Search", array("classname" => "fms_widget_national_search"));
		}
		
		public function widget($args, $instance) {

			extract($args);
			
			$title 	= (!empty($instance["title"]) 	? $instance["title"] 	: __("DDF Listing Search", 	"firstlook"));
			$more	= (!empty($instance["more"]) 	? $instance["more"] 	: __("Advanced Search", 	"firstlook"));
			$less	= (!empty($instance["less"]) 	? $instance["less"] 	: __("Basic Search", 		"firstlook"));
			$button	= (!empty($instance["button"]) 	? $instance["button"] 	: __("Search", 				"firstlook"));
			
			$jsid = "fms_search_".rand(100, 999);
			
			echo $before_widget;

			echo $before_title . $title . $after_title;
			
			if (fms_is_ddf_national_search_available()): ?>
			
				<div id="fms-remote-search-box" data-id="<?php echo $jsid ?>">
					
					<form class="fms-search-form">
						
						<div id="basic-search" class="form-section">
						
							<!-- MLS NUMBER -->
							<input type="text" id="mlsNumber" placeholder="MLS® Number" />
							
							<!-- CITY -->
							<input type="text" id="city" placeholder="City" />
							
							<!-- PROVINCE -->
							<div class="styled-select">
							<select id="province">
								<option value="">Select Province</option>
								
								<option value="Alberta">Alberta</option>
								<option value="British Columbia">British Columbia</option>
								<!--<option value="Manitoba">Manitoba</option>-->
								<option value="New Brunswick">New Brunswick</option>
								<option value="Newfoundland">Newfoundland & Labrador</option>
								<!--<option value="Northwest Territories">Northwest Territories</option>-->
								<option value="Nova Scotia">Nova Scotia</option>
								<!--<option value="Nunavut">Nunavut</option>-->
								<option value="Ontario">Ontario</option>
								<option value="Prince Edward Island">Prince Edward Island</option>
								<!--<option value="Quebec">Québec</option>-->
								<!--<option value="Saskatchewan">Saskatchewan</option>-->
								<!--<option value="Yukon">Yukon</option>-->
								
							</select>
							<i class="fa fa-angle-down"></i>
							</div>

						</div>
						
						<div class="form-section">
							
							<!-- ADVANCED SEARCH LINK -->
							<a title="<?php echo __("Toggle search form fields", "firstlook") ?>" id="advanced-toggle" href=""><?php echo $more ?></a>
							
						</div>
						
						<div id="advanced-search" class="form-section">
						
							<!-- POSTAL CODE -->
							<input type="text" id="postal" placeholder="Postal Code" />
							
							<!-- NEIGHBOURHOOD -->
							<input type="text" id="neighborhood" placeholder="Neighbourhood" />
							
							<!-- TYPE (???) -->
							<!--<input type="text" id="type" placeholder="Type" />-->
							
							<!-- MIN PRICE -->
							<div class="styled-select">
							<select id="minPrice">
								<option value="">Min Price</option>
								<option value="100000">$100,000</option>
								<option value="200000">$200,000</option>
								<option value="300000">$300,000</option>
								<option value="400000">$400,000</option>
								<option value="500000">$500,000</option>
								<option value="600000">$600,000</option>
								<option value="700000">$700,000</option>
								<option value="800000">$800,000</option>
								<option value="900000">$900,000</option>
								<option value="1000000">$1,000,000</option>
								<option value="2000000">$2,000,000</option>
								<option value="3000000">$3,000,000</option>
								<option value="4000000">$4,000,000</option>
								<option value="5000000">$5,000,000</option>
								<option value="6000000">$6,000,000</option>
								<option value="7000000">$7,000,000</option>
								<option value="8000000">$8,000,000</option>
								<option value="9000000">$9,000,000</option>
								<option value="10000000">$10,000,000</option>
							</select>
							<i class="fa fa-angle-down"></i>
							</div>
							
							<!-- MAX PRICE -->
							<div class="styled-select">
							<select id="maxPrice">
								<option value="">Max Price</option>
								<option value="100000">$100,000</option>
								<option value="200000">$200,000</option>
								<option value="300000">$300,000</option>
								<option value="400000">$400,000</option>
								<option value="500000">$500,000</option>
								<option value="600000">$600,000</option>
								<option value="700000">$700,000</option>
								<option value="800000">$800,000</option>
								<option value="900000">$900,000</option>
								<option value="1000000">$1,000,000</option>
								<option value="2000000">$2,000,000</option>
								<option value="3000000">$3,000,000</option>
								<option value="4000000">$4,000,000</option>
								<option value="5000000">$5,000,000</option>
								<option value="6000000">$6,000,000</option>
								<option value="7000000">$7,000,000</option>
								<option value="8000000">$8,000,000</option>
								<option value="9000000">$9,000,000</option>
								<option value="10000000">$10,000,000</option>
							</select>
							<i class="fa fa-angle-down"></i>
							</div>
							
							<!-- MIN BEDROOMS -->
							<div class="styled-select">
							<select id="minBeds">
								<option value="">Min Bedrooms</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
							</select>
							<i class="fa fa-angle-down"></i>
							</div>
							
							<!-- MIN BATHROOMS -->
							<div class="styled-select">
							<select id="minBaths">
								<option value="">Min Bathrooms</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
							</select>
							<i class="fa fa-angle-down"></i>
							</div>
						
						</div>
		
						<div class="form-button-container">
		
							<button id="submit" class="fms-button" type="submit" 
									title="<?php echo __("Submit search form", "firstlook") ?>"><?php echo $button ?></button>
							
							<button id="clear" class="fms-button" type="clear" 
									title="<?php echo __("Clear search form", "firstlook") ?>"><i class="fa fa-close"></i></button>
		
						</div>
		
					</form>
					<script>
						jQuery(document).ready(function($) {
							
							var targetForm 		= $("div#fms-remote-search-box[data-id=<?php echo $jsid ?>] form");
							
							var advancedToggle	= $("a#advanced-toggle", 	targetForm);
							var basicFields		= $("div#basic-search", 	targetForm);
							var advancedFields	= $("div#advanced-search", 	targetForm);
							
							var inputs			= $("input[type=text], select", targetForm);
							var required		= [ "city", "province" ];
							
							var missingFields	= [];


							// Toggle between the basic and advanced search forms
							var isAdvancedForm 	= false;
							var toggleText		= advancedToggle.text();
							
							var openAdvancedForm = function() {
								
								advancedFields.show();
								advancedToggle.text("<?php echo $less ?>");
								isAdvancedForm = true;
							}
							
							var closeAdvancedForm = function() {
								
								advancedFields.hide();
								advancedToggle.text(toggleText);
								isAdvancedForm = false;
							}
							
							var toggleAdvancedForm = function() {

								if (!isAdvancedForm) openAdvancedForm();
								else closeAdvancedForm();
							}
							
							advancedToggle.on("click", function(e) {
								
								e.preventDefault();
								toggleAdvancedForm();
							});
	
	
							// Validate and submit the form
							(function() {
								
								function submitSearch() {
								
									var missingRequired = false;
									var mlsFound		= false;
									var params 			= { };
		
									inputs.each(function() {
										
										var id 		= $(this).attr("id");
										var entry 	= $(this).val().trim();
										var box 	= $(this);
										
										// Correct Formatting where required * * *
										
										if (id === "postal") entry = entry.replace(/\s+/g, ''); // Remove spaces from postal code
										
										// * * *
										
										// Capture the field value
										if (entry !== "") params[id] = entry;
										
										// If MLS number is set, ignore everything else
										if (id === "mlsNumber" && entry !== "") {
	
											missingRequired = false;
											mlsFound		= true;
											
											/*
											params = {};
											params[id] = entry;
											return false;
											*/
										}
										
										// If a required field is missing...
										if (!mlsFound) {
											
											if (entry === "" && $.inArray(id, required) > -1) {
											
												missingRequired = true;	
												missingFields.push($(this));
											}
										}
										
									});								
	
									if (!missingRequired) {

										// DISABLE THE BUTTONS!

										$("button", targetForm).css({ 
										
											"pointer-events": 	"none",
											"opacity":			"0.8",
											
										}).prop("disabled", true);
										
										$("button[type=submit]", targetForm).html("<i class='fa fa-refresh fa-spin fa-fw'></i>");

										// DO THE SEARCH!

										var params = $.param(params);

										// The AJAX way
										// NOTE: This will run the search method async, which will cache the results
										/**/
										var ajaxurl = "<?php echo admin_url("admin-ajax.php") ?>";

										var searchParams = "?" + params;
										
										var dataOut = { action: "fms_fetch_national_feed" }
										
										$.post(ajaxurl + searchParams, dataOut, function(dataIn) {
											
											// Redirect to the same search URL that we just cached the results for.
											// Get the archive link from WordPress...
											var archiveLink = "<?php echo get_post_type_archive_link("fms_listing") ?>";
											
											// Check to see if we should append parameters or start a new query string (permalink structure dependant)
											if (archiveLink.indexOf("?") > -1) archiveLink += "&pool=national&"+ params;
											else archiveLink += "?pool=national&"+ params;
											
											// Goodbye!
											window.location.href = archiveLink;
											
										});
										/**/
										
										// Purely inline method
										// NOTE: This will just run an inline search
										// window.location.href = "<?= get_site_url() ?>/listings?fms=remote&"+ params;

									} else {
										
										$.each(missingFields, function() {
											
											$(this).addClass("missing-required");
											$(this).one("change", function() {
								
												$(this).removeClass("missing-required");
											});
										});
										
										// Clear the missing fields list
										missingFields = [];
									}
								}
								
								// When the form is clicked, allow the enter / return key to submit
								targetForm.on("focus", function(e) {
								
									document.onkeypress = function(e) {
										if (e.keyCode === 13) submitSearch();
									}
								});
								
								// Track the submit button press
								$("button[type=submit]", targetForm).on("click", function(e) {
									
									e.preventDefault();
									submitSearch();
									
									// Unfocus the search button
									$(this).blur();
								});
								
								// Track the submit clear press
								$("button[type=clear]", targetForm).on("click", function(e) {
									
									e.preventDefault();
									targetForm.get(0).reset();

									// Unfocus the search button
									$(this).blur();
								});
	
							})();

							// Map the query parameters back to the correct search fields
							(function() {
	
								$.each(getQueryParams(), function(key, value) {
									if (key && value) {
										
										value = decodeURIComponent(value).replace(/\+/g, " ");
										
										// For normal text inputs just show the text
										var input = $("input[type=text]#"+ key, targetForm).val(value);
										
										// For select inputs, do a case-insensitive match
										var option = $("select#"+ key +" option").filter(function() {
											
											if ($(this).attr("value").toLowerCase() === value.toLowerCase()) {
												$(this).prop("selected", true);
											}
											return $(this);
										});
										
										// Open the advanced form if the input or selected option is in the advanced form section
										if (advancedFields.has(input).length > 0 || advancedFields.has(option).length > 0) {
											openAdvancedForm();
										}	
									}
								});
								
								// Split the query parameters into a nice object
								function getQueryParams(str) {
									return (str || document.location.search)
									.replace(/(^\?)/,'').split("&")
									.map(function(n){return n = n.split("="),this[n[0]] = n[1],this}.bind({}))[0];
								}
							
							})();	
						});
					</script>
				
				</div>
				
			<?php else: ?>
			
				<div id="fms-remote-search-box" data-id="<?php echo $jsid ?>">
					
					<p>Search is currently unavailable! A National Shared Pool Transport ID is required.</p>
					
				</div>
			
			<?php endif;
			
			echo $after_widget;
		}

		public function form($instance) {
			
			$title 		= esc_attr(!empty($instance["title"]) 	? $instance["title"] 	: "");
			$more		= esc_attr(!empty($instance["more"]) 	? $instance["more"] 	: "");
			$less		= esc_attr(!empty($instance["less"]) 	? $instance["less"] 	: "");
			$button		= esc_attr(!empty($instance["button"]) 	? $instance["button"] 	: "");
			
			/*<?php*/ ?>
			
				<p>
				<?php $id = $this->get_field_id("title"); $name = $this->get_field_name("title"); ?>
				
	        	<label for="<?php echo $id ?>">Title:</label>
	        	<input class="widefat" id="<?php echo $id ?>" name="<?php echo $name ?>" type="text" value="<?php echo $title ?>" />
	        	
	        	<small><i>Will default to "DDF Listing Search" if left blank.</i></small>
		        </p>

		        <p>
				<?php $id = $this->get_field_id("more"); $name = $this->get_field_name("more"); ?>
				
	        	<label for="<?php echo $id ?>">Advanced Search Link Text:</label>
	        	<input class="widefat" id="<?php echo $id ?>" name="<?php echo $name ?>" type="text" value="<?php echo $more ?>" />
	        	
	        	<small><i>Will default to "More Fields" if left blank.</i></small>
		        </p>
		        
		        <p>
				<?php $id = $this->get_field_id("less"); $name = $this->get_field_name("less"); ?>
				
	        	<label for="<?php echo $id ?>">Basic Search Link Text:</label>
	        	<input class="widefat" id="<?php echo $id ?>" name="<?php echo $name ?>" type="text" value="<?php echo $less ?>" />
	        	
	        	<small><i>Will default to "Fewer Fields" if left blank.</i></small>
		        </p>

		        <p>
				<?php $id = $this->get_field_id("button"); $name = $this->get_field_name("button"); ?>
				
	        	<label for="<?php echo $id ?>">Search Button Text:</label>
	        	<input class="widefat" id="<?php echo $id ?>" name="<?php echo $name ?>" type="text" value="<?php echo $button ?>" />
	        	
	        	<small><i>Will default to "Search" if left blank.</i></small>
		        </p>

			<?php /*?>*/
		}

		public function update($new_instance, $old_instance) {
			
			$instance = $old_instance;
  
			$instance["title"]	= strip_tags($new_instance["title"]);
			$instance["more"]	= strip_tags($new_instance["more"]);
			$instance["less"]	= strip_tags($new_instance["less"]);
			$instance["button"]	= strip_tags($new_instance["button"]);
  
	        return $instance;
		}
	}
		
?>