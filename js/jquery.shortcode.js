jQuery(document).ready(function($){
	var win = window.dialogArguments || opener || parent || top;
	
	var footerHeight = $('#buttonSection').height();
	$('#optionSection').css({
		marginBottom: footerHeight + 'px',
		height: ($('body').height() - footerHeight) + 'px'
	});

	$("#spCancelShortcode").click(function () {
		win.tb_remove();
	});
	
	$("#spInsertShortcode").click(function () {	
				
		var inputVal, text, optSect = $(this).parents('#buttonSection').prev();
		inputVal = '';
		
		$('input', optSect).each(function(){
			
			if ($(this).attr('id') && $(this).is(':checkbox') )
				inputVal += ' ' + $(this).attr('id') + '="' + $(this).prop("checked") + '"';
		
			else if ( $(this).attr('id') && $(this).not(':checkbox') )
				inputVal += ' ' + $(this).attr('id') + '="' + $(this).val() + '"';
		});

		$('select', optSect).each(function(){
			if ( $(this).attr('id') && $(this).val())
				inputVal += ' ' + $(this).attr('id') + '="' + $(this).val() + '"';
		});
		
		$('textarea', optSect).each(function(){
			if ($(this).attr('id'))
				inputVal += ' ' + $(this).attr('id') + '="' + $(this).val() + '"';
		});
		
		shortcode = "[" + $(optSect).attr('class') + inputVal + "]";

		if ($(this).hasClass("content")) {
			text = win.tinyMCE.getInstanceById('content').selection.getContent();
			if (text=='') text = 'put your content here';
			win.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, shortcode+text+"[/"+$(optSect).attr('class')+"]");
		} else {
			win.tinyMCE.execCommand('mceInsertContent', false, shortcode);
		}

		win.tb_remove();
	});
});