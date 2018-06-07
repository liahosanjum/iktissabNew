jQuery.fn.liScroll = function(settings) {
		settings = jQuery.extend({
		travelocity: 0.07,
		ltr: true
		}, settings);		
		return this.each(function(){
				var c1='tickercontainer'+(settings.rtl?'_rtl':'');
				var c2='newsticker'+(settings.rtl?'_rtl':'');
				var $strip = jQuery(this);
				$strip.addClass(c2);
				$strip.show();
				var stripWidth = 0;
				var $mask = $strip.wrap("<div class='mask'></div>");
				var $tickercontainer = $strip.parent().wrap("<div class='"+c1+"'></div>");								
				var containerWidth = $strip.parent().parent().width();	//a.k.a. 'mask' width 	
				$strip.find("li").each(function(i){
				stripWidth += jQuery(this, i).width();
				});
				$strip.width(stripWidth);
				var defTiming = stripWidth/settings.travelocity;
				var totalTravel = stripWidth+containerWidth;								
				function scrollnews(spazio, tempo, rtl){
				  if(!rtl)
				    $strip.animate({left: '-='+ spazio}, tempo, "linear", function(){$strip.css("left", containerWidth); scrollnews(totalTravel, defTiming, rtl);});
				  else
				    $strip.animate({left: '-='+ spazio}, tempo, "linear", function(){$strip.css("left", containerWidth); scrollnews(totalTravel, defTiming, rtl);});
				}
				scrollnews(totalTravel, defTiming, settings.rtl);				
				$strip.hover(function(){
				jQuery(this).stop();
				},
				function(){
				var offset = jQuery(this).offset();
				var residualSpace = offset.left + stripWidth;
				var residualTime = residualSpace/settings.travelocity;
				scrollnews(residualSpace, residualTime, settings.rtl);
				});			
		});	
};