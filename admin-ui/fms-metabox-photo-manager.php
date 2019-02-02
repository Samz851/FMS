<?php defined('ABSPATH') or die("Access denied.") ?>
<?php

	// **********************************************************************************************************************************
	// 		Add photo management metabox
	// **********************************************************************************************************************************
	
	function fms_add_photo_manager_metabox() {
		
		add_meta_box("fms-photo-metabox", "Photos", "fms_photo_manager_metabox", "fms_listing", "normal", "high");
	}
	add_action("add_meta_boxes", "fms_add_photo_manager_metabox");
	
	
	// **********************************************************************************************************************************
	// 		Display the custom photo editor
	// **********************************************************************************************************************************
	
	function fms_photo_manager_metabox($post, $metabox) {
		
		global $post;
	
		if (!$post || $post->post_type != "fms_listing") {
		
			echo "<h2>Post type not unsupported.</h2>";
			return;
		}
	
		$photos = get_post_meta($post->ID, "_fms_photos", true);
		$jsafe 	= "js_".rand(100, 999);
		
		/*<?php*/ ?>
			<div class="fms-admin-photo-manager" data-jsafe="<?php echo $jsafe ?>">
							
				<!-- SORTABLE BOX OF ALL IMAGES ATTACHED TO THIS POST -->
				<div class="fms-photo-grid sortable ui-sortable">
					<?php //foreach ($photos as $photo) fms_generate_photo_card($photo); ?>
				</div>
				
				<!-- EXTRA CARD/BUTTON FOR ADDING ADDITIONAL IMAGES -->
				<div class="fms-admin-photo-card add-photo">
					<div class="interior">
						
						<div class="photo-add-icon"><i class="fa fa-plus-circle"></i></div>
						
					</div>
				</div>
				
			</div>
			
			<!-- CONTROL LOGIC -->
			<script type="text/javascript">
				jQuery(document).ready(function($) {
				
					var $manager 	= $("div.fms-admin-photo-manager[data-jsafe='<?php echo $jsafe ?>']");
					
					var postID		= <?php echo $post->ID ?>;
					var photos_raw 	= <?php echo json_encode($photos) ?>;
					var photos 		= { };


					// Create a photo card for all pre-existing listing photos
					// **********************************************************************************************************************************
					$.each(photos_raw, function(index, item) {
						
						createPhotoBox(item);
					});
					
					
					// Add UI sorting attributes to the photo grid
					// **********************************************************************************************************************************
					$("div.fms-photo-grid").sortable({
									
			            revert: true,
			            cursor: "move",
			            handle: ".handle",
			            
						placeholder: {
						
							element: function(currentItem) {
								return $("<div class='fms-admin-photo-card'></div>")[0];
							},
							
							update: function(container, p) {
								return;
							}
						}
			            
			        }).disableSelection();
			        
					
					// Add an image to the collection from the WP media gallery
					// **********************************************************************************************************************************
					$("div.fms-admin-photo-card.add-photo").on("click", function(e) {
						
						// Open the WP media selector
						var image = wp.media({
							 
							title: "Add New Listing Photos",
							button: {
								
								text: "Add Listing Photos"	
							},
							multiple: true
							
						}).open().on("select", function(e) {
							
							// Grab the selected images
							var selected = image.state().get("selection").map(function(attachment) {
								
								// Get each image selection as a JSON object
								attachment = attachment.toJSON();
				
								// Cast the WP media data to our DDF image data structure
								var photoData = {
									
									// Standard DDF image data
									"url":		attachment.url,
									"show":		true,
									"remote":	false,	
								}
				
								// Make a new photo box for the added photo
								createPhotoBox(photoData);
							});
						});	
					});
					
					
					// Create a new sortable photo box
					// ********************************************************************************************************************************** 
					function createPhotoBox(data) {
						
						// Create an id for this photo and associate the photo data with it
						var ID = "photo_"+ Math.floor(100 + Math.random() * 900);
						photos[ID] = data;

						// Create the new photo card
						var newPhotoCard = $(
						
							"<div id='"+ ID +"' class='fms-admin-photo-card'>"+
								"<div class='interior'>"+
									
									"<div class='handle'>"+
										
										"<div class='img-control-btn show'><i class='fa fa-eye' title='Show photo'></i></div>"+
										"<div class='img-control-btn hide'><i class='fa fa-ban' title='Hide photo'></i></div>"+
										
										"<div class='img-control-btn close'><i class='fa fa-close' title='Remove photo'></i></div>"+
										
									"</div>"+
									
									"<div class='body'>"+
									
										"<div class='hidden-cover'><span>HIDDEN</span></div>"+
										"<div class='photo' style='background-image:url("+ data.url +")'></div>"+
									
									"</div>"+
									
								"</div>"+
							"</div>"
						);

						// Only show the close button for assets that were added through WP!									
						if (data.remote === false) {
							
							$("div.img-control-btn.close", newPhotoCard).show();
							
						} else {
							
							// Show/hide controls for the DDF source images only!
							if (data.show === true) {
								
								$("div.img-control-btn.hide", newPhotoCard).show();
							
							} else {
								
								$("div.img-control-btn.show", newPhotoCard).show();
								$("div.hidden-cover", newPhotoCard).show();
							}
						}
						
						// Add the new card to the list	
						$("div.fms-photo-grid", $manager).append(newPhotoCard);
					}
					
					
					// Remove photo control handler
					// **********************************************************************************************************************************
					$("body").on("click", "div.img-control-btn.close", function(e) {
						
						var $target = $(this).closest("div.fms-admin-photo-card");
						var id = $target.attr("id");
						
						if (photos[id].remote === false) {
						
							delete photos[id];
							$target.remove();
						}
					});
					
					
					// Hide button control handler
					// **********************************************************************************************************************************
					$("body").on("click", "div.img-control-btn.hide", function(e) {
						
						var $target = $(this).closest("div.fms-admin-photo-card");
						var id = $target.attr("id");
						
						if (photos[id].remote === true) {
						
							$("div.img-control-btn", $target).hide();
							$("div.img-control-btn.show", $target).show();
						
							photos[id].show = false;
							$("div.hidden-cover", $target).show();
						}
					});	
					
					
					// Show photo control handler
					// **********************************************************************************************************************************
					$("body").on("click", "div.img-control-btn.show", function(e) {
						
						var $target = $(this).closest("div.fms-admin-photo-card");
						var id = $target.attr("id");
						
						if (photos[id].remote === true) {
						
							$("div.img-control-btn", $target).hide();
							$("div.img-control-btn.hide", $target).show();
						
							photos[id].show = true;
							$("div.hidden-cover", $target).hide();
						}
					});	
					
					
					// Bind to the update/publish button to save photo meta
					// **********************************************************************************************************************************
					$("input#publish").on("click", function(e) {
						
						$publish = $(this);
						
						e.preventDefault();
						
						var sortedIDs = $("div.fms-photo-grid").sortable("toArray");
						var savePhotos = [];
						
						$.each(sortedIDs, function(index, id) { 
							savePhotos.push(photos[id]);
						});
						
						var data_out = {
							
							action: "fms_save_photo_meta",
							photos: JSON.stringify(savePhotos),
							postID:	postID,
						}
						
						$.post(ajaxurl, data_out, function(data_in) {
							
							$publish.off("click");
							$publish.trigger("click");
						});	
					}); 	
				});
			</script>
		<?php /*?>*/
	}


	// **********************************************************************************************************************************
	// 		Save the photo data to the current post meta
	// **********************************************************************************************************************************

	function fms_save_photo_meta() {

		$photos  	= (!empty($_POST["photos"]) ? stripslashes($_POST["photos"]) : array());
		$post_id	= (!empty($_POST["postID"]) ? $_POST["postID"] : "");
		
		if (!empty($photos) && !empty($post_id)) {

			update_post_meta($post_id, "_fms_photos", json_decode($photos, true));
		}

		wp_die();
	}
	add_action("wp_ajax_fms_save_photo_meta", "fms_save_photo_meta");
	
?>