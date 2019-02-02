<?php defined("ABSPATH") or die("Access denied.") ?>
<?php

	class FMSListingMap {
		
		private $key;
		private $data;
		private $jsid;
		
		public function __construct($key, $data = array()) {
			
			$this->key 	= $key;
			$this->data = $data;
			$this->jsid = get_class($this)."_".rand(100, 999);
			
			if (empty($this->data)) {
				$this->data = array(array(
						
					"link" 		=> fms_the_permalink(),
					"latitude" 	=> fms_get_latitude(),
					"longitude" => fms_get_longitude(),
					"address"	=> array(
					
						fms_get_address_city_province(),
						fms_get_full_address(),				// First address to try if lat/lon aren't present
						//fms_get_city_province(),			// Backup if first address fails
					),
				));
			}

			echo $this->html();
			echo $this->js();	
		}
		
		private function html() {
			
			ob_start(); ?>
			
				<div id="fms-listing-map" data-jsid="<?php echo $this->jsid ?>"></div>
			
			<?php return ob_get_clean();
		}
		
		private function js() { 
				
			ob_start(); ?>
			
				<script type="text/javascript" data-jsid="<?php echo $this->jsid ?>">
					
					function gmapsInit() {
					
						jQuery(document).ready(function($) {
							
							var mapBox 		= $("div#fms-listing-map[data-jsid='<?php echo $this->jsid ?>']");
							var mapData 	= <?php echo json_encode($this->data) ?>;
							
							(function() {
								
								// Map options
								var bounds 		= new google.maps.LatLngBounds();
								var mapOptions 	= {
									
							        mapTypeId: 			"roadmap",
							        streetViewControl: 	false,
							        scrollwheel: 		false,
							        //draggable: 		!("ontouchend" in document), // Disable dragging for mobile only
									fullscreenControl: 	true,
									streetViewControl:	true,
				        
							        //center: 			{ lat: 45.390606, lng: -75.756895 },
									//zoom: 			15,
							    };
								
								// Create a new map object in this widgets map box
								var map = new google.maps.Map(mapBox.get(0), mapOptions);
								var geocoder = new google.maps.Geocoder();





								// Create a marker on the map!
								var createMarker = function(map, position, current) {
											
											
											
									
									console.log(position);
									
											
											
											
									var marker = new google.maps.Marker({  
										             
										position: 	position, 
										map: 		map,
										data: 		current   
									}); 
									
									// Listen for clicks on the map markers
									google.maps.event.addListener(marker, "click", function() { 
										
										window.location.href = this.data.link; // Goodbye, enjoy the listing page!
									});
									
									if (mapData.length === 1) {
									
										// Create the street view object
										streetView = new google.maps.StreetViewPanorama(mapBox.get(0), {
								
											position: 			position,
											addressControl:		false,
											linksControl: 		false,
											panControl: 		false,
											enableCloseButton: 	false
										});
										
										// Make sure the street view overlay is hidden
										streetView.setVisible(false);
									}
									
									// Fit the map to the updated bounds
									bounds.extend(position);
									map.fitBounds(bounds);
									
									//map.setZoom(15); 
									
									zoomListener = google.maps.event.addListenerOnce(map, "bounds_changed", function(event) {
										if (this.getZoom()) this.setZoom(14);
									});
									
									setTimeout(function() {
										google.maps.event.removeListener(zoomListener);
									}, 2000);
									
								};




								







								// Plot the data!
								if (mapData.length > 0) {
									
									var streetView = null;
									
									for (var i = 0; i < mapData.length; i++) {
									
										var current = mapData[i];




										// If we have lat/lon data from the DB, use that to create the position marker
										if (current.latitude && current.longitude) {
											
											console.log("Using location from database.");
											
											var position = { 
												
												lat: parseFloat(current.latitude),
												lng: parseFloat(current.longitude), 
											};
											
											createMarker(map, position, current);
										} 

										// If we don't have lat/lon but we have an address, try to geocode
										else if (current.address[0]) {

											(function(current) {
											
												console.log("No location in database, trying to geocode instead...");
												console.log("Searching for: "+ current.address[0]);
												
												geocoder.geocode({ "address": current.address[0] }, function(results, status) {	

													console.log("Geocoder status: "+ status);
													
													if (status == google.maps.GeocoderStatus.OK) {
		
														console.log("Using geocoded position from address 1.");
														createMarker(map, results[0].geometry.location, current);
													}
													
													else if (current.address[1]) {
														
														console.log("Could not locate address 1, trying to find address 2...");
														console.log("Searching for: "+ current.address[1]);
														
														geocoder.geocode({ "address": current.address[1] }, function(results, status) {	

															console.log("Geocoder status: "+ status);
															
															if (status == google.maps.GeocoderStatus.OK) {
				
																console.log("Using geocoded position from address 2.");
																createMarker(map, results[0].geometry.location, current);
															}
															
															else console.log("Could not geocode position!");	
															
														});
													}
												});
											
											})(current);
										}
										
										// Not much to do with no lat/lon and no address...
										else console.log("Could not map location! Not enough data.");
									}
								}

								// Make sure the map stays centered when the canvas resizes
								$(window).on("resize", function() {
									
									var center = map.getCenter();
									google.maps.event.trigger(map, "resize");
									map.setCenter(center); 
									
								});
							
							})();
						});
					}
				</script>
				
				<script type="text/javascript" 
					src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->key ?>&callback=gmapsInit" async defer>
				</script>
			
			<?php return ob_get_clean();
		}
	}
	
?>