(function($){$.fn.aboMegaMenu=function(options){var settings=$.extend({showAnimation:{height:'show',width:'show'},hideAnimation:{height:'hide',width:'hide'},showSpeed:300,hideSpeed:0,fx:'linear',maxWidth:960,extraWidth:10,space:10},options),rowWidth=0,biggestRow=0;calcWidth=0,theWidth=0,thisHtml='',thisWidth=0,x=0,xDifference=0,xSite=$('#container').offset().left,amm=$.fn.aboMegaMenu,calcSubWidth=function(element){rowWidth=0;element.children('div.level_2').eq(0).find('ul.level_2').each(function(){theWidth=visualLength($(this));$(this).css('width',theWidth);rowWidth+=settings.space;rowWidth+=theWidth;});},calcSubWidth_empty=function(element){rowWidth=0;element.children('div.level_2').eq(0).find('ul.level_2_empty').each(function(){theWidth=visualLength($(this));$(this).css('width',theWidth);theWidth+=settings.space
if(theWidth>rowWidth){rowWidth=theWidth;}});},visualLength=function(element){thisHtml=element.html();$('#ruler').html(thisHtml);thisWidth=$('#ruler').width();$('#ruler').html('');return thisWidth;},megaHoverOver=function(){if($(this).children('.amm_calc_done').length<1){biggestRow=0;calcSubWidth($(this));x=$(this).offset().left;xDifference=x-xSite;if(xDifference>(settings.maxWidth/2)){}else{if(rowWidth==0){calcSubWidth_empty($(this));}
if((rowWidth+xDifference)>settings.maxWidth){rowWidth=settings.maxWidth-xDifference-(settings.extraWidth*2)-2;$(this).children('div.level_2').eq(0).find('ul:not(.level_2_empty)').each(function(){theWidth=visualLength($(this));calcWidth+=settings.space;calcWidth+=theWidth;if(calcWidth>rowWidth){$(this).before('<div class="clear" style="height:'+settings.space+'px;"></div>');if(biggestRow<calcWidth){biggestRow=calcWidth-theWidth-settings.space;}
calcWidth=0;}});}}
if(biggestRow==0){biggestRow=rowWidth;}
calcWidth=0;$(this).find("div.level_2").css({'width':biggestRow});$(this).children('div.level_2').eq(0).addClass('amm_calc_done');}
$(this).find('div.level_2').animate(settings.showAnimation,settings.showSpeed,settings.fx);},megaHoverOut=function(){$(this).find('div.level_2').animate(settings.hideAnimation,settings.hideSpeed,settings.fx);};$('body').prepend('<span id="ruler"></span>');this.children('li').children('div.level_2').hide().css('width','').find('ul').each(function(){$(this).css('width','');});$('a.level_1').attr('title','');$('a.level_2').attr('title','');$('a.level_3').attr('title','');if($.fn.hoverIntent){this.children('li.level_1').hoverIntent(megaHoverOver,megaHoverOut);}else{this.children('li.level_1').hover(megaHoverOver,megaHoverOut);}};})(jQuery);