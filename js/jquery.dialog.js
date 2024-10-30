/**
 * @detail
 * Additional function to handle content
 * http://zourbuth.com/
 */

(function($) { Linkimage = {

	init : function(){
		$('.totalControls').closest(".widget-inside").addClass("totalWidgetBg");		
		
		$('ul.nav-tabs li').live( "click", function(){
			Linkimage.tabs(this)
		});
		
		$("a.addImage").live( "click", function(){
			Linkimage.addImage(this); return false;
		});
		
		$("a.removeImage").live( "click", function(){
			Linkimage.removeImage(this); return false;
		});
		
		$("a.add-linkimage").live( "click", function(){
			Linkimage.addLinkimage(this);
			return false;
		});
		
		$(".element-delete").live('click', function(){
			$(this).parent('.element').fadeTo( 300, 0.00, function() {
				$(this).animate({width: 0}, function() {
					$(this).remove();
				});
			});
		});
	},
	
	tabs : function(tab){
		var t, i, c;
		
		t = $(tab);
		i = t.index();
		c = t.parent("ul").next().children("li").eq(i);
		t.addClass('active').siblings("li").removeClass('active');
		$(c).show().addClass('active').siblings().hide().removeClass('active');
		t.parent("ul").find("input").val(0);
		$('input', t).val(1);
	},
	
	addImage : function(el){
		var g, u, i, a;
		
		g = $(el).siblings('img');
		i = $(el).siblings('input');
		a = $(el).siblings('a');
		
		tb_show('Select Image/Icon Title', 'media-upload.php?post_id=0&type=image&TB_iframe=true');	
		
		window.send_to_editor = function(html) {
			u = $('img',html).attr('src');
			
			if ( u === undefined || typeof( u ) == "undefined" ) 
				u = $(html).attr('src');		
			
			g.attr("src", u).slideDown();
			i.val(u);
			a.addClass("showRemove").removeClass("hidden");
			tb_remove();
		};
	},
	
	removeImage : function(el){
		var t = $(el);
		
		t.next().val('');
		t.siblings('img').slideUp();
		t.removeClass('show-remove').addClass('hide-remove');
		t.fadeOut();
	},
	
	colorPicker : function(el){
		$('.wp-color-picker').wpColorPicker();
	},
	
	addLinkimage : function(e) {
		var img, link, alt;

		tb_show('Select Image', 'media-upload.php?post_id=0&type=image&TB_iframe=true');	
		
		window.send_to_editor = function(a) {
			image = $("img",a).attr("src");
			link = $(a).attr("href");
			alt = $("img",a).attr("alt");
			
			if ( image === undefined || typeof( image ) == "undefined" ) 
				image = $(a).attr("src");
			
			if ( link === undefined || typeof( link ) == "undefined" ) 
				link = $(a).attr("href");
			
			if ( alt === undefined || typeof( alt ) == "undefined" ) 
				alt = $(a).attr("alt");

			$(e).siblings(".loading").removeClass("hidden");
			$.post( linkimagevar.ajaxurl, { action: linkimagevar.action, link: link, alt: alt, image: image, _ajax_nonce: linkimagevar.nonce }, function( data ){
				$(e).siblings(".linkimage-container").append(data);
				$(e).siblings(".loading").addClass("hidden");
			});
			tb_remove();
		};
	},
	
	addSortable : function() {
		$('.linkimage-container').sortable({ 
			items: '.element', 
			placeholder: 'placeholder',
			start: function(event, ui) {
				$(".placeholder").width( ui.item.width() ).height( ui.item.height() );
			}			
		});		
	}
};

$(document).ready(function(){Linkimage.init();});
})(jQuery);
