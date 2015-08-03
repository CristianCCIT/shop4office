(function($) {
	$.fn.aboMegaMenu = function (options) {
		var settings = $.extend({
			showAnimation : {height: 'show', width: 'show'},
			hideAnimation : {height: 'hide', width: 'hide'},
			showSpeed: 300,
			hideSpeed: 0,
			fx : 'linear',
			maxWidth : 960,
			extraWidth : 10,
			space : 10
			}, options),
			rowWidth = 0,
			biggestRow = 0;
			calcWidth = 0,
			theWidth = 0,
			thisHtml = '',
			thisWidth = 0,
			x = 0,
			xDifference = 0,
			xSite = $('#container').offset().left,
			// find fx: http://jqueryui.com/demos/effect/#easing
			amm = $.fn.aboMegaMenu,
				//Function to calculate total width of all ul's
				calcSubWidth = function(element) {
					rowWidth = 0;
					//Calculate row
					element.children('div.level_2').eq(0).find('ul.level_2').each(function() {
						theWidth = visualLength($(this));
						$(this).css('width', theWidth);
						rowWidth += settings.space;
						rowWidth += theWidth; //Add each ul's width together
					});
				},
				calcSubWidth_empty = function(element) {
					rowWidth = 0;
					//Calculate row
					element.children('div.level_2').eq(0).find('ul.level_2_empty').each(function() {
						theWidth = visualLength($(this));
						$(this).css('width', theWidth);
						theWidth += settings.space
						if (theWidth > rowWidth) {
							rowWidth = theWidth;
						}
					});
				},
				// function to calculate actual width of element
				visualLength = function (element) {
					thisHtml = element.html();
					$('#ruler').html(thisHtml);
					thisWidth = $('#ruler').width();
					$('#ruler').html('');
					return thisWidth;
				},
				//On Hover Over
				megaHoverOver = function () {
					if ($(this).children('.amm_calc_done').length < 1) {
						biggestRow = 0;
						calcSubWidth($(this));  //Call function to calculate width of all ul's
						x = $(this).offset().left;
						xDifference = x - xSite;
						if (xDifference > (settings.maxWidth/2)) {
							//Here comes code for align right instead of align left for the div.second_level
						} else {
							if (rowWidth == 0) {
								calcSubWidth_empty($(this));
							}
							if ((rowWidth + xDifference) > settings.maxWidth) { //check is the row stays in the container
								rowWidth = settings.maxWidth - xDifference - (settings.extraWidth*2) - 2;
								
								$(this).children('div.level_2').eq(0).find('ul:not(.level_2_empty)').each(function() {
									theWidth = visualLength($(this));
									calcWidth += settings.space;
									calcWidth += theWidth; //Add each ul's width together
									if (calcWidth > rowWidth) { //if calculated width is bigger then row width, start a new row.
										$(this).before('<div class="clear" style="height:'+settings.space+'px;"></div>');
										if (biggestRow < calcWidth) { //be sure to use the width of biggest row
											biggestRow = calcWidth - theWidth - settings.space;
										}
										calcWidth = 0;
									}
								});
							}
						}
						if (biggestRow == 0) { //when no extra row is made
							biggestRow = rowWidth;
						}
						calcWidth = 0;
						$(this).find("div.level_2").css({'width' : biggestRow}); //Set Width
						$(this).children('div.level_2').eq(0).addClass('amm_calc_done');
					}
					$(this).find('div.level_2').animate(settings.showAnimation, settings.showSpeed, settings.fx);
				},
				//On Hover Out
				megaHoverOut = function () {
				  $(this).find('div.level_2').animate(settings.hideAnimation, settings.hideSpeed, settings.fx);
				};
		$('body').prepend('<span id="ruler"></span>');
		this.children('li').children('div.level_2').hide()
														.css('width', '')
														.find('ul').each(function() {
															$(this).css('width', '');
														});
		$('a.level_1').attr('title', '');
		$('a.level_2').attr('title', '');
		$('a.level_3').attr('title', '');
		if ($.fn.hoverIntent) {
			this.children('li.level_1').hoverIntent( megaHoverOver, megaHoverOut );
		} else {
			this.children('li.level_1').hover( megaHoverOver, megaHoverOut );
		}
	};
})(jQuery);