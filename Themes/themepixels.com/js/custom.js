jQuery(document).ready(function(){
	
	// main menu
	if(jQuery.superfish) {
		jQuery('.header-nav .mainmenu').superfish({ 
			delay:       100,                            	// one second delay on mouseout 
			animation:   {opacity:'show',height:'show'},  	// fade-in and slide-down animation 
			speed:       'fast',                          	// faster animation speed 
			autoArrows:  false,                          	// disable generation of arrow mark-up 
			dropShadows: false
		});
	}
	
	
	// this will create a clone of menu for mobile
	// and this will be used as the menu when viewed mobile
	jQuery('.header-nav nav').append('<div class="activemenu"><div></div><a></a></div>');
	
	jQuery('.header-nav li').each(function(){
		if(jQuery(this).hasClass('current-menu-item')) {
			var curpage = jQuery(this).find('> a').text();
			jQuery('.activemenu div').text(curpage);
		}
	});
	
	jQuery('.header-nav .mainmenu').clone()
											 .appendTo('.header-nav nav')
											 .removeAttr('class')
											 .addClass('mobilemenu');
	
	jQuery('.header-nav .mobilemenu ul').each(function(){
		jQuery(this).removeAttr('style');
	});
	
	
	// for mobile menu toggle
	jQuery('.activemenu').click(function(){
		if(!jQuery('.mobilemenu').is(':visible')) {
			jQuery('.mobilemenu').slideDown();
		} else {
			jQuery('.mobilemenu').slideUp();
		}
		return false;
	});
	
   // tooltip
	jQuery('.topheader-right .social a').tooltip({placement: 'bottom'});
	
	// collapsible widget
	if(jQuery('.widget-title').length > 0) {
		
		jQuery('.widget-title a').click(function(){
			var parent = jQuery(this).parents('.widget');
			var content = parent.find('.widget-content');
			if(!content.is(':visible')) {
				content.slideDown(100, function(){
					parent.removeClass('widget-close');
				});
			} else {
				content.slideUp(100, function(){
					parent.addClass('widget-close');
				});
			}
		});
		
	}
      
		
	// blog image hover effect
	if(jQuery('.blog-img').length > 0) {
		
		jQuery('.blog-img').hover(function() {
			jQuery(this).animate({opacity: 0.8}, 'fast');
			
		}, function() {
			jQuery(this).animate({opacity: 1},'fast');			
		})
		
	}
		
	// blog image slides
	if(jQuery('.blog-slides').length > 0 ) {
		jQuery('.blog-slides').flexslider({
			animation: "slide" // slide or fade
		});
	}
	
	// portfolio image slides
	if(jQuery('.portfolio-slides').length > 0 ) {
		jQuery('.portfolio-slides').flexslider({
			animation: "slide" // slide or fade
		});
	}
	
	// accordion
	if(jQuery('.accordion').length > 0)
		jQuery('.accordion').accordion({heightStyle:'content'});
		
	
	// flickr feeds
	if(jQuery('#flickrfeed').length > 0) {
		jQuery('#flickrfeed').jflickrfeed({
			limit: 9,
			qstrings: {
				id: '26654072@N04'
			},
			itemTemplate:
			'<li>' +
				'<a rel="colorbox" href="{{image}}" title="{{title}}">' +
					'<img src="{{image_s}}" alt="{{title}}" />' +
				'</a>' +
			'</li>'
		}, function(data) {
			
			jQuery('#flickrfeed a').colorbox();
		
		});
	}
	
	// carousel posts
	if(jQuery('.carousel-list').length > 0) {
		
		function mycarousel_initCallback(carousel) {
			jQuery('#rl-next').bind('click', function() {
				carousel.next();
				return false;
			});
		 
			jQuery('#rl-prev').bind('click', function() {
				carousel.prev();
				return false;
			});
		}
		
		jQuery('.carousel-list').jcarousel({
			initCallback: mycarousel_initCallback,
			buttonNextHTML: null,
			buttonPrevHTML: null,
			scroll: 1
		});
	}
	
	
	// related posts
	if(jQuery('.related-list').length > 0) {
		
		function mycarousel_initCallback(carousel) {
			jQuery('#rl-next').bind('click', function() {
				carousel.next();
				return false;
			});
		 
			jQuery('#rl-prev').bind('click', function() {
				carousel.prev();
				return false;
			});
		}
		
		jQuery('.related-list').jcarousel({
			initCallback: mycarousel_initCallback,
			buttonNextHTML: null,
			buttonPrevHTML: null,
			scroll: 1
		});
	}
	
	// testimonial list
	if(jQuery('.testimonial-list').length > 0) {
		
		function testi_carousel_initCallback(carousel) {
			
			var parentWidth = jQuery('.testimonialpanel').width();
			jQuery('.testimonial-list li').css({width: parentWidth});
			
			jQuery('#testi-next').bind('click', function() {
				carousel.next();
				return false;
			});
		 
			jQuery('#testi-prev').bind('click', function() {
				carousel.prev();
				return false;
			});
		}
		
		jQuery('.testimonial-list').jcarousel({
			initCallback: testi_carousel_initCallback,
			buttonNextHTML: null,
			buttonPrevHTML: null,
			scroll: 1
		});
	}
	
	// show sub replies
	if(jQuery('.showreplies').length > 0) {
		
		jQuery('.showreplies').click(function(){
			var n = jQuery(this).next();
			if(!n.is(':visible')) n.show(); else n.hide();
				
			return false;
		});
		
	}
	
	// portfolio hover
	if(jQuery('.port-entry').length > 0) {
		
		jQuery('.port-entry').hover(function(){
			jQuery(this).addClass('portHover');
		}, function(){
			jQuery(this).removeClass('portHover');
		});
		
	}
	
	// float footer if content below main document height
	if (jQuery("body").height() < jQuery(window).height()) {
		  jQuery('footer').addClass('footer-float');
    }
	
	
	// adding back to top button
	jQuery('body').append('<a id="backtotop"></a>');
	jQuery('#backtotop').backToTop();
	
	
	// sticky header
	var win = jQuery(window);
	win.scroll(function(){
		if(!jQuery('.activemenu').is(':visible')) {
			if(win.scrollTop() > 52) {
				jQuery('.mainwrapper').addClass('header-sticky');
			} else {
				jQuery('.mainwrapper').removeClass('header-sticky');
			}
		}
	});
	
});

// scroll back to top
(function($){$.fn.backToTop=function(options){var $this=$(this);$this.hide().click(function(){$("body, html").animate({scrollTop:"0px"});});var $window=$(window);$window.scroll(function(){if($window.scrollTop()>0){$this.fadeIn();}else{$this.fadeOut();}});return this;};})(jQuery);