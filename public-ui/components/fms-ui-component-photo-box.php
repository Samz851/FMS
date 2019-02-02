<?php defined("ABSPATH") or die("Access denied.") ?>
<?php

	class FMSPhotoBox {
		
		private $lead_photo;
		private $photo_urls;
		private $jsid;
		
		public function __construct($urls = array(), $with_lead = false) {
			
			if (empty($urls)) $urls = fms_get_photo_urls();
			if (empty($urls)) return;
			
			if ($with_lead) {
			
				$this->lead_photo = $urls[0];
				$this->photo_urls = array_slice($urls, 1);
			}
			
			else {
				
				$this->lead_photo = "";
				$this->photo_urls = $urls;
			}
			
			$this->jsid = get_class($this)."_".rand(100, 999);
			
			echo $this->html();
			echo $this->js();
		}
		
		private function html() {
			
			ob_start(); ?>
			
				<div id="fms-photo-box" data-jsid="<?php echo $this->jsid ?>">
					
					<?php if (!empty($this->lead_photo)): ?>
					
						<img class="fms-lightbox-item" src="<?php echo $this->lead_photo ?>" />
					
					<?php endif ?>
					
					<?php if (count($this->photo_urls) > 0): ?>
					
						<div class="fms-photo-strip">
							
							<i id="strip-prev" class="fa fa-angle-left strip-nav"></i>
							<i id="strip-next" class="fa fa-angle-right strip-nav"></i>
							
							
							<div class="fms-photo-scroller">
							
								<?php foreach ($this->photo_urls as $url): ?>
								
									<div class="fms-photo fms-lightbox-item" style="background-image: url(<?php echo $url ?>)"></div>
								
								<?php endforeach ?>
							
							</div>
							
						</div>
					
					<?php endif ?>

				</div>
				
				<?php if (!fms_get_option("disable_photo_lightbox", false)): ?>
				
					<div id="fms-photo-viewer" data-jsid="<?php echo $this->jsid ?>">
							
						<div id="close-btn" class="lightbox-btn"><i class="fa fa-close"></i></div>
						<div id="next-btn"  class="lightbox-btn"><i class="fa fa-chevron-right"></i></div>
						<div id="prev-btn"  class="lightbox-btn"><i class="fa fa-chevron-left"></i></div>	
						
						<img id="big-photo" />
						
					</div>
				
				<?php endif ?>
			
			<?php return ob_get_clean();
		}
		
		private function js() {
			
			ob_start(); ?>
			
				<script type="text/javascript" data-jsid="<?php echo $this->jsid ?>">
					
					jQuery(document).ready(function($) {
						
						
						// *******************************************************************************************************
						//
						//		Photo strip navigation
						//
						// *******************************************************************************************************
						
						(function() {
						
							var $box 	= $("div#fms-photo-box[data-jsid='<?php echo $this->jsid ?>']");
							var $cont	= $("div.fms-photo-scroller", $box);
							
							var $prev	= $("i#strip-prev", $box);
							var $next 	= $("i#strip-next", $box);
							
							// Get the real width of the scroll container
							var contWidth = (function() {
								
								var width = 0;
								$("div.fms-photo", $cont).each(function() { width += $(this).outerWidth() });
								
								return width;
								
							})();
	
							// Set the default button states
							$prev.hide();
							$next.hide();
							
							if (contWidth > $box.outerWidth()) $next.show();
							
							// set button states based on scroll position
							$cont.on("scroll", function() {
								
								if ($(this).scrollLeft() <= 0) {
									
									$prev.hide();
									$next.show();
								}
								
								else if (($(this).scrollLeft() + $cont.parent().width()) >= (contWidth - 1)) {
									
									$prev.show();
									$next.hide();
								}								
								
								else {
									
									$prev.show();
									$next.show();
								}
							});

							var lockScroll = false;
							
							// Scroll the photo strip to the left
							$prev.on("click", function() { scrollLeft() });
							function scrollLeft() {
								
								if (!lockScroll) {
								
									lockScroll = true;
								
									$cont.animate({ "scrollLeft": $cont.scrollLeft() - $cont.parent().width() }, function() {
										
										lockScroll = false;
									});
								}
							}
							
							// Scroll the photo strip to the right
							$next.on("click", function() { scrollRight() });
							function scrollRight() {
								
								if (!lockScroll) {
								
									lockScroll = true;
								
									$cont.animate({ "scrollLeft": $cont.scrollLeft() + $cont.parent().width() }, function() {  
										
										lockScroll = false;
									});
								}
							}
						})();
						
						
						// *******************************************************************************************************
						//
						//		Photo lightbox control
						//
						// *******************************************************************************************************
						
						(function() {
							
							var $target 	= $("div#fms-photo-viewer[data-jsid='<?php echo $this->jsid ?>']");
							var $thumbs		= $("*.fms-lightbox-item");
							
							var $bigPhoto	= $("img#big-photo", $target);
							var $closeBtn 	= $("div#close-btn", $target);
							var $nextBtn 	= $("div#next-btn", $target);
							var $prevBtn 	= $("div#prev-btn", $target);
	
							var isOpen	= false;
							var index 	= 0;
							
							// Give all the target photos the pointer property
							$thumbs.css({ "cursor": "pointer" });
							
							var openLightbox 	= function() { $target.addClass("open"); 		isOpen = true; 		};
							var closeLightbox 	= function() { $target.removeClass("open"); 	isOpen = false;		}; 
						
							// Go to the next photo
							var nextPhoto = function() { 
								if (isOpen && index < $thumbs.length - 1) {
									
									index++;
									loadPhoto.call(); 
								}
							};
							
							// Go back to the previous photo
							var prevPhoto = function() { 
								if (isOpen && index > 0) {
								
									index--;
									loadPhoto.call(); 
								}
							};
						
							// Load the target photo at the current index
							var loadPhoto = function() {
								
								var image = $thumbs.eq(index);
								
								if (image) {
								
									// Get either the src attribute or the background image property
									var SRC = image.attr("src");
									var BGI = image.css("background-image");
									
									// Filter out some useless jank
									SRC = (SRC || "");
									BGI = (BGI.replace("url(", "").replace(")", "").replace(/\"/gi, "").replace("none", ""));
									
									// Get whichever one is set
									var URL = (SRC || BGI);

									$bigPhoto.attr("src", URL);
								
								}	
								
								
								if (index === $thumbs.length - 1) $nextBtn.hide();
								else $nextBtn.show();
								
								if (index === 0) $prevBtn.hide();
								else $prevBtn.show();
								
							};

							// Handle close button clicks
							$closeBtn.on("click", closeLightbox);
							
							// Handle next/prev button clicks
							$nextBtn.on("click", nextPhoto);
							$prevBtn.on("click", prevPhoto);
							
							// Handle swipe events on touch screen devices
							$target.on("swipeleft",  nextPhoto);
							$target.on("swiperight", prevPhoto);
							$target.on("swipedown swipeup",  closeLightbox);

							// Handle keyboard events
							$(document).keyup(function(e) {
				
								// ESC key, up or down arrow pressed
							    if(e.keyCode === 27 || e.keyCode === 38 || e.keyCode === 40) closeLightbox.call();
							
								// Left arrow
								if(e.keyCode === 37) prevPhoto.call();
							
								// Right arrow
								if(e.keyCode === 39) nextPhoto.call();
							
							});
						
							// When a thumbnail is clicked, open it!
							$thumbs.on("click", function(e) {
								
								index = $thumbs.index(this);

								loadPhoto.call();
								openLightbox.call();
								
							});
								
						})();
					});
					
				</script>	
			
			<?php return ob_get_clean();
		}
	}	
?>